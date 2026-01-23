<?php

namespace Eduardoks98\RateLimiter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Eduardoks98\RateLimiter\Services\ThrottleService;
use Eduardoks98\RateLimiter\Services\IpWhitelistService;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class GenericThrottle
{
    protected ThrottleService $throttleService;
    protected IpWhitelistService $whitelistService;

    public function __construct(ThrottleService $throttleService, IpWhitelistService $whitelistService)
    {
        $this->throttleService = $throttleService;
        $this->whitelistService = $whitelistService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('rate-limiter.enabled')) {
            return $next($request);
        }

        $ip = $request->ip();
        $route = $request->path();
        $method = $request->method();

        // Check if IP is whitelisted (bypass all checks)
        if ($this->whitelistService->isWhitelisted($ip)) {
            return $this->processRequest($request, $next, $ip, $route, $method);
        }

        // Check if IP is globally banned (Tier 3)
        if ($this->throttleService->isIpBanned($ip)) {
            return $this->blockedResponse($request, $ip, $route, 'IP globally banned', 403);
        }

        // Check geolocation restrictions
        if ($geoResponse = $this->checkGeolocation($request, $ip)) {
            return $geoResponse;
        }

        // Check for injection attempts
        if ($this->throttleService->detectInjection($request->all())) {
            $this->throttleService->banIp($ip, 'Injection attempt detected', [
                'route' => $route,
                'payload' => $request->all(),
            ]);

            return $this->blockedResponse($request, $ip, $route, 'Malicious payload detected', 403);
        }

        // Check volume anomaly
        if ($this->throttleService->analyzeVolumeAnomaly($ip, $route)) {
            \Log::warning("Volume anomaly detected for IP {$ip} on route {$route}");
        }

        $decayMinutes = config('rate-limiter.decay_minutes', 1);

        // Tier 1: Per-Route Global Limit
        $tier1Key = "rate_limiter:tier1:{$route}";
        $tier1Max = config('rate-limiter.route_max_attempts', 60);

        if ($this->throttleService->checkRateLimit($tier1Key, $tier1Max, $decayMinutes)) {
            return $this->rateLimitedResponse($request, $ip, $route, 'Route limit exceeded (Tier 1)', $tier1Key, $tier1Max);
        }

        // Tier 2: Per-IP + Route Limit
        $tier2Key = "rate_limiter:tier2:{$ip}:{$route}";
        $tier2Max = config('rate-limiter.ip_route_max_attempts', 30);

        if ($this->throttleService->checkRateLimit($tier2Key, $tier2Max, $decayMinutes)) {
            return $this->rateLimitedResponse($request, $ip, $route, 'IP+Route limit exceeded (Tier 2)', $tier2Key, $tier2Max);
        }

        // Tier 3: Global IP Limit (across all routes)
        $tier3Key = "rate_limiter:tier3:{$ip}";
        $tier3Max = config('rate-limiter.max_attempts_before_ban', 100);

        if ($this->throttleService->checkRateLimit($tier3Key, $tier3Max, $decayMinutes)) {
            // Ban the IP globally
            $this->throttleService->banIp($ip, 'Exceeded global rate limit (Tier 3)', [
                'route' => $route,
                'attempts' => $tier3Max,
            ]);

            return $this->blockedResponse($request, $ip, $route, 'Global IP limit exceeded - IP banned', 429);
        }

        // Record attempts for all tiers
        $this->throttleService->recordAttempt($tier1Key, $decayMinutes);
        $this->throttleService->recordAttempt($tier2Key, $decayMinutes);
        $this->throttleService->recordAttempt($tier3Key, $decayMinutes);

        // Process request and log it
        return $this->processRequest($request, $next, $ip, $route, $method);
    }

    /**
     * Check geolocation-based restrictions.
     *
     * @param Request $request
     * @param string $ip
     * @return Response|null
     */
    protected function checkGeolocation(Request $request, string $ip): ?Response
    {
        if (!config('rate-limiter.geolocation_enabled')) {
            return null;
        }

        $highRiskCountries = config('rate-limiter.high_risk_countries', []);

        if (empty($highRiskCountries)) {
            return null;
        }

        try {
            $location = Location::get($ip);

            if ($location && in_array($location->countryCode, $highRiskCountries)) {
                $action = config('rate-limiter.high_risk_action', 'strict');

                if ($action === 'block') {
                    return $this->blockedResponse(
                        $request,
                        $ip,
                        $request->path(),
                        'Access from high-risk country blocked',
                        403
                    );
                }

                if ($action === 'strict') {
                    // Apply 50% stricter limits (handled in rate limit checks)
                    // Log for analytics
                    \Log::info("High-risk country access: {$location->countryCode} from IP {$ip}");
                }
            }
        } catch (\Throwable $e) {
            // Silent fail - don't block request if geolocation fails
            \Log::error("Geolocation check failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Process the request and log it.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $ip
     * @param string $route
     * @param string $method
     * @return Response
     */
    protected function processRequest(Request $request, Closure $next, string $ip, string $route, string $method): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

        $this->throttleService->logRequest([
            'request_id' => (string) Str::uuid(),
            'ip' => $ip,
            'route' => $route,
            'method' => $method,
            'http_code' => $response->getStatusCode(),
            'payload' => $request->all(),
            'response' => $this->getResponseData($response),
            'response_time_ms' => (int) $duration,
            'user_agent' => $request->userAgent(),
            'success' => $response->isSuccessful(),
            'error_message' => $response->isSuccessful() ? null : 'Request failed',
        ]);

        return $response;
    }

    /**
     * Return a rate-limited response.
     *
     * @param Request $request
     * @param string $ip
     * @param string $route
     * @param string $reason
     * @param string $key
     * @param int $maxAttempts
     * @return Response
     */
    protected function rateLimitedResponse(Request $request, string $ip, string $route, string $reason, string $key, int $maxAttempts): Response
    {
        $remaining = $this->throttleService->getRemainingAttempts($key, $maxAttempts);
        $resetTime = $this->throttleService->getResetTime($key);

        $this->throttleService->logRequest([
            'request_id' => (string) Str::uuid(),
            'ip' => $ip,
            'route' => $route,
            'method' => $request->method(),
            'http_code' => 429,
            'payload' => $request->all(),
            'response' => ['error' => $reason],
            'response_time_ms' => 0,
            'user_agent' => $request->userAgent(),
            'success' => false,
            'error_message' => $reason,
        ]);

        return response()->json([
            'type' => 'https://tools.ietf.org/html/rfc6585#section-4',
            'title' => 'Too Many Requests',
            'status' => 429,
            'detail' => $reason,
            'instance' => $request->path(),
            'remaining' => $remaining,
            'reset_in_seconds' => $resetTime,
        ], 429, [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => now()->addSeconds($resetTime)->timestamp,
            'Retry-After' => $resetTime,
        ]);
    }

    /**
     * Return a blocked response.
     *
     * @param Request $request
     * @param string $ip
     * @param string $route
     * @param string $reason
     * @param int $statusCode
     * @return Response
     */
    protected function blockedResponse(Request $request, string $ip, string $route, string $reason, int $statusCode): Response
    {
        $this->throttleService->logRequest([
            'request_id' => (string) Str::uuid(),
            'ip' => $ip,
            'route' => $route,
            'method' => $request->method(),
            'http_code' => $statusCode,
            'payload' => $request->all(),
            'response' => ['error' => $reason],
            'response_time_ms' => 0,
            'user_agent' => $request->userAgent(),
            'success' => false,
            'error_message' => $reason,
        ]);

        return response()->json([
            'type' => 'https://api.example.com/errors/access-denied',
            'title' => 'Access Denied',
            'status' => $statusCode,
            'detail' => $reason,
            'instance' => $request->path(),
        ], $statusCode);
    }

    /**
     * Extract response data for logging.
     *
     * @param Response $response
     * @return array|null
     */
    protected function getResponseData(Response $response): ?array
    {
        $content = $response->getContent();

        if (empty($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return ['raw' => substr($content, 0, 500)]; // Truncate large responses
    }
}
