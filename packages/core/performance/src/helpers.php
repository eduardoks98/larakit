<?php

use Eduardoks98\Performance\Models\PerformanceLog;

if (!function_exists('getSlowRequests')) {
    function getSlowRequests(int $limit = 100)
    {
        return PerformanceLog::slow()
            ->orderBy('duration_ms', 'desc')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('getPerformanceStats')) {
    function getPerformanceStats(int $days = 7): array
    {
        $since = now()->subDays($days);

        $stats = PerformanceLog::where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(duration_ms) as avg_duration,
                MAX(duration_ms) as max_duration,
                AVG(query_count) as avg_queries,
                AVG(memory_mb) as avg_memory,
                SUM(CASE WHEN is_slow = 1 THEN 1 ELSE 0 END) as slow_requests
            ')
            ->first();

        return [
            'total_requests' => $stats->total_requests ?? 0,
            'avg_duration_ms' => round($stats->avg_duration ?? 0, 2),
            'max_duration_ms' => $stats->max_duration ?? 0,
            'avg_queries' => round($stats->avg_queries ?? 0, 2),
            'avg_memory_mb' => round($stats->avg_memory ?? 0, 2),
            'slow_requests' => $stats->slow_requests ?? 0,
            'slow_percentage' => $stats->total_requests > 0
                ? round(($stats->slow_requests / $stats->total_requests) * 100, 2)
                : 0,
        ];
    }
}

if (!function_exists('preventN1Query')) {
    function preventN1Query(): void
    {
        Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());
    }
}
