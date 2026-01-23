<?php

namespace Eduardoks98\BaseApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetApiHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set standard API headers
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-API-Version', config('base-api.api_version', 'v1'));
        $response->headers->set('X-Request-ID', (string) Str::uuid());

        return $response;
    }
}
