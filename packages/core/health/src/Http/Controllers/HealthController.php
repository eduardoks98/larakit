<?php

namespace Eduardoks98\Health\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class HealthController extends ApiController
{
    public function index()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'uptime' => $this->getUptime(),
        ]);
    }

    public function database()
    {
        $connections = [];

        try {
            DB::connection()->getPdo();
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;

            $connections['mysql'] = [
                'status' => 'up',
                'response_time_ms' => round($responseTime, 2),
            ];
        } catch (\Throwable $e) {
            $connections['mysql'] = [
                'status' => 'down',
                'error' => $e->getMessage(),
            ];
        }

        $allHealthy = collect($connections)->every(fn($conn) => $conn['status'] === 'up');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'connections' => $connections,
        ], $allHealthy ? 200 : 503);
    }

    public function cache()
    {
        try {
            $start = microtime(true);
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);
            $responseTime = (microtime(true) - $start) * 1000;

            return response()->json([
                'status' => 'healthy',
                'driver' => config('cache.default'),
                'response_time_ms' => round($responseTime, 2),
                'test_write' => true,
                'test_read' => $value === 'test',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    public function queue()
    {
        try {
            $size = Queue::size();

            return response()->json([
                'status' => 'healthy',
                'driver' => config('queue.default'),
                'pending_jobs' => $size,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    public function full()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allHealthy ? 200 : 503);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'healthy'];
        } catch (\Throwable $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health_' . time();
            Cache::put($key, 1, 10);
            Cache::get($key);
            Cache::forget($key);
            return ['status' => 'healthy'];
        } catch (\Throwable $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    protected function checkQueue(): array
    {
        try {
            Queue::size();
            return ['status' => 'healthy'];
        } catch (\Throwable $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    protected function getUptime(): int
    {
        if (function_exists('posix_times')) {
            return posix_times()['ticks'] ?? 0;
        }
        return 0;
    }
}
