<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business Cloud API Credentials
    |--------------------------------------------------------------------------
    |
    | Get these from Meta for Developers: https://developers.facebook.com/
    | Create a WhatsApp Business App and get your credentials
    |
    */

    'from_phone_number_id' => env('WHATSAPP_FROM_PHONE_NUMBER_ID'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Business Account ID
    |--------------------------------------------------------------------------
    |
    | Your WhatsApp Business Account ID (WABA ID)
    |
    */

    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | WhatsApp Cloud API version
    |
    */

    'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook URL and verify token for receiving messages
    |
    */

    'webhook' => [
        'url' => env('WHATSAPP_WEBHOOK_URL', env('APP_URL').'/api/webhooks/whatsapp'),
        'verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'your-verify-token'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Tracking
    |--------------------------------------------------------------------------
    |
    | Enable or disable message tracking in database
    |
    */

    'track_messages' => env('WHATSAPP_TRACK_MESSAGES', true),

    /*
    |--------------------------------------------------------------------------
    | Default Template Language
    |--------------------------------------------------------------------------
    |
    | Default language code for message templates
    |
    */

    'default_language' => env('WHATSAPP_DEFAULT_LANGUAGE', 'pt_BR'),

    /*
    |--------------------------------------------------------------------------
    | Media Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for media uploads
    |
    */

    'media' => [
        'max_size_mb' => env('WHATSAPP_MEDIA_MAX_SIZE', 16), // MB
        'allowed_types' => [
            'image' => ['image/jpeg', 'image/png'],
            'video' => ['video/mp4', 'video/3gpp'],
            'audio' => ['audio/aac', 'audio/mp4', 'audio/mpeg', 'audio/amr', 'audio/ogg'],
            'document' => ['application/pdf', 'application/vnd.ms-powerpoint', 'application/msword', 'application/vnd.ms-excel'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limits (based on WhatsApp documentation)
    |
    */

    'rate_limit' => [
        'messages_per_second' => env('WHATSAPP_RATE_LIMIT_PER_SECOND', 80),
        'daily_limit' => env('WHATSAPP_DAILY_LIMIT', 100000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Retry failed messages
    |
    */

    'retry' => [
        'enabled' => env('WHATSAPP_RETRY_ENABLED', true),
        'max_attempts' => env('WHATSAPP_RETRY_MAX_ATTEMPTS', 3),
        'delay_seconds' => env('WHATSAPP_RETRY_DELAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Free Tier Configuration
    |--------------------------------------------------------------------------
    |
    | WhatsApp offers 1,000 free service conversations per month
    |
    */

    'free_tier' => [
        'enabled' => env('WHATSAPP_FREE_TIER_ENABLED', true),
        'monthly_limit' => 1000,
    ],

];
