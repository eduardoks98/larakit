<?php

namespace Eduardoks98\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Eduardoks98\Security\Services\IpBlockingService;

class BannedIP
{
    /**
     * IP Blocking Service.
     *
     * @var IpBlockingService
     */
    protected IpBlockingService $ipBlockingService;

    /**
     * Constructor.
     */
    public function __construct(IpBlockingService $ipBlockingService)
    {
        $this->ipBlockingService = $ipBlockingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.ip_blocking.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip();

        // Check if IP is whitelisted
        if ($this->ipBlockingService->isWhitelisted($ip)) {
            return $next($request);
        }

        // Check if IP is banned
        if ($this->ipBlockingService->isBanned($ip)) {
            return response()->json([
                'type' => 'https://api.example.com/errors/ip-banned',
                'title' => 'IP Banned',
                'status' => 403,
                'detail' => 'Your IP address has been banned due to suspicious activity.',
            ], 403);
        }

        return $next($request);
    }
}
