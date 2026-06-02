<?php

namespace Eduardoks98\Auth\Services;

use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class TokenService
{
    /**
     * Create an access token.
     *
     * @param mixed $user
     * @param string $deviceName
     * @param string|null $deviceId
     * @param array $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createAccessToken($user, string $deviceName, ?string $deviceId = null, array $abilities = ['*'])
    {
        $expiresAt = now()->addMinutes(config('auth.sanctum.access_token_expiration', 15));

        $token = $user->createToken($deviceName, $abilities, $expiresAt);

        // Store device ID and type in token metadata
        if (config('auth.sanctum.device_id_enabled') && $deviceId) {
            PersonalAccessToken::where('id', $token->accessToken->id)->update([
                'device_id' => $deviceId,
                'type' => 'access',
            ]);
        }

        return $token;
    }

    /**
     * Create a refresh token.
     *
     * @param mixed $user
     * @param string $deviceName
     * @param string|null $deviceId
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createRefreshToken($user, string $deviceName, ?string $deviceId = null)
    {
        $expiresAt = now()->addMinutes(config('auth.sanctum.refresh_token_expiration', 10080));

        $token = $user->createToken($deviceName . '_refresh', ['refresh'], $expiresAt);

        // Store device ID and type
        if (config('auth.sanctum.device_id_enabled') && $deviceId) {
            PersonalAccessToken::where('id', $token->accessToken->id)->update([
                'device_id' => $deviceId,
                'type' => 'refresh',
            ]);
        }

        return $token;
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshTokenString
     * @return array
     * @throws \Exception
     */
    public function refreshAccessToken(string $refreshTokenString): array
    {
        // Parse token
        [$id, $token] = explode('|', $refreshTokenString, 2);

        $refreshToken = PersonalAccessToken::find($id);

        if (!$refreshToken || !hash_equals($refreshToken->token, hash('sha256', $token))) {
            throw new \Exception('Invalid refresh token');
        }

        // Check if token is expired
        if ($refreshToken->expires_at && $refreshToken->expires_at->isPast()) {
            $refreshToken->delete();
            throw new \Exception('Refresh token has expired');
        }

        // Check if it's actually a refresh token
        if (!$refreshToken->can('refresh')) {
            throw new \Exception('Token is not a refresh token');
        }

        $user = $refreshToken->tokenable;
        $deviceId = $refreshToken->device_id;
        $deviceName = str_replace('_refresh', '', $refreshToken->name);

        // Create new access token
        $newAccessToken = $this->createAccessToken($user, $deviceName, $deviceId);

        // Create new refresh token and revoke old one
        $newRefreshToken = $this->createRefreshToken($user, $deviceName, $deviceId);
        $refreshToken->delete();

        return [
            'access_token' => $newAccessToken->plainTextToken,
            'refresh_token' => $newRefreshToken->plainTextToken,
            'user' => $user,
        ];
    }

    /**
     * Generate a device ID from request information.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function generateDeviceId($request): string
    {
        $components = [
            $request->userAgent(),
            $request->ip(),
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Revoke all tokens for a specific device.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return int Number of tokens deleted
     */
    public function revokeDeviceTokens($user, string $deviceId): int
    {
        return PersonalAccessToken::where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->where('device_id', $deviceId)
            ->delete();
    }

    /**
     * Get all active tokens for a user.
     *
     * @param mixed $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTokens($user)
    {
        return PersonalAccessToken::where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active devices for a user.
     *
     * @param mixed $user
     * @return array
     */
    public function getUserDevices($user): array
    {
        $tokens = $this->getUserTokens($user);

        $devices = [];

        foreach ($tokens as $token) {
            if (!isset($devices[$token->device_id])) {
                $devices[$token->device_id] = [
                    'device_id' => $token->device_id,
                    'device_name' => str_replace('_refresh', '', $token->name),
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            } else {
                // Update last_used_at if this token was used more recently
                if ($token->last_used_at &&
                    (!$devices[$token->device_id]['last_used_at'] ||
                     $token->last_used_at->gt($devices[$token->device_id]['last_used_at']))) {
                    $devices[$token->device_id]['last_used_at'] = $token->last_used_at;
                }
            }
        }

        return array_values($devices);
    }

    /**
     * Clean up expired tokens.
     *
     * @return int Number of tokens deleted
     */
    public function cleanupExpiredTokens(): int
    {
        return PersonalAccessToken::where('expires_at', '<', now())->delete();
    }

    /**
     * Check if user has reached max devices limit.
     *
     * @param mixed $user
     * @return bool
     */
    public function hasReachedDeviceLimit($user): bool
    {
        $maxDevices = config('auth.sanctum.max_devices_per_user', 0);

        if ($maxDevices === 0) {
            return false; // Unlimited
        }

        $deviceCount = count($this->getUserDevices($user));

        return $deviceCount >= $maxDevices;
    }
}
