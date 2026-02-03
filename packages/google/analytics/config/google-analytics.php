<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Measurement ID
    |--------------------------------------------------------------------------
    |
    | Your Google Analytics 4 Measurement ID (G-XXXXXXXXXX).
    |
    */
    'measurement_id' => env('GA_MEASUREMENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable Google Analytics tracking globally.
    |
    */
    'enabled' => env('GA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode to see events in GA4 DebugView.
    |
    */
    'debug' => env('GA_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Anonymize IP
    |--------------------------------------------------------------------------
    |
    | Anonymize IP addresses for GDPR compliance.
    |
    */
    'anonymize_ip' => env('GA_ANONYMIZE_IP', true),

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Configure cookie behavior for analytics.
    |
    */
    'cookie' => [
        'domain' => env('GA_COOKIE_DOMAIN', 'auto'),
        'expires' => env('GA_COOKIE_EXPIRES', 63072000), // 2 years in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    |
    | Only track in these environments.
    |
    */
    'track_in_environments' => ['production'],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should not be tracked (e.g., admin pages).
    |
    */
    'excluded_routes' => [
        'admin/*',
        'api/*',
    ],
];
