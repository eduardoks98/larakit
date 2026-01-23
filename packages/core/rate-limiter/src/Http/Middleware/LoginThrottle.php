<?php

namespace Eduardoks98\RateLimiter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Eduardoks98\RateLimiter\Services\ThrottleService;
use Eduardoks98\RateLimiter\Services\IpWhitelistService;
use Symfony\Component\HttpFoundation\Response;

class LoginThrottle
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
     * @param int $maxAttempts Maximum login attempts (default: 5)
     * @param int $decayMinutes Time window in minutes (default: 15)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 15)
    {
        if (!config('rate-limiter.enabled')) {
            return $next($request);
        }

        $ip = $request->ip();
        $route = $request->path();

        // Check if IP is whitelisted
        if ($this->whitelistService->isWhitelisted($ip)) {
            return $next($request);
        }

        // Check if IP is globally banned
        if ($this->throttleService->isIpBanned($ip)) {
            return $this->blockedResponse($request, $ip, $route);
        }

        // Create rate limit key for login attempts
        $key = "rate_limiter:login:{$ip}";

        // Check if rate limit exceeded
        if ($this->throttleService->checkRateLimit($key, $maxAttempts, $decayMinutes)) {
            $resetTime = $this->throttleService->getResetTime($key);

            // If exceeded significantly, consider banning
            $attempts = $this->throttleService->getAttempts($key);
            if ($attempts >= $maxAttempts * 3) {
                $this->throttleService->banIp($ip, 'Multiple failed login attempts', [
                    'route' => $route,
                    'attempts' => $attempts,
                ]);

                return $this->blockedResponse($request, $ip, $route);
            }

            return $this->rateLimitedResponse($request, $ip, $route, $key, $maxAttempts, $resetTime);
        }

        // Record the attempt
        $this->throttleService->recordAttempt($key, $decayMinutes);

        return $next($request);
    }

    /**
     * Return a rate-limited response.
     *
     * @param Request $request
     * @param string $ip
     * @param string $route
     * @param string $key
     * @param int $maxAttempts
     * @param int $resetTime
     * @return Response
     */
    protected function rateLimitedResponse(Request $request, string $ip, string $route, string $key, int $maxAttempts, int $resetTime): Response
    {
        $remaining = $this->throttleService->getRemainingAttempts($key, $maxAttempts);

        $this->throttleService->logRequest([
            'request_id' => (string) Str::uuid(),
            'ip' => $ip,
            'route' => $route,
            'method' => $request->method(),
            'http_code' => 429,
            'payload' => ['username' => $request->input('username', 'N/A')], // Don't log password
            'response' => ['error' => 'Too many login attempts'],
            'response_time_ms' => 0,
            'user_agent' => $request->userAgent(),
            'success' => false,
            'error_message' => 'Too many login attempts',
        ]);

        return response()->json([
            'type' => 'https://tools.ietf.org/html/rfc6585#section-4',
            'title' => 'Too Many Requests',
            'status' => 429,
            'detail' => 'Too many login attempts. Please try again later.',
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
     * @return Response
     */
    protected function blockedResponse(Request $request, string $ip, string $route): Response
    {
        $this->throttleService->logRequest([
            'request_id' => (string) Str::uuid(),
            'ip' => $ip,
            'route' => $route,
            'method' => $request->method(),
            'http_code' => 403,
            'payload' => ['username' => $request->input('username', 'N/A')],
            'response' => ['error' => 'IP banned'],
            'response_time_ms' => 0,
            'user_agent' => $request->userAgent(),
            'success' => false,
            'error_message' => 'IP banned due to excessive login attempts',
        ]);

        return response()->json([
            'type' => 'https://api.example.com/errors/access-denied',
            'title' => 'Access Denied',
            'status' => 403,
            'detail' => 'Your IP has been blocked due to excessive failed login attempts.',
            'instance' => $request->path(),
        ], 403);
    }
}
