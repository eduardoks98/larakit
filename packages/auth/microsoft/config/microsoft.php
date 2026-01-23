<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Client ID
    |--------------------------------------------------------------------------
    |
    | The Client ID from Azure AD app registration.
    |
    */
    'client_id' => env('MICROSOFT_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Client Secret
    |--------------------------------------------------------------------------
    |
    | The Client Secret from Azure AD app registration.
    |
    */
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Tenant
    |--------------------------------------------------------------------------
    |
    | Supported values:
    | - 'common': Multi-tenant and personal Microsoft accounts
    | - 'organizations': Multi-tenant Azure AD accounts only
    | - 'consumers': Personal Microsoft accounts only
    | - '{tenant-id}': Specific Azure AD tenant (GUID or domain)
    |
    */
    'tenant' => env('MICROSOFT_TENANT', 'common'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI configured in Azure AD app registration.
    | Must match exactly with the registered redirect URI.
    |
    */
    'redirect_uri' => env('MICROSOFT_REDIRECT_URI', env('APP_URL') . '/api/auth/microsoft/callback'),

    /*
    |--------------------------------------------------------------------------
    | Microsoft OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | Default scopes requested during authentication.
    | Common scopes:
    | - openid: Required for OpenID Connect
    | - profile: User's profile information
    | - email: User's email address
    | - User.Read: Read user's profile via Microsoft Graph
    | - offline_access: Refresh tokens
    |
    */
    'scopes' => [
        'openid',
        'profile',
        'email',
        'User.Read',
    ],

    /*
    |--------------------------------------------------------------------------
    | Microsoft Graph API Version
    |--------------------------------------------------------------------------
    |
    | The Microsoft Graph API version to use.
    |
    */
    'graph_version' => env('MICROSOFT_GRAPH_VERSION', 'v1.0'),

    /*
    |--------------------------------------------------------------------------
    | Frontend Redirect URL
    |--------------------------------------------------------------------------
    |
    | The frontend URL to redirect after successful authentication.
    | The Sanctum token will be appended as query parameter.
    |
    */
    'frontend_redirect_url' => env('MICROSOFT_FRONTEND_REDIRECT_URL', env('FRONTEND_URL') . '/auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | Token Abilities
    |--------------------------------------------------------------------------
    |
    | Default abilities for Sanctum tokens created via Microsoft OAuth.
    |
    */
    'token_abilities' => [
        '*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Name
    |--------------------------------------------------------------------------
    |
    | Default name for Sanctum tokens created via Microsoft OAuth.
    |
    */
    'token_name' => 'microsoft-oauth',

    /*
    |--------------------------------------------------------------------------
    | Auto Create User
    |--------------------------------------------------------------------------
    |
    | Automatically create a user in the users table if not found.
    | If false, only microsoft_users records will be created.
    |
    */
    'auto_create_user' => env('MICROSOFT_AUTO_CREATE_USER', true),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model to use for authentication.
    |
    */
    'user_model' => env('MICROSOFT_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Store Tokens
    |--------------------------------------------------------------------------
    |
    | Store Microsoft access and refresh tokens in database.
    | Useful for making Microsoft Graph API calls on behalf of the user.
    |
    */
    'store_tokens' => env('MICROSOFT_STORE_TOKENS', true),

    /*
    |--------------------------------------------------------------------------
    | Default URL Options
    |--------------------------------------------------------------------------
    |
    | Default URL options for the Azure provider.
    | See: https://github.com/TheNetworg/oauth2-azure#usage
    |
    */
    'url_api' => 'https://graph.microsoft.com/',
    'url_authorize' => 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize',
    'url_access_token' => 'https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token',
    'url_resource_owner_details' => 'https://graph.microsoft.com/{api-version}/me',
];
