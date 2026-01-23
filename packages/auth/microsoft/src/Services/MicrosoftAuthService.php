<?php

namespace Eduardoks98\MicrosoftAuth\Services;

use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class MicrosoftAuthService
{
    protected Azure $provider;

    public function __construct()
    {
        $this->provider = new Azure([
            'clientId' => config('microsoft.client_id'),
            'clientSecret' => config('microsoft.client_secret'),
            'redirectUri' => config('microsoft.redirect_uri'),
            'tenant' => config('microsoft.tenant'),
            'urlAPI' => config('microsoft.url_api'),
            'urlAuthorize' => config('microsoft.url_authorize'),
            'urlAccessToken' => config('microsoft.url_access_token'),
            'urlResourceOwnerDetails' => config('microsoft.url_resource_owner_details'),
        ]);

        // Set Graph API version
        $this->provider->defaultEndPointVersion = config('microsoft.graph_version');
    }

    /**
     * Get the authorization URL.
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        $scopes = $options['scopes'] ?? config('microsoft.scopes');

        return $this->provider->getAuthorizationUrl([
            'scope' => $scopes,
            'state' => $options['state'] ?? bin2hex(random_bytes(16)),
        ]);
    }

    /**
     * Get the state parameter.
     */
    public function getState(): string
    {
        return $this->provider->getState();
    }

    /**
     * Get access token from authorization code.
     */
    public function getAccessToken(string $code): AccessToken
    {
        try {
            return $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (IdentityProviderException $e) {
            Log::error('Microsoft OAuth token exchange failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);
            throw $e;
        }
    }

    /**
     * Get user information from Microsoft Graph API.
     */
    public function getUserInfo(AccessToken $token): array
    {
        try {
            $user = $this->provider->getResourceOwner($token);
            return $user->toArray();
        } catch (IdentityProviderException $e) {
            Log::error('Microsoft Graph API user info failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);
            throw $e;
        }
    }

    /**
     * Create or update Microsoft user record.
     */
    public function findOrCreateMicrosoftUser(array $userData, AccessToken $token): MicrosoftUser
    {
        return DB::transaction(function () use ($userData, $token) {
            $microsoftUser = MicrosoftUser::updateOrCreate(
                ['microsoft_id' => $userData['id']],
                [
                    'email' => $userData['mail'] ?? $userData['userPrincipalName'] ?? null,
                    'name' => $userData['displayName'] ?? null,
                    'given_name' => $userData['givenName'] ?? null,
                    'surname' => $userData['surname'] ?? null,
                    'user_principal_name' => $userData['userPrincipalName'] ?? null,
                    'job_title' => $userData['jobTitle'] ?? null,
                    'office_location' => $userData['officeLocation'] ?? null,
                    'mobile_phone' => $userData['mobilePhone'] ?? null,
                    'business_phones' => $userData['businessPhones'] ?? null,
                    'preferred_language' => $userData['preferredLanguage'] ?? null,
                    'avatar_url' => $this->getAvatarUrl($userData),
                    'tenant_id' => $this->extractTenantId($token),
                ]
            );

            // Store tokens if enabled
            if (config('microsoft.store_tokens')) {
                $microsoftUser->updateTokens(
                    $token->getToken(),
                    $token->getRefreshToken(),
                    $token->getExpires() ? $token->getExpires() - time() : null
                );
            }

            // Update last login
            $microsoftUser->updateLastLogin();

            return $microsoftUser;
        });
    }

    /**
     * Find or create application user and link with Microsoft account.
     */
    public function findOrCreateUser(MicrosoftUser $microsoftUser): mixed
    {
        if ($microsoftUser->user_id) {
            return $microsoftUser->user;
        }

        if (!config('microsoft.auto_create_user')) {
            return null;
        }

        $userModel = config('microsoft.user_model');
        $email = $microsoftUser->email;

        if (!$email) {
            Log::warning('Cannot create user: email not provided by Microsoft', [
                'microsoft_id' => $microsoftUser->microsoft_id,
            ]);
            return null;
        }

        return DB::transaction(function () use ($userModel, $email, $microsoftUser) {
            // Find existing user by email
            $user = $userModel::where('email', $email)->first();

            // Create new user if not found
            if (!$user) {
                $user = $userModel::create([
                    'name' => $microsoftUser->name ?? $microsoftUser->user_principal_name,
                    'email' => $email,
                    'password' => bcrypt(bin2hex(random_bytes(32))), // Random password
                    'email_verified_at' => now(), // Microsoft verified the email
                ]);

                Log::info('User created via Microsoft OAuth', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            }

            // Link Microsoft account to user
            $microsoftUser->update(['user_id' => $user->id]);

            return $user;
        });
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refreshToken(string $refreshToken): AccessToken
    {
        try {
            return $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
            ]);
        } catch (IdentityProviderException $e) {
            Log::error('Microsoft OAuth token refresh failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);
            throw $e;
        }
    }

    /**
     * Get avatar URL from user data.
     */
    protected function getAvatarUrl(array $userData): ?string
    {
        // Avatar URL is not directly in user data, would need separate Graph API call
        // Format: https://graph.microsoft.com/v1.0/me/photo/$value
        return null;
    }

    /**
     * Extract tenant ID from access token.
     */
    protected function extractTenantId(AccessToken $token): ?string
    {
        $values = $token->getValues();
        return $values['tenant_id'] ?? $values['tid'] ?? null;
    }

    /**
     * Make a Microsoft Graph API request.
     */
    public function graphApiRequest(string $accessToken, string $endpoint, string $method = 'GET', array $options = []): array
    {
        $url = rtrim(config('microsoft.url_api'), '/') . '/' .
               config('microsoft.graph_version') . '/' .
               ltrim($endpoint, '/');

        try {
            $request = $this->provider->getAuthenticatedRequest($method, $url, $accessToken, $options);
            $response = $this->provider->getParsedResponse($request);
            return $response;
        } catch (\Exception $e) {
            Log::error('Microsoft Graph API request failed', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get user photo from Microsoft Graph.
     */
    public function getUserPhoto(string $accessToken): ?string
    {
        try {
            $response = $this->graphApiRequest($accessToken, 'me/photo/$value');
            return base64_encode($response);
        } catch (\Exception $e) {
            Log::debug('Could not fetch user photo from Microsoft', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
