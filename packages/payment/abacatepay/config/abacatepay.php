<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AbacatePay API Token
    |--------------------------------------------------------------------------
    |
    | Your AbacatePay API token for authentication.
    | Get it from: https://dashboard.abacatepay.com/settings/api
    |
    */
    'token' => env('ABACATEPAY_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret key used to validate webhook requests from AbacatePay.
    |
    */
    'webhook_secret' => env('ABACATEPAY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Method
    |--------------------------------------------------------------------------
    |
    | Default payment method to use when not specified.
    | Available: pix, card
    |
    */
    'default_method' => env('ABACATEPAY_DEFAULT_METHOD', 'pix'),

    /*
    |--------------------------------------------------------------------------
    | Default Frequency
    |--------------------------------------------------------------------------
    |
    | Default billing frequency.
    | Available: one_time, monthly, yearly
    |
    */
    'default_frequency' => env('ABACATEPAY_DEFAULT_FREQUENCY', 'one_time'),

    /*
    |--------------------------------------------------------------------------
    | Return URL
    |--------------------------------------------------------------------------
    |
    | URL to redirect users after payment completion.
    |
    */
    'return_url' => env('ABACATEPAY_RETURN_URL'),

    /*
    |--------------------------------------------------------------------------
    | Completion URL
    |--------------------------------------------------------------------------
    |
    | URL to redirect users after successful payment.
    |
    */
    'completion_url' => env('ABACATEPAY_COMPLETION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency for transactions (BRL for Brazil).
    |
    */
    'currency' => env('ABACATEPAY_CURRENCY', 'BRL'),

    /*
    |--------------------------------------------------------------------------
    | Database Storage
    |--------------------------------------------------------------------------
    |
    | Enable/disable storing billing records in database.
    |
    */
    'store_billings' => env('ABACATEPAY_STORE_BILLINGS', true),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | AbacatePay API base URL (usually not needed to change).
    |
    */
    'api_base_url' => env('ABACATEPAY_API_BASE_URL', 'https://api.abacatepay.com/v1'),
];
