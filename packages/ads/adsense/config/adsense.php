<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Publisher ID
    |--------------------------------------------------------------------------
    |
    | Your Google AdSense publisher ID (ca-pub-XXXXXXXXXXXXXXXX).
    |
    */
    'publisher_id' => env('ADSENSE_PUBLISHER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable AdSense ads globally.
    |
    */
    'enabled' => env('ADSENSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | AdSense Management API
    |--------------------------------------------------------------------------
    |
    | Configuration for the AdSense Management API for revenue reporting.
    | Requires google/apiclient package and service account credentials.
    |
    */
    'api' => [
        'enabled' => env('ADSENSE_API_ENABLED', false),
        'credentials_path' => env('ADSENSE_CREDENTIALS_PATH'),
        'account_id' => env('ADSENSE_ACCOUNT_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for ad units.
    |
    */
    'defaults' => [
        'format' => 'responsive',
        'test_mode' => env('ADSENSE_TEST_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching for ad units and revenue data.
    |
    */
    'cache' => [
        'enabled' => env('ADSENSE_CACHE_ENABLED', true),
        'ttl' => env('ADSENSE_CACHE_TTL', 300), // 5 minutes
        'prefix' => 'adsense_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the admin panel routes and middleware.
    |
    */
    'admin_middleware' => ['web', 'auth'],
    'admin_prefix' => 'admin',
    'admin_layout' => 'layouts.admin',

    /*
    |--------------------------------------------------------------------------
    | Game Model
    |--------------------------------------------------------------------------
    |
    | The model class for games/projects that ad units can be associated with.
    | Set to null to disable game association.
    |
    */
    'game_model' => env('ADSENSE_GAME_MODEL', null),

    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Configure the API routes for fetching ad units.
    |
    */
    'routes' => [
        'prefix' => 'api/ads',
        'middleware' => ['api'],
    ],
];
