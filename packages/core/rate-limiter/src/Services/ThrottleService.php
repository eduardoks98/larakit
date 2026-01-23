<?php

namespace Eduardoks98\RateLimiter\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Eduardoks98\RateLimiter\Models\ApiRequestLog;
use Eduardoks98\RateLimiter\Models\ApiRequestStat;
use Eduardoks98\Security\Services\IpBlockingService;

class ThrottleService
{
    protected IpBlockingService $ipBlockingService;

    public function __construct(IpBlockingService $ipBlockingService)
    {
        $this->ipBlockingService = $ipBlockingService;
    }

    /**
     * Check if rate limit is exceeded for a given key.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return bool
     */
    public function checkRateLimit(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return true; // Rate limit exceeded
        }

        return false;
    }

    /**
     * Record an attempt for rate limiting.
     *
     * @param string $key
     * @param int $decayMinutes
     * @return int Current attempt count
     */
    public function recordAttempt(string $key, int $decayMinutes): int
    {
        $attempts = Cache::get($key, 0);
        $attempts++;

        Cache::put($key, $attempts, now()->addMinutes($decayMinutes));

        return $attempts;
    }

    /**
     * Get remaining attempts for a key.
     *
     * @param string $key
     * @param int $maxAttempts
     * @return int
     */
    public function getRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = Cache::get($key, 0);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get time until rate limit resets.
     *
     * @param string $key
     * @return int Seconds until reset
     */
    public function getResetTime(string $key): int
    {
        $ttl = Cache::getStore()->getRedis()->ttl($key);
        return max(0, $ttl);
    }

    /**
     * Check if IP is banned globally (Tier 3).
     *
     * @param string $ip
     * @return bool
     */
    public function isIpBanned(string $ip): bool
    {
        return $this->ipBlockingService->isBanned($ip);
    }

    /**
     * Ban an IP globally.
     *
     * @param string $ip
     * @param string $reason
     * @param array $context
     * @return void
     */
    public function banIp(string $ip, string $reason, array $context = []): void
    {
        $this->ipBlockingService->banIp($ip, $reason, $context);
    }

    /**
     * Log API request for analytics.
     *
     * @param array $data
     * @return void
     */
    public function logRequest(array $data): void
    {
        if (!config('rate-limiter.log_requests')) {
            return;
        }

        // Skip logging if only blocked requests should be logged
        if (config('rate-limiter.log_only_blocked') && ($data['http_code'] ?? 200) < 400) {
            return;
        }

        try {
            ApiRequestLog::create($data);
            $this->updateRequestStats($data['ip'] ?? null, $data['route'] ?? null);
        } catch (\Throwable $e) {
            // Silent fail - don't break the request if logging fails
            \Log::error('Failed to log API request: ' . $e->getMessage());
        }
    }

    /**
     * Update aggregated request statistics.
     *
     * @param string|null $ip
     * @param string|null $route
     * @return void
     */
    protected function updateRequestStats(?string $ip, ?string $route): void
    {
        if (!$ip || !$route) {
            return;
        }

        $date = now()->toDateString();

        ApiRequestStat::updateOrCreate(
            [
                'ip_address' => $ip,
                'route' => $route,
                'date' => $date,
            ],
            [
                'total_requests' => DB::raw('total_requests + 1'),
            ]
        );
    }

    /**
     * Analyze traffic volume for anomaly detection.
     *
     * @param string $ip
     * @param string $route
     * @return bool True if anomaly detected
     */
    public function analyzeVolumeAnomaly(string $ip, string $route): bool
    {
        if (!config('rate-limiter.volume_anomaly_detection')) {
            return false;
        }

        // Get average requests for this IP+route over last 7 days
        $avgRequests = ApiRequestStat::where('ip_address', $ip)
            ->where('route', $route)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->avg('total_requests') ?? 0;

        if ($avgRequests == 0) {
            return false; // No historical data
        }

        // Get today's requests so far
        $todayRequests = ApiRequestStat::where('ip_address', $ip)
            ->where('route', $route)
            ->where('date', now()->toDateString())
            ->value('total_requests') ?? 0;

        $threshold = $avgRequests * config('rate-limiter.anomaly_threshold_multiplier', 3);

        return $todayRequests > $threshold;
    }

    /**
     * Detect injection attempts in request data.
     *
     * @param array $payload
     * @return bool True if injection detected
     */
    public function detectInjection(array $payload): bool
    {
        if (!config('rate-limiter.injection_detection')) {
            return false;
        }

        $jsonPayload = json_encode($payload);

        // SQL injection patterns
        $sqlPatterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bexec\b.*\bxp_)/i',
            '/(;.*--)/i',
            '/(\bor\b.*=.*)/i',
            "/(';|\"--|or 1=1)/i",
        ];

        // XSS patterns
        $xssPatterns = [
            '/(<script[^>]*>.*<\/script>)/i',
            '/(<iframe[^>]*>)/i',
            '/(javascript:)/i',
            '/(onerror=)/i',
            '/(onload=)/i',
            '/(<img[^>]*onerror)/i',
        ];

        // Path traversal patterns
        $traversalPatterns = [
            '/(\.\.\/)/i',
            '/(\.\.\\\\)/i',
        ];

        $allPatterns = array_merge($sqlPatterns, $xssPatterns, $traversalPatterns);

        foreach ($allPatterns as $pattern) {
            if (preg_match($pattern, $jsonPayload)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear all rate limit counters for a specific key.
     *
     * @param string $key
     * @return void
     */
    public function clearRateLimit(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Get current attempt count for a key.
     *
     * @param string $key
     * @return int
     */
    public function getAttempts(string $key): int
    {
        return Cache::get($key, 0);
    }
}
