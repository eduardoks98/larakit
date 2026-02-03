<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook Audience Network Configuration
    |--------------------------------------------------------------------------
    |
    | Configure Facebook Audience Network (FAN) integration settings.
    | Note: FAN does not have traditional S2S callbacks like other networks.
    | Rewards should be handled client-side or via custom implementation.
    |
    */
    'app' => [
        // Facebook App ID
        'app_id' => env('FACEBOOK_ADS_APP_ID'),

        // Facebook App Secret
        'app_secret' => env('FACEBOOK_ADS_APP_SECRET'),

        // Access Token for Graph API
        'access_token' => env('FACEBOOK_ADS_ACCESS_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Graph API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Facebook Graph API access.
    |
    */
    'graph_api' => [
        // API version
        'version' => env('FACEBOOK_ADS_API_VERSION', 'v21.0'),

        // Base URL
        'base_url' => 'https://graph.facebook.com/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Client-Side Reward Endpoint
    |--------------------------------------------------------------------------
    |
    | Since FAN doesn't have S2S callbacks, this endpoint allows
    | your mobile app to report completed rewarded ads.
    |
    */
    'rewards' => [
        // Enable client-side reward endpoint
        'enabled' => env('FACEBOOK_ADS_REWARDS_ENABLED', true),

        // Callback route path
        'callback_path' => env('FACEBOOK_ADS_CALLBACK_PATH', '/api/ads/facebook/reward'),

        // Default reward item name
        'default_item' => env('FACEBOOK_ADS_DEFAULT_REWARD_ITEM', 'coins'),

        // Default reward amount
        'default_amount' => env('FACEBOOK_ADS_DEFAULT_REWARD_AMOUNT', 10),

        // Process rewards synchronously (false = use queue)
        'sync_processing' => env('FACEBOOK_ADS_SYNC_PROCESSING', false),

        // Require authentication for reward endpoint
        'require_auth' => env('FACEBOOK_ADS_REQUIRE_AUTH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for FAN revenue reporting via Audience Network Reporting API.
    |
    */
    'reporting' => [
        // Cache duration for reports in minutes
        'cache_duration' => env('FACEBOOK_ADS_REPORT_CACHE', 60),

        // Default metrics to fetch
        'default_metrics' => [
            'fb_ad_network_imp',
            'fb_ad_network_click',
            'fb_ad_network_revenue',
            'fb_ad_network_request',
            'fb_ad_network_filled_request',
            'fb_ad_network_fill_rate',
            'fb_ad_network_cpm',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for Facebook Ads integration.
    |
    */
    'logging' => [
        // Enable logging
        'enabled' => env('FACEBOOK_ADS_LOGGING_ENABLED', true),

        // Log channel to use
        'channel' => env('FACEBOOK_ADS_LOG_CHANNEL', null),

        // Log successful rewards
        'log_success' => env('FACEBOOK_ADS_LOG_SUCCESS', true),

        // Log failures
        'log_failures' => env('FACEBOOK_ADS_LOG_FAILURES', true),
    ],
];
