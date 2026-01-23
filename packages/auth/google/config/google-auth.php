<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google OAuth 2.0 Credentials
    |--------------------------------------------------------------------------
    |
    | Configure your Google OAuth 2.0 credentials from Google Cloud Console.
    | Get credentials at: https://console.cloud.google.com/apis/credentials
    |
    */

    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/api/auth/google/callback'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | Define the scopes your application needs access to.
    | Available scopes: https://developers.google.com/identity/protocols/oauth2/scopes
    |
    */

    'scopes' => [
        'openid',
        'profile',
        'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model to use for authentication.
    |
    */

    'user_model' => env('GOOGLE_AUTH_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Google User Model
    |--------------------------------------------------------------------------
    |
    | The model to use for storing Google user data.
    |
    */

    'google_user_model' => Eduardoks98\GoogleAuth\Models\GoogleUser::class,

    /*
    |--------------------------------------------------------------------------
    | Auto Create Users
    |--------------------------------------------------------------------------
    |
    | Automatically create a new user if they don't exist in the database.
    |
    */

    'auto_create_users' => env('GOOGLE_AUTO_CREATE_USERS', true),

    /*
    |--------------------------------------------------------------------------
    | Auto Sync User Data
    |--------------------------------------------------------------------------
    |
    | Automatically sync user data (name, email, avatar) from Google on login.
    |
    */

    'auto_sync_user_data' => env('GOOGLE_AUTO_SYNC_USER_DATA', true),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Name
    |--------------------------------------------------------------------------
    |
    | The name to use for Sanctum tokens created via Google authentication.
    |
    */

    'token_name' => env('GOOGLE_AUTH_TOKEN_NAME', 'google-auth-token'),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Abilities
    |--------------------------------------------------------------------------
    |
    | The abilities to assign to Sanctum tokens created via Google authentication.
    |
    */

    'token_abilities' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Frontend Redirect URL
    |--------------------------------------------------------------------------
    |
    | The URL to redirect to after successful authentication.
    | Token will be appended as query parameter.
    |
    */

    'frontend_redirect_url' => env('GOOGLE_AUTH_FRONTEND_REDIRECT_URL', env('FRONTEND_URL') . '/auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Support
    |--------------------------------------------------------------------------
    |
    | Enable refresh token support to get access tokens that last longer.
    |
    */

    'enable_refresh_token' => env('GOOGLE_ENABLE_REFRESH_TOKEN', true),

    /*
    |--------------------------------------------------------------------------
    | Access Type
    |--------------------------------------------------------------------------
    |
    | Set to 'offline' to receive a refresh token on first authorization.
    | Set to 'online' for normal access tokens.
    |
    */

    'access_type' => env('GOOGLE_ACCESS_TYPE', 'offline'),

    /*
    |--------------------------------------------------------------------------
    | Prompt
    |--------------------------------------------------------------------------
    |
    | Control the OAuth consent screen behavior.
    | Options: 'none', 'consent', 'select_account'
    |
    */

    'prompt' => env('GOOGLE_PROMPT', 'select_account'),
];
