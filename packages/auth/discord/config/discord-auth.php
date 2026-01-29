<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Discord OAuth 2.0 Credentials
    |--------------------------------------------------------------------------
    |
    | Configure your Discord OAuth 2.0 credentials from Discord Developer Portal.
    | Get credentials at: https://discord.com/developers/applications
    |
    */

    'client_id' => env('DISCORD_CLIENT_ID'),
    'client_secret' => env('DISCORD_CLIENT_SECRET'),
    'redirect_uri' => env('DISCORD_REDIRECT_URI', env('APP_URL') . '/api/auth/discord/callback'),

    /*
    |--------------------------------------------------------------------------
    | OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | Define the scopes your application needs access to.
    | Available scopes: identify, email, guilds, guilds.join, gdm.join,
    | messages.read, rpc, rpc.notifications.read, bot, webhook.incoming,
    | applications.commands, applications.store.update
    |
    */

    'scopes' => [
        'identify',
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

    'user_model' => env('DISCORD_AUTH_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Discord User Model
    |--------------------------------------------------------------------------
    |
    | The model to use for storing Discord user data.
    |
    */

    'discord_user_model' => Eduardoks98\DiscordAuth\Models\DiscordUser::class,

    /*
    |--------------------------------------------------------------------------
    | Auto Create Users
    |--------------------------------------------------------------------------
    |
    | Automatically create a new user if they don't exist in the database.
    |
    */

    'auto_create_users' => env('DISCORD_AUTO_CREATE_USERS', true),

    /*
    |--------------------------------------------------------------------------
    | Auto Sync User Data
    |--------------------------------------------------------------------------
    |
    | Automatically sync user data (name, email, avatar) from Discord on login.
    |
    */

    'auto_sync_user_data' => env('DISCORD_AUTO_SYNC_USER_DATA', true),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Name
    |--------------------------------------------------------------------------
    |
    | The name to use for Sanctum tokens created via Discord authentication.
    |
    */

    'token_name' => env('DISCORD_AUTH_TOKEN_NAME', 'discord-auth-token'),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Abilities
    |--------------------------------------------------------------------------
    |
    | The abilities to assign to Sanctum tokens created via Discord authentication.
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

    'frontend_redirect_url' => env('DISCORD_AUTH_FRONTEND_REDIRECT_URL', env('FRONTEND_URL') . '/auth/callback'),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Support
    |--------------------------------------------------------------------------
    |
    | Enable refresh token support to get access tokens that last longer.
    |
    */

    'enable_refresh_token' => env('DISCORD_ENABLE_REFRESH_TOKEN', true),

    /*
    |--------------------------------------------------------------------------
    | Prompt
    |--------------------------------------------------------------------------
    |
    | Control the OAuth consent screen behavior.
    | Options: 'none', 'consent'
    | Set to 'consent' to force re-approval of the app permissions.
    |
    */

    'prompt' => env('DISCORD_PROMPT', 'none'),
];
