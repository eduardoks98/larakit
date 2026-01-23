<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Comtele API Configuration
    |--------------------------------------------------------------------------
    |
    | Get your API key from https://sms.comtele.com.br
    | Navigate to: Developer Information → API Key
    |
    */

    'api_key' => env('COMTELE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoint
    |--------------------------------------------------------------------------
    |
    | Comtele API base URL (default: production)
    |
    */

    'api_url' => env('COMTELE_API_URL', 'https://sms.comtele.com.br/api/v2'),

    /*
    |--------------------------------------------------------------------------
    | Default Sender ID
    |--------------------------------------------------------------------------
    |
    | Internal identifier for tracking purposes (optional)
    | Helps you identify the source of messages in reports
    |
    */

    'default_sender' => env('COMTELE_DEFAULT_SENDER', 'laravel-app'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook URLs for delivery status and replies
    | Set these in Comtele dashboard: https://sms.comtele.com.br
    |
    */

    'webhook' => [
        'status_callback' => env('COMTELE_WEBHOOK_STATUS', env('APP_URL').'/api/webhooks/comtele/status'),
        'reply_callback' => env('COMTELE_WEBHOOK_REPLY', env('APP_URL').'/api/webhooks/comtele/reply'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Tracking
    |--------------------------------------------------------------------------
    |
    | Enable or disable delivery status tracking in database
    |
    */

    'track_delivery' => env('COMTELE_TRACK_DELIVERY', true),

    /*
    |--------------------------------------------------------------------------
    | Phone Number Validation
    |--------------------------------------------------------------------------
    |
    | Validate Brazilian phone numbers before sending
    | Expected format: DDD + Number (e.g., 11987654321)
    |
    */

    'validate_phone' => env('COMTELE_VALIDATE_PHONE', true),

    /*
    |--------------------------------------------------------------------------
    | Bulk Sending Configuration
    |--------------------------------------------------------------------------
    |
    | Maximum recipients per API request (Comtele limit: 100)
    |
    */

    'bulk' => [
        'max_recipients' => env('COMTELE_BULK_MAX_RECIPIENTS', 100),
        'chunk_size' => env('COMTELE_BULK_CHUNK_SIZE', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Character Limits
    |--------------------------------------------------------------------------
    |
    | SMS character limits (Comtele uses standard GSM-7)
    | Single SMS: 160 characters
    | Multi-part: 153 characters per segment
    |
    */

    'character_limits' => [
        'single' => 160,
        'multi_part' => 153,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | API rate limits (based on Comtele documentation)
    | - 30 second cooldown between detailed reporting queries
    |
    */

    'rate_limit' => [
        'reporting_cooldown' => env('COMTELE_REPORTING_COOLDOWN', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Retry failed API requests
    |
    */

    'retry' => [
        'enabled' => env('COMTELE_RETRY_ENABLED', true),
        'max_attempts' => env('COMTELE_RETRY_MAX_ATTEMPTS', 3),
        'delay_seconds' => env('COMTELE_RETRY_DELAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client settings for API requests
    |
    */

    'http' => [
        'timeout' => env('COMTELE_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('COMTELE_HTTP_CONNECT_TIMEOUT', 10),
        'verify_ssl' => env('COMTELE_VERIFY_SSL', true),
    ],

];
