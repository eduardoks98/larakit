<?php

namespace Eduardoks98\Performance\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Eduardoks98\Performance\Models\PerformanceLog;

class PerformanceMonitor
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('performance.enabled')) {
            return $next($request);
        }

        // Sample rate check
        if (rand(1, 100) / 100 > config('performance.sample_rate', 1.0)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $queryCount = 0;
        $queryTime = 0;

        // Listen to DB queries
        DB::listen(function ($query) use (&$queryCount, &$queryTime) {
            $queryCount++;
            $queryTime += $query->time;
        });

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024;
        $isSlow = $duration > config('performance.slow_request_threshold', 1000);

        try {
            PerformanceLog::create([
                'route' => $request->path(),
                'method' => $request->method(),
                'duration_ms' => (int) $duration,
                'query_count' => $queryCount,
                'query_time_ms' => (int) $queryTime,
                'memory_mb' => round($memoryUsed, 2),
                'response_size' => strlen($response->getContent()),
                'is_slow' => $isSlow,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log performance: ' . $e->getMessage());
        }

        return $response;
    }
}
