<?php

namespace Eduardoks98\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequestStat extends Model
{
    protected $fillable = [
        'ip_address',
        'route',
        'date',
        'total_requests',
    ];

    protected $casts = [
        'date' => 'date',
        'total_requests' => 'integer',
    ];

    public $timestamps = false;

    /**
     * Scope to get stats by IP.
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope to get stats by route.
     */
    public function scopeByRoute($query, string $route)
    {
        return $query->where('route', $route);
    }

    /**
     * Scope to get stats for a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get top IPs by request count.
     */
    public function scopeTopIps($query, int $limit = 10)
    {
        return $query->select('ip_address')
            ->selectRaw('SUM(total_requests) as total')
            ->groupBy('ip_address')
            ->orderByDesc('total')
            ->limit($limit);
    }

    /**
     * Scope to get top routes by request count.
     */
    public function scopeTopRoutes($query, int $limit = 10)
    {
        return $query->select('route')
            ->selectRaw('SUM(total_requests) as total')
            ->groupBy('route')
            ->orderByDesc('total')
            ->limit($limit);
    }
}
