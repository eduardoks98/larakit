<?php

use Eduardoks98\Auth\Services\TokenService;
use Eduardoks98\Auth\Services\SessionService;

if (!function_exists('createAccessToken')) {
    /**
     * Create an access token for a user.
     *
     * @param mixed $user
     * @param string $deviceName
     * @param string|null $deviceId
     * @param array $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    function createAccessToken($user, string $deviceName, ?string $deviceId = null, array $abilities = ['*'])
    {
        return app(TokenService::class)->createAccessToken($user, $deviceName, $deviceId, $abilities);
    }
}

if (!function_exists('createRefreshToken')) {
    /**
     * Create a refresh token for a user.
     *
     * @param mixed $user
     * @param string $deviceName
     * @param string|null $deviceId
     * @return \Laravel\Sanctum\NewAccessToken
     */
    function createRefreshToken($user, string $deviceName, ?string $deviceId = null)
    {
        return app(TokenService::class)->createRefreshToken($user, $deviceName, $deviceId);
    }
}

if (!function_exists('refreshAccessToken')) {
    /**
     * Refresh an access token using a refresh token.
     *
     * @param string $refreshTokenString
     * @return array
     * @throws \Exception
     */
    function refreshAccessToken(string $refreshTokenString): array
    {
        return app(TokenService::class)->refreshAccessToken($refreshTokenString);
    }
}

if (!function_exists('revokeDeviceTokens')) {
    /**
     * Revoke all tokens for a specific device.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return int
     */
    function revokeDeviceTokens($user, string $deviceId): int
    {
        return app(TokenService::class)->revokeDeviceTokens($user, $deviceId);
    }
}

if (!function_exists('getUserDevices')) {
    /**
     * Get all active devices for a user.
     *
     * @param mixed $user
     * @return array
     */
    function getUserDevices($user): array
    {
        return app(TokenService::class)->getUserDevices($user);
    }
}

if (!function_exists('cleanupExpiredTokens')) {
    /**
     * Clean up expired authentication tokens.
     *
     * @return int Number of tokens deleted
     */
    function cleanupExpiredTokens(): int
    {
        return app(TokenService::class)->cleanupExpiredTokens();
    }
}

if (!function_exists('createUserSession')) {
    /**
     * Create or update a user session.
     *
     * @param mixed $user
     * @param \Illuminate\Http\Request $request
     * @param string $deviceId
     * @return \Eduardoks98\Auth\Models\UserSession|null
     */
    function createUserSession($user, $request, string $deviceId)
    {
        return app(SessionService::class)->createSession($user, $request, $deviceId);
    }
}

if (!function_exists('endUserSession')) {
    /**
     * End a specific user session.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return bool
     */
    function endUserSession($user, string $deviceId): bool
    {
        return app(SessionService::class)->endSession($user, $deviceId);
    }
}

if (!function_exists('endAllUserSessions')) {
    /**
     * End all sessions for a user.
     *
     * @param mixed $user
     * @return int Number of sessions ended
     */
    function endAllUserSessions($user): int
    {
        return app(SessionService::class)->endAllSessions($user);
    }
}

if (!function_exists('getUserSessions')) {
    /**
     * Get all active sessions for a user.
     *
     * @param mixed $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getUserSessions($user)
    {
        return app(SessionService::class)->getUserSessions($user);
    }
}

if (!function_exists('cleanupOldSessions')) {
    /**
     * Clean up old inactive sessions.
     *
     * @return int Number of sessions cleaned up
     */
    function cleanupOldSessions(): int
    {
        return app(SessionService::class)->cleanupOldSessions();
    }
}

if (!function_exists('generateDeviceId')) {
    /**
     * Generate a device ID from request information.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    function generateDeviceId($request): string
    {
        return app(TokenService::class)->generateDeviceId($request);
    }
}

if (!function_exists('hasTokenAbility')) {
    /**
     * Check if current user's token has a specific ability.
     *
     * @param string $ability
     * @return bool
     */
    function hasTokenAbility(string $ability): bool
    {
        $user = request()->user();

        if (!$user || !$user->currentAccessToken()) {
            return false;
        }

        return $user->tokenCan($ability);
    }
}

if (!function_exists('getTokenAbilities')) {
    /**
     * Get all abilities for the current user's token.
     *
     * @return array
     */
    function getTokenAbilities(): array
    {
        $user = request()->user();

        if (!$user || !$user->currentAccessToken()) {
            return [];
        }

        return $user->currentAccessToken()->abilities ?? [];
    }
}
