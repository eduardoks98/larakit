<?php

namespace Eduardoks98\GoogleAuth\Services;

use Eduardoks98\GoogleAuth\Models\GoogleUser;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;

class GoogleAuthService
{
    /**
     * The Google OAuth provider instance.
     */
    protected Google $provider;

    /**
     * Create a new GoogleAuthService instance.
     */
    public function __construct()
    {
        $this->provider = new Google([
            'clientId' => config('google-auth.client_id'),
            'clientSecret' => config('google-auth.client_secret'),
            'redirectUri' => config('google-auth.redirect_uri'),
        ]);
    }

    /**
     * Get the authorization URL.
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        $defaultOptions = [
            'scope' => config('google-auth.scopes', []),
            'access_type' => config('google-auth.access_type', 'offline'),
            'prompt' => config('google-auth.prompt', 'select_account'),
        ];

        $options = array_merge($defaultOptions, $options);

        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * Get the state parameter for CSRF protection.
     */
    public function getState(): ?string
    {
        return $this->provider->getState();
    }

    /**
     * Exchange authorization code for access token.
     *
     * @throws IdentityProviderException
     */
    public function getAccessToken(string $code): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    /**
     * Refresh the access token using a refresh token.
     *
     * @throws IdentityProviderException
     */
    public function refreshAccessToken(string $refreshToken): AccessToken
    {
        return $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Get the resource owner (user) details.
     *
     * @throws IdentityProviderException
     */
    public function getResourceOwner(AccessToken $token): array
    {
        $user = $this->provider->getResourceOwner($token);

        return $user->toArray();
    }

    /**
     * Handle the OAuth callback and authenticate the user.
     *
     * @throws IdentityProviderException
     */
    public function handleCallback(string $code): array
    {
        // Exchange code for access token
        $token = $this->getAccessToken($code);

        // Get user details from Google
        $googleUserData = $this->getResourceOwner($token);

        // Find or create Google user
        $googleUser = $this->findOrCreateGoogleUser($googleUserData, $token);

        // Find or create application user
        $user = $this->findOrCreateUser($googleUser);

        // Sync user data if enabled
        if (config('google-auth.auto_sync_user_data', true)) {
            $this->syncUserData($user, $googleUserData);
        }

        // Create Sanctum token
        $sanctumToken = $user->createToken(
            config('google-auth.token_name', 'google-auth-token'),
            config('google-auth.token_abilities', ['*'])
        );

        return [
            'user' => $user,
            'google_user' => $googleUser,
            'token' => $sanctumToken->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Find or create a Google user record.
     */
    protected function findOrCreateGoogleUser(array $googleUserData, AccessToken $token): GoogleUser
    {
        $googleUserId = $googleUserData['sub'] ?? $googleUserData['id'];

        $googleUser = GoogleUser::where('google_id', $googleUserId)->first();

        if ($googleUser) {
            // Update existing Google user
            $googleUser->updateToken(
                $token->getToken(),
                $token->getRefreshToken(),
                $token->getExpires() ? $token->getExpires() - time() : null
            );

            $googleUser->updateProfile([
                'email' => $googleUserData['email'] ?? null,
                'name' => $googleUserData['name'] ?? null,
                'given_name' => $googleUserData['given_name'] ?? null,
                'family_name' => $googleUserData['family_name'] ?? null,
                'picture' => $googleUserData['picture'] ?? null,
                'locale' => $googleUserData['locale'] ?? null,
            ]);

            return $googleUser->fresh();
        }

        // Create new Google user
        return GoogleUser::create([
            'google_id' => $googleUserId,
            'email' => $googleUserData['email'] ?? null,
            'name' => $googleUserData['name'] ?? null,
            'given_name' => $googleUserData['given_name'] ?? null,
            'family_name' => $googleUserData['family_name'] ?? null,
            'picture' => $googleUserData['picture'] ?? null,
            'locale' => $googleUserData['locale'] ?? null,
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_in' => $token->getExpires() ? $token->getExpires() - time() : null,
            'token_type' => 'Bearer',
            'last_login_at' => now(),
        ]);
    }

    /**
     * Find or create an application user.
     */
    protected function findOrCreateUser(GoogleUser $googleUser)
    {
        $userModel = config('google-auth.user_model', 'App\\Models\\User');

        // Try to find user by email
        $user = $userModel::where('email', $googleUser->email)->first();

        if ($user) {
            // Link Google user to existing user if not already linked
            if (! $googleUser->user_id) {
                $googleUser->update(['user_id' => $user->id]);
            }

            return $user;
        }

        // Create new user if auto-create is enabled
        if (config('google-auth.auto_create_users', true)) {
            return DB::transaction(function () use ($userModel, $googleUser) {
                $user = $userModel::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'email_verified_at' => now(), // Google has verified the email
                    'password' => null, // No password for OAuth users
                ]);

                $googleUser->update(['user_id' => $user->id]);

                return $user;
            });
        }

        throw new \Exception('User not found and auto-create is disabled.');
    }

    /**
     * Sync user data from Google.
     */
    protected function syncUserData($user, array $googleUserData): void
    {
        $updateData = [];

        if (isset($googleUserData['name']) && $user->name !== $googleUserData['name']) {
            $updateData['name'] = $googleUserData['name'];
        }

        if (isset($googleUserData['email']) && $user->email !== $googleUserData['email']) {
            $updateData['email'] = $googleUserData['email'];
        }

        if (isset($googleUserData['picture']) && method_exists($user, 'setAvatarAttribute')) {
            $updateData['avatar'] = $googleUserData['picture'];
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Revoke a user's Google access.
     */
    public function revokeAccess(GoogleUser $googleUser): bool
    {
        try {
            // Revoke token with Google
            $this->provider->getHttpClient()
                ->post('https://oauth2.googleapis.com/revoke', [
                    'form_params' => [
                        'token' => $googleUser->access_token,
                    ],
                ]);

            // Delete the Google user record
            $googleUser->delete();

            return true;
        } catch (\Exception $e) {
            // Log error but still delete the local record
            logger()->error('Failed to revoke Google access', [
                'google_user_id' => $googleUser->id,
                'error' => $e->getMessage(),
            ]);

            $googleUser->delete();

            return false;
        }
    }

    /**
     * Get a valid access token, refreshing if necessary.
     *
     * @throws IdentityProviderException
     */
    public function getValidAccessToken(GoogleUser $googleUser): string
    {
        if (! $googleUser->isTokenExpired()) {
            return $googleUser->access_token;
        }

        if (! $googleUser->refresh_token) {
            throw new \Exception('No refresh token available. User must re-authenticate.');
        }

        // Refresh the access token
        $newToken = $this->refreshAccessToken($googleUser->refresh_token);

        // Update the Google user record
        $googleUser->updateToken(
            $newToken->getToken(),
            $newToken->getRefreshToken() ?? $googleUser->refresh_token,
            $newToken->getExpires() ? $newToken->getExpires() - time() : null
        );

        return $newToken->getToken();
    }
}
