<?php

namespace Eduardoks98\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'request_id',
        'ip',
        'route',
        'method',
        'http_code',
        'payload',
        'response',
        'response_time_ms',
        'user_agent',
        'success',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'success' => 'boolean',
        'response_time_ms' => 'integer',
        'http_code' => 'integer',
    ];

    /**
     * Scope to get failed requests.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope to get requests by IP.
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    /**
     * Scope to get requests by route.
     */
    public function scopeByRoute($query, string $route)
    {
        return $query->where('route', $route);
    }

    /**
     * Scope to get slow requests (over a threshold).
     */
    public function scopeSlow($query, int $thresholdMs = 1000)
    {
        return $query->where('response_time_ms', '>', $thresholdMs);
    }
}
