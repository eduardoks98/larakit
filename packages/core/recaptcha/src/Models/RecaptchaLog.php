<?php

namespace Eduardoks98\Recaptcha\Models;

use Illuminate\Database\Eloquent\Model;

class RecaptchaLog extends Model
{
    protected $fillable = [
        'ip',
        'score',
        'trust_score',
        'threshold',
        'success',
        'decision',
        'decision_reason',
        'context',
        'user_agent',
        'user_id',
        'login_attempted',
        'login_successful',
        'login_failure_reason',
    ];

    protected $casts = [
        'score' => 'decimal:3',
        'trust_score' => 'decimal:3',
        'threshold' => 'decimal:2',
        'success' => 'boolean',
        'context' => 'array',
        'login_attempted' => 'boolean',
        'login_successful' => 'boolean',
        'user_id' => 'integer',
    ];

    /**
     * Scope to get successful validations.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope to get failed validations.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to get by IP address.
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    /**
     * Scope to get by user ID.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get login attempts.
     */
    public function scopeLoginAttempts($query)
    {
        return $query->where('login_attempted', true);
    }

    /**
     * Scope to get successful logins.
     */
    public function scopeSuccessfulLogins($query)
    {
        return $query->where('login_attempted', true)
                     ->where('login_successful', true);
    }

    /**
     * Scope to get failed logins.
     */
    public function scopeFailedLogins($query)
    {
        return $query->where('login_attempted', true)
                     ->where('login_successful', false);
    }

    /**
     * Scope to get low-score validations.
     */
    public function scopeLowScore($query, float $threshold = 0.5)
    {
        return $query->where('score', '<', $threshold);
    }

    /**
     * Scope to get high-score validations.
     */
    public function scopeHighScore($query, float $threshold = 0.7)
    {
        return $query->where('score', '>=', $threshold);
    }

    /**
     * Scope to get suspicious validations.
     */
    public function scopeSuspicious($query)
    {
        return $query->where('decision', 'suspicious')
                     ->orWhere('decision', 'auto_rejected');
    }
}
