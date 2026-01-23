<?php

namespace Eduardoks98\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply standard security headers
        $headers = config('security.headers', []);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        // Apply HSTS if enabled
        if (config('security.hsts.enabled', true)) {
            $hstsValue = 'max-age=' . config('security.hsts.max_age', 31536000);

            if (config('security.hsts.include_subdomains', true)) {
                $hstsValue .= '; includeSubDomains';
            }

            if (config('security.hsts.preload', false)) {
                $hstsValue .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $hstsValue);
        }

        // Apply CSP if enabled
        if (config('security.csp.enabled', true)) {
            $csp = $this->buildCspHeader();
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }

    /**
     * Build Content Security Policy header value.
     *
     * @return string
     */
    protected function buildCspHeader(): string
    {
        $directives = config('security.csp.directives', []);
        $cspParts = [];

        foreach ($directives as $directive => $sources) {
            if (is_array($sources)) {
                $cspParts[] = $directive . ' ' . implode(' ', $sources);
            } else {
                $cspParts[] = $directive . ' ' . $sources;
            }
        }

        // Add report-uri if configured
        $reportUri = config('security.csp.report-uri');
        if ($reportUri) {
            $cspParts[] = 'report-uri ' . $reportUri;
        }

        return implode('; ', $cspParts);
    }
}
