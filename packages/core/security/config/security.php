<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Configure Content Security Policy directives.
    |
    */
    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),

        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", 'https://www.google.com', 'https://www.gstatic.com'],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'", 'data:'],
            'connect-src' => ["'self'"],
            'frame-ancestors' => ["'none'"],
        ],

        // Report violations to this endpoint
        'report-uri' => env('CSP_REPORT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Standard security headers to be applied.
    |
    */
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | Force HTTPS connections.
    |
    */
    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', true),
        'max_age' => 31536000, // 1 year in seconds
        'include_subdomains' => true,
        'preload' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Blocking
    |--------------------------------------------------------------------------
    |
    | Database-driven IP blocking configuration.
    |
    */
    'ip_blocking' => [
        'enabled' => env('SECURITY_IP_BLOCKING_ENABLED', true),
        'whitelist_enabled' => true,
        'geolocation_enabled' => env('SECURITY_GEOLOCATION_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | JWE encryption settings.
    |
    */
    'encryption' => [
        'algorithm' => 'A256GCM',
        'key_encryption' => 'A256KW',
    ],
];
