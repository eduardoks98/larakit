<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AppLovin MAX S2S Callback Configuration
    |--------------------------------------------------------------------------
    |
    | Configure AppLovin MAX server-to-server callback settings.
    |
    */
    's2s' => [
        // Enable S2S callback verification
        'enabled' => env('APPLOVIN_S2S_ENABLED', true),

        // Event key for callback verification (from AppLovin Dashboard)
        'event_key' => env('APPLOVIN_EVENT_KEY'),

        // Callback route path
        'callback_path' => env('APPLOVIN_CALLBACK_PATH', '/api/ads/applovin/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | AppLovin API keys for reporting and management.
    |
    */
    'api' => [
        // Report Key for revenue reporting API
        'report_key' => env('APPLOVIN_REPORT_KEY'),

        // SDK Key (for reference)
        'sdk_key' => env('APPLOVIN_SDK_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | App Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your AppLovin app settings.
    |
    */
    'app' => [
        // Your app package name (com.example.app)
        'package_name' => env('APPLOVIN_PACKAGE_NAME'),

        // Platform (android or ios)
        'platform' => env('APPLOVIN_PLATFORM', 'android'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward Configuration
    |--------------------------------------------------------------------------
    |
    | Default reward settings for AppLovin MAX rewarded ads.
    |
    */
    'rewards' => [
        // Default reward item name
        'default_item' => env('APPLOVIN_DEFAULT_REWARD_ITEM', 'coins'),

        // Default reward amount
        'default_amount' => env('APPLOVIN_DEFAULT_REWARD_AMOUNT', 10),

        // Process rewards synchronously (false = use queue)
        'sync_processing' => env('APPLOVIN_SYNC_PROCESSING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the AppLovin Revenue Reporting API.
    |
    */
    'reporting' => [
        // Base URL for reporting API
        'base_url' => 'https://r.applovin.com/max/',

        // User-level revenue report endpoint
        'user_revenue_endpoint' => 'userAdRevenueReport',

        // Rate limit (requests per hour)
        'rate_limit' => 8000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for AppLovin callbacks.
    |
    */
    'logging' => [
        // Enable callback logging
        'enabled' => env('APPLOVIN_LOGGING_ENABLED', true),

        // Log channel to use
        'channel' => env('APPLOVIN_LOG_CHANNEL', null),

        // Log successful callbacks
        'log_success' => env('APPLOVIN_LOG_SUCCESS', true),

        // Log failed callbacks
        'log_failures' => env('APPLOVIN_LOG_FAILURES', true),
    ],
];
