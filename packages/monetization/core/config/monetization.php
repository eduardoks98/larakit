<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the default virtual currency settings for your application.
    |
    */
    'currency' => [
        'name' => env('MONETIZATION_CURRENCY_NAME', 'coins'),
        'symbol' => env('MONETIZATION_CURRENCY_SYMBOL', ''),
        'initial_balance' => env('MONETIZATION_INITIAL_BALANCE', 0),
        'max_balance' => env('MONETIZATION_MAX_BALANCE', 999999999),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reward Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for reward processing and fulfillment.
    |
    */
    'rewards' => [
        // Queue name for reward fulfillment jobs
        'queue' => env('MONETIZATION_REWARD_QUEUE', 'default'),

        // Maximum retry attempts for failed rewards
        'max_retries' => env('MONETIZATION_MAX_RETRIES', 3),

        // Delay between retries in seconds
        'retry_delay' => env('MONETIZATION_RETRY_DELAY', 60),

        // Enable duplicate detection (based on transaction_id)
        'duplicate_detection' => env('MONETIZATION_DUPLICATE_DETECTION', true),

        // Time window for duplicate detection in seconds (24 hours)
        'duplicate_window' => env('MONETIZATION_DUPLICATE_WINDOW', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ad Impression Tracking
    |--------------------------------------------------------------------------
    |
    | Configure how ad impressions are tracked and stored.
    |
    */
    'impressions' => [
        // Enable impression tracking
        'enabled' => env('MONETIZATION_TRACK_IMPRESSIONS', true),

        // Retention period for impressions in days (0 = forever)
        'retention_days' => env('MONETIZATION_IMPRESSION_RETENTION', 90),

        // Track user agent information
        'track_user_agent' => env('MONETIZATION_TRACK_USER_AGENT', false),

        // Track IP address (be mindful of privacy regulations)
        'track_ip' => env('MONETIZATION_TRACK_IP', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for monetization analytics and reporting.
    |
    */
    'analytics' => [
        // Enable analytics collection
        'enabled' => env('MONETIZATION_ANALYTICS_ENABLED', true),

        // Cache duration for analytics queries in minutes
        'cache_duration' => env('MONETIZATION_ANALYTICS_CACHE', 60),

        // Default timezone for reports
        'timezone' => env('MONETIZATION_TIMEZONE', 'UTC'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the monetization package.
    |
    */
    'tables' => [
        'impressions' => 'ad_impressions',
        'rewards' => 'rewards',
        'transactions' => 'virtual_currency_transactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class that should be used for relationships.
    |
    */
    'user_model' => env('MONETIZATION_USER_MODEL', 'App\\Models\\User'),
];
