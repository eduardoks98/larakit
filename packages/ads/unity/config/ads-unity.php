<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unity Ads S2S Callback Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Unity Ads server-to-server callback settings.
    |
    */
    's2s' => [
        // Enable S2S callback verification
        'enabled' => env('UNITY_ADS_S2S_ENABLED', true),

        // Secret key provided by Unity (contact Unity Support to get this)
        'secret_key' => env('UNITY_ADS_SECRET_KEY'),

        // Callback route path
        'callback_path' => env('UNITY_ADS_CALLBACK_PATH', '/api/ads/unity/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Game Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Unity game settings.
    |
    */
    'game' => [
        // Your Unity Game ID
        'game_id' => env('UNITY_ADS_GAME_ID'),

        // Allowed game IDs (leave empty to allow all)
        'allowed_game_ids' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward Configuration
    |--------------------------------------------------------------------------
    |
    | Default reward settings for Unity rewarded ads.
    |
    */
    'rewards' => [
        // Default reward item name
        'default_item' => env('UNITY_ADS_DEFAULT_REWARD_ITEM', 'coins'),

        // Default reward amount
        'default_amount' => env('UNITY_ADS_DEFAULT_REWARD_AMOUNT', 10),

        // Process rewards synchronously (false = use queue)
        'sync_processing' => env('UNITY_ADS_SYNC_PROCESSING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monetization Stats API
    |--------------------------------------------------------------------------
    |
    | Configure access to Unity's Monetization Stats API.
    |
    */
    'stats_api' => [
        // API key for stats access
        'api_key' => env('UNITY_ADS_STATS_API_KEY'),

        // Organization ID
        'organization_id' => env('UNITY_ADS_ORGANIZATION_ID'),

        // Base URL for stats API
        'base_url' => 'https://monetization.api.unity.com/stats/v1/operate/organizations/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for Unity Ads callbacks.
    |
    */
    'logging' => [
        // Enable callback logging
        'enabled' => env('UNITY_ADS_LOGGING_ENABLED', true),

        // Log channel to use
        'channel' => env('UNITY_ADS_LOG_CHANNEL', null),

        // Log successful callbacks
        'log_success' => env('UNITY_ADS_LOG_SUCCESS', true),

        // Log failed callbacks
        'log_failures' => env('UNITY_ADS_LOG_FAILURES', true),
    ],
];
