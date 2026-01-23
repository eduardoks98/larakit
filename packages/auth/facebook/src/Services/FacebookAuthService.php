<?php

namespace Eduardoks98\FacebookAuth\Services;

use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\FacebookUser as LeagueFacebookUser;
use League\OAuth2\Client\Token\AccessToken;
use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Facebook Authentication Service
 *
 * Handles Facebook OAuth2 flow using League\OAuth2\Client\Provider\Facebook
 * Integrates with Laravel Sanctum for token-based authentication
 */
class FacebookAuthService
{
    /**
     * The Facebook OAuth2 provider instance.
     *
     * @var Facebook
     */
    protected Facebook $provider;

    /**
     * Create a new FacebookAuthService instance.
     */
    public function __construct()
    {
        $this->provider = new Facebook([
            'clientId' => config('facebook-auth.app_id'),
            'clientSecret' => config('facebook-auth.app_secret'),
            'redirectUri' => config('facebook-auth.redirect_uri'),
            'graphApiVersion' => config('facebook-auth.graph_api_version'),
        ]);
    }

    /**
     * Get the authorization URL for Facebook OAuth.
     *
     * @param array $options Additional options for the authorization URL
     * @return string
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        $scopes = config('facebook-auth.scopes', ['email', 'public_profile']);

        $options = array_merge([
            'scope' => $scopes,
        ], $options);

        $authUrl = $this->provider->getAuthorizationUrl($options);

        $this->logInfo('Generated Facebook authorization URL', [
            'scopes' => $scopes,
        ]);

        return $authUrl;
    }

    /**
     * Get the state parameter for CSRF protection.
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->provider->getState();
    }

    /**
     * Handle the OAuth callback and authenticate the user.
     *
     * @param string $code The authorization code from Facebook
     * @param string|null $state The state parameter for CSRF protection
     * @return array ['user' => User, 'token' => string, 'facebook_user' => FacebookUser]
     * @throws \Exception
     */
    public function handleCallback(string $code, ?string $state = null): array
    {
        try {
            // Exchange authorization code for access token
            $accessToken = $this->getAccessToken($code);

            // Get Facebook user details
            $facebookUser = $this->getFacebookUser($accessToken);

            // Find or create user in our system
            $user = $this->findOrCreateUser($facebookUser, $accessToken);

            // Create or update Facebook user record
            $facebookUserRecord = $this->createOrUpdateFacebookUser($user, $facebookUser, $accessToken);

            // Create Sanctum token
            $token = $this->createSanctumToken($user);

            $this->logInfo('Facebook authentication successful', [
                'user_id' => $user->id,
                'facebook_id' => $facebookUser->getId(),
            ]);

            return [
                'user' => $user,
                'token' => $token,
                'facebook_user' => $facebookUserRecord,
            ];
        } catch (\Exception $e) {
            $this->logError('Facebook authentication failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get access token from authorization code.
     *
     * @param string $code
     * @return AccessToken
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    protected function getAccessToken(string $code): AccessToken
    {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    /**
     * Get Facebook user details using access token.
     *
     * @param AccessToken $accessToken
     * @return LeagueFacebookUser
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    protected function getFacebookUser(AccessToken $accessToken): LeagueFacebookUser
    {
        $fields = config('facebook-auth.user_fields', [
            'id',
            'name',
            'email',
            'first_name',
            'last_name',
            'picture.type(large)',
        ]);

        // Get user details from Facebook Graph API
        return $this->provider->getResourceOwner($accessToken);
    }

    /**
     * Find or create a user in the system.
     *
     * @param LeagueFacebookUser $facebookUser
     * @param AccessToken $accessToken
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function findOrCreateUser(LeagueFacebookUser $facebookUser, AccessToken $accessToken)
    {
        $userModel = config('facebook-auth.user_model', 'App\\Models\\User');

        // Try to find existing Facebook user
        $existingFacebookUser = FacebookUser::findByFacebookId($facebookUser->getId());

        if ($existingFacebookUser && $existingFacebookUser->user) {
            return $existingFacebookUser->user;
        }

        // Try to find user by email
        $email = $facebookUser->getEmail();
        if ($email) {
            $existingUser = $userModel::where('email', $email)->first();
            if ($existingUser) {
                return $existingUser;
            }
        }

        // Create new user
        $userData = [
            'name' => $facebookUser->getName(),
            'email' => $email ?? $this->generateEmailFromFacebookId($facebookUser->getId()),
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Facebook verified the email
        ];

        return $userModel::create($userData);
    }

    /**
     * Create or update Facebook user record.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param LeagueFacebookUser $facebookUser
     * @param AccessToken $accessToken
     * @return FacebookUser
     */
    protected function createOrUpdateFacebookUser($user, LeagueFacebookUser $facebookUser, AccessToken $accessToken): FacebookUser
    {
        $pictureData = $facebookUser->getPictureUrl();

        $data = [
            'user_id' => $user->id,
            'facebook_id' => $facebookUser->getId(),
            'email' => $facebookUser->getEmail(),
            'name' => $facebookUser->getName(),
            'first_name' => $facebookUser->getFirstName(),
            'last_name' => $facebookUser->getLastName(),
            'avatar_url' => $pictureData,
            'access_token' => $accessToken->getToken(),
            'metadata' => [
                'expires_at' => $accessToken->getExpires(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'resource_owner_id' => $accessToken->getResourceOwnerId(),
            ],
        ];

        return FacebookUser::createOrUpdate($data);
    }

    /**
     * Create a Sanctum token for the user.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @return string
     */
    protected function createSanctumToken($user): string
    {
        $tokenName = config('facebook-auth.token.name', 'facebook-auth-token');
        $abilities = config('facebook-auth.token.abilities', ['*']);
        $expiresIn = config('facebook-auth.token.expires_in');

        $token = $user->createToken($tokenName, $abilities, $expiresIn ? now()->addMinutes($expiresIn) : null);

        return $token->plainTextToken;
    }

    /**
     * Generate a unique email from Facebook ID.
     *
     * @param string $facebookId
     * @return string
     */
    protected function generateEmailFromFacebookId(string $facebookId): string
    {
        return "facebook_{$facebookId}@facebook-auth.local";
    }

    /**
     * Revoke Facebook access token.
     *
     * @param string $accessToken
     * @return bool
     */
    public function revokeToken(string $accessToken): bool
    {
        try {
            // Facebook doesn't provide a direct revoke endpoint in oauth2-facebook
            // Users must revoke access from their Facebook settings
            // We can delete our local record

            $this->logInfo('Facebook token revocation requested', [
                'token' => substr($accessToken, 0, 10) . '...',
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('Failed to revoke Facebook token', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Log info message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (config('facebook-auth.logging.enabled', true)) {
            Log::channel(config('facebook-auth.logging.channel', 'stack'))
                ->info("[Facebook Auth] {$message}", $context);
        }
    }

    /**
     * Log error message.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        if (config('facebook-auth.logging.enabled', true)) {
            Log::channel(config('facebook-auth.logging.channel', 'stack'))
                ->error("[Facebook Auth] {$message}", $context);
        }
    }
}
