<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Converx API Configuration
    |--------------------------------------------------------------------------
    |
    | Converx is a Chatwoot-based WhatsApp provider for Brazil
    | Get your credentials from https://converx.app
    |
    */

    'account_id' => env('CONVERX_ACCOUNT_ID', '8'),
    'api_token' => env('CONVERX_API_TOKEN'),
    'api_url' => env('CONVERX_API_URL', 'https://converx.app/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Inbox Configuration
    |--------------------------------------------------------------------------
    |
    | Default inbox ID for sending messages
    |
    */

    'inbox_id' => env('CONVERX_INBOX_ID', '1'),

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    |
    | Default templates for common use cases
    |
    */

    'templates' => [
        'lead_notification' => env('CONVERX_TEMPLATE_LEAD', 'notificar_lead_vendedor'),
        'namespace' => env('CONVERX_TEMPLATE_NAMESPACE', '2d984c77_0a6a_48a8_b1ff_0bc434fae591'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => env('CONVERX_HTTP_TIMEOUT', 60),
        'verify_ssl' => env('CONVERX_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'enabled' => env('CONVERX_RETRY_ENABLED', true),
        'max_attempts' => env('CONVERX_RETRY_MAX_ATTEMPTS', 3),
        'delay_seconds' => env('CONVERX_RETRY_DELAY_SECONDS', 60),
    ],

];
