<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AdMob Server-Side Verification (SSV)
    |--------------------------------------------------------------------------
    |
    | Configure AdMob SSV settings for rewarded ad callback validation.
    |
    */
    'ssv' => [
        // Enable SSV callback verification
        'enabled' => env('ADMOB_SSV_ENABLED', true),

        // URL for Google's public key server
        'keys_url' => env('ADMOB_KEYS_URL', 'https://www.gstatic.com/admob/reward/verifier-keys.json'),

        // Cache duration for public keys in seconds (max 24 hours)
        'keys_cache_ttl' => env('ADMOB_KEYS_CACHE_TTL', 86400),

        // Cache key prefix
        'cache_prefix' => 'admob_ssv_',

        // Allowed time drift in seconds for timestamp validation
        'time_drift' => env('ADMOB_TIME_DRIFT', 300),

        // Callback route path
        'callback_path' => env('ADMOB_CALLBACK_PATH', '/api/ads/admob/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward Configuration
    |--------------------------------------------------------------------------
    |
    | Default reward settings for AdMob rewarded ads.
    |
    */
    'rewards' => [
        // Default reward item name if not specified in callback
        'default_item' => env('ADMOB_DEFAULT_REWARD_ITEM', 'coins'),

        // Default reward amount if not specified in callback
        'default_amount' => env('ADMOB_DEFAULT_REWARD_AMOUNT', 10),

        // Process rewards synchronously (false = use queue)
        'sync_processing' => env('ADMOB_SYNC_PROCESSING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ad Units
    |--------------------------------------------------------------------------
    |
    | Configure allowed ad units for validation.
    | Leave empty to allow all ad units.
    |
    */
    'ad_units' => [
        // Example: 'ca-app-pub-1234567890123456/1234567890'
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for AdMob callbacks.
    |
    */
    'logging' => [
        // Enable callback logging
        'enabled' => env('ADMOB_LOGGING_ENABLED', true),

        // Log channel to use
        'channel' => env('ADMOB_LOG_CHANNEL', null),

        // Log successful callbacks
        'log_success' => env('ADMOB_LOG_SUCCESS', true),

        // Log failed callbacks
        'log_failures' => env('ADMOB_LOG_FAILURES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Data Parser
    |--------------------------------------------------------------------------
    |
    | Configure how custom_data from callbacks should be parsed.
    | Custom data typically contains the user_id.
    |
    */
    'custom_data' => [
        // Format: 'json' or 'plain' (plain = just the user_id)
        'format' => env('ADMOB_CUSTOM_DATA_FORMAT', 'plain'),

        // Key name for user_id when format is 'json'
        'user_id_key' => env('ADMOB_USER_ID_KEY', 'user_id'),
    ],
];
