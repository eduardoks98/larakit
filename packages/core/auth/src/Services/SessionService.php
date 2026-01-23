<?php

namespace Eduardoks98\Auth\Services;

use Eduardoks98\Auth\Models\UserSession;

class SessionService
{
    /**
     * Create a new user session.
     *
     * @param mixed $user
     * @param \Illuminate\Http\Request $request
     * @param string $deviceId
     * @return UserSession|null
     */
    public function createSession($user, $request, string $deviceId): ?UserSession
    {
        if (!config('auth.session_tracking.enabled')) {
            return null;
        }

        return UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $deviceId,
            ],
            [
                'ip' => config('auth.session_tracking.log_ip_address') ? $request->ip() : null,
                'user_agent' => config('auth.session_tracking.log_user_agent') ? $request->userAgent() : null,
                'last_activity' => now(),
            ]
        );
    }

    /**
     * Update session activity.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return void
     */
    public function updateActivity($user, string $deviceId): void
    {
        if (!config('auth.session_tracking.enabled')) {
            return;
        }

        UserSession::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->update(['last_activity' => now()]);
    }

    /**
     * End a specific session.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return bool
     */
    public function endSession($user, string $deviceId): bool
    {
        return UserSession::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->delete() > 0;
    }

    /**
     * End all sessions for a user.
     *
     * @param mixed $user
     * @return int Number of sessions ended
     */
    public function endAllSessions($user): int
    {
        return UserSession::where('user_id', $user->id)->delete();
    }

    /**
     * Get all active sessions for a user.
     *
     * @param mixed $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserSessions($user)
    {
        return UserSession::where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Clean up old sessions.
     *
     * @return int Number of sessions cleaned up
     */
    public function cleanupOldSessions(): int
    {
        if (!config('auth.session_tracking.cleanup_old_sessions')) {
            return 0;
        }

        $days = config('auth.session_tracking.cleanup_after_days', 30);
        $cutoffDate = now()->subDays($days);

        return UserSession::where('last_activity', '<', $cutoffDate)->delete();
    }

    /**
     * Get session by device ID.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return UserSession|null
     */
    public function getSession($user, string $deviceId): ?UserSession
    {
        return UserSession::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();
    }

    /**
     * Check if user has an active session on a device.
     *
     * @param mixed $user
     * @param string $deviceId
     * @return bool
     */
    public function hasActiveSession($user, string $deviceId): bool
    {
        return UserSession::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->exists();
    }
}
