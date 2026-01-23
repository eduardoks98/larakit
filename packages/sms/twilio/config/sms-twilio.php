<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twilio Credentials
    |--------------------------------------------------------------------------
    |
    | Your Twilio Account SID and Auth Token from twilio.com/console
    |
    */

    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Phone Number
    |--------------------------------------------------------------------------
    |
    | Your Twilio phone number in E.164 format (e.g., +15551234567)
    | This is the sender number for outgoing SMS
    |
    */

    'from' => env('TWILIO_FROM_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Messaging Service SID (Optional)
    |--------------------------------------------------------------------------
    |
    | If you use Twilio Messaging Service instead of a single phone number,
    | set the Messaging Service SID here. This takes precedence over 'from'
    |
    */

    'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook URL for delivery status updates
    |
    */

    'webhook' => [
        'enabled' => env('TWILIO_WEBHOOK_ENABLED', true),
        'url' => env('TWILIO_WEBHOOK_URL', env('APP_URL').'/api/webhooks/twilio/status'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Tracking
    |--------------------------------------------------------------------------
    |
    | Enable or disable delivery status tracking in database
    |
    */

    'track_delivery' => env('TWILIO_TRACK_DELIVERY', true),

    /*
    |--------------------------------------------------------------------------
    | Phone Number Validation
    |--------------------------------------------------------------------------
    |
    | Enable phone number validation before sending
    |
    */

    'validate_phone' => env('TWILIO_VALIDATE_PHONE', true),

    /*
    |--------------------------------------------------------------------------
    | Trial Mode
    |--------------------------------------------------------------------------
    |
    | In trial mode, you can only send to verified phone numbers
    |
    */

    'trial_mode' => env('TWILIO_TRIAL_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Default Country Code
    |--------------------------------------------------------------------------
    |
    | Default country code for phone numbers without country prefix
    |
    */

    'default_country_code' => env('TWILIO_DEFAULT_COUNTRY_CODE', '55'), // Brazil

    /*
    |--------------------------------------------------------------------------
    | Character Limits
    |--------------------------------------------------------------------------
    |
    | SMS character limits for different encodings
    |
    */

    'character_limits' => [
        'gsm7' => 160,  // GSM-7 encoding
        'ucs2' => 70,   // UCS-2 (Unicode) encoding
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
        'enabled' => env('TWILIO_RETRY_ENABLED', true),
        'max_attempts' => env('TWILIO_RETRY_MAX_ATTEMPTS', 3),
        'delay_seconds' => env('TWILIO_RETRY_DELAY_SECONDS', 60),
    ],

];
