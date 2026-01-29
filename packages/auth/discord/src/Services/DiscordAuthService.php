<?php

namespace Eduardoks98\DiscordAuth\Services;

use Eduardoks98\DiscordAuth\Models\DiscordUser;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Wohali\OAuth2\Client\Provider\Discord;

class DiscordAuthService
{
    /**
     * The Discord OAuth provider instance.
     */
    protected Discord $provider;

    /**
     * Create a new DiscordAuthService instance.
     */
    public function __construct()
    {
        $this->provider = new Discord([
            'clientId' => config('discord-auth.client_id'),
            'clientSecret' => config('discord-auth.client_secret'),
            'redirectUri' => config('discord-auth.redirect_uri'),
        ]);
    }

    /**
     * Get the authorization URL.
     */
    public function getAuthorizationUrl(array $options = []): string
    {
        $defaultOptions = [
            'scope' => config('discord-auth.scopes', ['identify', 'email']),
            'prompt' => config('discord-auth.prompt', 'none'),
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

        // Get user details from Discord
        $discordUserData = $this->getResourceOwner($token);

        // Find or create Discord user
        $discordUser = $this->findOrCreateDiscordUser($discordUserData, $token);

        // Find or create application user
        $user = $this->findOrCreateUser($discordUser);

        // Sync user data if enabled
        if (config('discord-auth.auto_sync_user_data', true)) {
            $this->syncUserData($user, $discordUserData);
        }

        // Create Sanctum token
        $sanctumToken = $user->createToken(
            config('discord-auth.token_name', 'discord-auth-token'),
            config('discord-auth.token_abilities', ['*'])
        );

        return [
            'user' => $user,
            'discord_user' => $discordUser,
            'token' => $sanctumToken->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Find or create a Discord user record.
     */
    protected function findOrCreateDiscordUser(array $discordUserData, AccessToken $token): DiscordUser
    {
        $discordId = $discordUserData['id'];

        $discordUser = DiscordUser::where('discord_id', $discordId)->first();

        if ($discordUser) {
            // Update existing Discord user
            $discordUser->updateToken(
                $token->getToken(),
                $token->getRefreshToken(),
                $token->getExpires() ? $token->getExpires() - time() : null
            );

            $discordUser->updateProfile([
                'email' => $discordUserData['email'] ?? null,
                'username' => $discordUserData['username'] ?? null,
                'discriminator' => $discordUserData['discriminator'] ?? null,
                'global_name' => $discordUserData['global_name'] ?? null,
                'avatar' => $this->getAvatarUrl($discordUserData),
                'banner' => $discordUserData['banner'] ?? null,
                'accent_color' => $discordUserData['accent_color'] ?? null,
                'locale' => $discordUserData['locale'] ?? null,
                'verified' => $discordUserData['verified'] ?? false,
                'mfa_enabled' => $discordUserData['mfa_enabled'] ?? false,
                'premium_type' => $discordUserData['premium_type'] ?? null,
                'flags' => $discordUserData['flags'] ?? null,
            ]);

            return $discordUser->fresh();
        }

        // Create new Discord user
        return DiscordUser::create([
            'discord_id' => $discordId,
            'email' => $discordUserData['email'] ?? null,
            'username' => $discordUserData['username'] ?? null,
            'discriminator' => $discordUserData['discriminator'] ?? null,
            'global_name' => $discordUserData['global_name'] ?? null,
            'avatar' => $this->getAvatarUrl($discordUserData),
            'banner' => $discordUserData['banner'] ?? null,
            'accent_color' => $discordUserData['accent_color'] ?? null,
            'locale' => $discordUserData['locale'] ?? null,
            'verified' => $discordUserData['verified'] ?? false,
            'mfa_enabled' => $discordUserData['mfa_enabled'] ?? false,
            'premium_type' => $discordUserData['premium_type'] ?? null,
            'flags' => $discordUserData['flags'] ?? null,
            'access_token' => $token->getToken(),
            'refresh_token' => $token->getRefreshToken(),
            'expires_in' => $token->getExpires() ? $token->getExpires() - time() : null,
            'token_type' => 'Bearer',
            'last_login_at' => now(),
        ]);
    }

    /**
     * Get the avatar URL for a Discord user.
     */
    protected function getAvatarUrl(array $discordUserData): ?string
    {
        $userId = $discordUserData['id'];
        $avatar = $discordUserData['avatar'] ?? null;

        if (! $avatar) {
            // Return default avatar URL
            $discriminator = $discordUserData['discriminator'] ?? '0';
            $index = $discriminator === '0'
                ? (intval($userId) >> 22) % 6
                : intval($discriminator) % 5;

            return "https://cdn.discordapp.com/embed/avatars/{$index}.png";
        }

        $extension = str_starts_with($avatar, 'a_') ? 'gif' : 'png';

        return "https://cdn.discordapp.com/avatars/{$userId}/{$avatar}.{$extension}";
    }

    /**
     * Find or create an application user.
     */
    protected function findOrCreateUser(DiscordUser $discordUser)
    {
        $userModel = config('discord-auth.user_model', 'App\\Models\\User');

        // If Discord user already has a linked user
        if ($discordUser->user_id) {
            return $userModel::find($discordUser->user_id);
        }

        // Try to find user by email if available
        if ($discordUser->email) {
            $user = $userModel::where('email', $discordUser->email)->first();

            if ($user) {
                // Link Discord user to existing user
                $discordUser->update(['user_id' => $user->id]);

                return $user;
            }
        }

        // Create new user if auto-create is enabled
        if (config('discord-auth.auto_create_users', true)) {
            return DB::transaction(function () use ($userModel, $discordUser) {
                $displayName = $discordUser->global_name
                    ?? $discordUser->username
                    ?? 'Discord User';

                $user = $userModel::create([
                    'name' => $displayName,
                    'email' => $discordUser->email,
                    'email_verified_at' => $discordUser->verified ? now() : null,
                    'password' => null, // No password for OAuth users
                ]);

                $discordUser->update(['user_id' => $user->id]);

                return $user;
            });
        }

        throw new \Exception('User not found and auto-create is disabled.');
    }

    /**
     * Sync user data from Discord.
     */
    protected function syncUserData($user, array $discordUserData): void
    {
        $updateData = [];

        $displayName = $discordUserData['global_name']
            ?? $discordUserData['username']
            ?? null;

        if ($displayName && $user->name !== $displayName) {
            $updateData['name'] = $displayName;
        }

        if (isset($discordUserData['email']) && $user->email !== $discordUserData['email']) {
            $updateData['email'] = $discordUserData['email'];
        }

        $avatarUrl = $this->getAvatarUrl($discordUserData);
        if ($avatarUrl && method_exists($user, 'setAvatarAttribute')) {
            $updateData['avatar'] = $avatarUrl;
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Revoke a user's Discord access.
     */
    public function revokeAccess(DiscordUser $discordUser): bool
    {
        try {
            // Discord doesn't have a token revocation endpoint via OAuth
            // We just delete the local record
            $discordUser->delete();

            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to revoke Discord access', [
                'discord_user_id' => $discordUser->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get a valid access token, refreshing if necessary.
     *
     * @throws IdentityProviderException
     */
    public function getValidAccessToken(DiscordUser $discordUser): string
    {
        if (! $discordUser->isTokenExpired()) {
            return $discordUser->access_token;
        }

        if (! $discordUser->refresh_token) {
            throw new \Exception('No refresh token available. User must re-authenticate.');
        }

        // Refresh the access token
        $newToken = $this->refreshAccessToken($discordUser->refresh_token);

        // Update the Discord user record
        $discordUser->updateToken(
            $newToken->getToken(),
            $newToken->getRefreshToken() ?? $discordUser->refresh_token,
            $newToken->getExpires() ? $newToken->getExpires() - time() : null
        );

        return $newToken->getToken();
    }
}
