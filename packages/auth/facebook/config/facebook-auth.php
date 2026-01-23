<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facebook App Credentials
    |--------------------------------------------------------------------------
    |
    | Your Facebook App credentials from https://developers.facebook.com/apps
    | Create a new app and get your App ID and App Secret
    |
    */

    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Facebook Graph API Version
    |--------------------------------------------------------------------------
    |
    | The Facebook Graph API version to use
    | Format: vX.X (e.g., 'v19.0')
    | See: https://developers.facebook.com/docs/graph-api/changelog
    |
    */

    'graph_api_version' => env('FACEBOOK_GRAPH_API_VERSION', 'v19.0'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI for OAuth callback
    | Must match the redirect URI configured in your Facebook App settings
    |
    */

    'redirect_uri' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/api/facebook-auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | The OAuth scopes to request from Facebook
    | Common scopes: email, public_profile, user_friends, user_birthday
    | See: https://developers.facebook.com/docs/permissions/reference
    |
    */

    'scopes' => [
        'email',
        'public_profile',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Fields
    |--------------------------------------------------------------------------
    |
    | The user fields to request from Facebook Graph API
    | See: https://developers.facebook.com/docs/graph-api/reference/user
    |
    */

    'user_fields' => [
        'id',
        'name',
        'email',
        'first_name',
        'last_name',
        'picture.type(large)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Redirect URL
    |--------------------------------------------------------------------------
    |
    | The frontend URL to redirect after successful authentication
    | The access token will be appended as a query parameter
    |
    */

    'frontend_redirect_url' => env('FACEBOOK_FRONTEND_REDIRECT_URL', env('FRONTEND_URL') . '/auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Table names for storing Facebook authentication data
    |
    */

    'tables' => [
        'facebook_users' => 'facebook_users',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The User model class to use for authentication
    |
    */

    'user_model' => env('FACEBOOK_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel Sanctum tokens
    |
    */

    'token' => [
        'name' => 'facebook-auth-token',
        'abilities' => ['*'],
        'expires_in' => null, // null = never expires, or specify minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging for debugging
    |
    */

    'logging' => [
        'enabled' => env('FACEBOOK_AUTH_LOGGING_ENABLED', true),
        'channel' => env('FACEBOOK_AUTH_LOG_CHANNEL', 'stack'),
    ],

];
