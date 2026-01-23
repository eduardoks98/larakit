<?php

namespace Eduardoks98\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'device_id',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userModel);
    }

    /**
     * Scope to get active sessions (activity within last 30 minutes).
     */
    public function scopeActive($query, int $minutes = 30)
    {
        return $query->where('last_activity', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope to get inactive sessions.
     */
    public function scopeInactive($query, int $minutes = 30)
    {
        return $query->where('last_activity', '<', now()->subMinutes($minutes));
    }

    /**
     * Scope to get sessions by user ID.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if session is currently active (activity within last 30 minutes).
     */
    public function isActive(int $minutes = 30): bool
    {
        return $this->last_activity && $this->last_activity->gte(now()->subMinutes($minutes));
    }

    /**
     * Get human-readable last activity time.
     */
    public function getLastActivityHumanAttribute(): string
    {
        return $this->last_activity ? $this->last_activity->diffForHumans() : 'Never';
    }

    /**
     * Get browser name from user agent.
     */
    public function getBrowserAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        if (preg_match('/Firefox/i', $this->user_agent)) return 'Firefox';
        if (preg_match('/Chrome/i', $this->user_agent)) return 'Chrome';
        if (preg_match('/Safari/i', $this->user_agent)) return 'Safari';
        if (preg_match('/Edge/i', $this->user_agent)) return 'Edge';
        if (preg_match('/Opera|OPR/i', $this->user_agent)) return 'Opera';

        return 'Unknown';
    }

    /**
     * Get platform from user agent.
     */
    public function getPlatformAttribute(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        if (preg_match('/Windows/i', $this->user_agent)) return 'Windows';
        if (preg_match('/Mac OS X/i', $this->user_agent)) return 'macOS';
        if (preg_match('/Linux/i', $this->user_agent)) return 'Linux';
        if (preg_match('/Android/i', $this->user_agent)) return 'Android';
        if (preg_match('/iOS|iPhone|iPad/i', $this->user_agent)) return 'iOS';

        return 'Unknown';
    }
}
