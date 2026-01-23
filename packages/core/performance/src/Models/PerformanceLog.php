<?php

namespace Eduardoks98\Performance\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    protected $fillable = [
        'route',
        'method',
        'duration_ms',
        'query_count',
        'query_time_ms',
        'memory_mb',
        'response_size',
        'is_slow',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'query_count' => 'integer',
        'query_time_ms' => 'integer',
        'memory_mb' => 'decimal:2',
        'response_size' => 'integer',
        'is_slow' => 'boolean',
    ];

    public function scopeSlow($query)
    {
        return $query->where('is_slow', true);
    }

    public function scopeByRoute($query, string $route)
    {
        return $query->where('route', $route);
    }
}
