<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MercadoPago Credentials
    |--------------------------------------------------------------------------
    |
    | Your MercadoPago Access Token (private key) for API authentication.
    | Get it from: https://www.mercadopago.com.br/developers/panel/credentials
    |
    */
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | MercadoPago Public Key
    |--------------------------------------------------------------------------
    |
    | Your MercadoPago Public Key for frontend integrations (tokenization).
    | Get it from: https://www.mercadopago.com.br/developers/panel/credentials
    |
    */
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'production' or 'sandbox' to determine which credentials to use.
    | Sandbox is for testing purposes only.
    |
    */
    'environment' => env('MERCADOPAGO_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret used to validate webhook signatures from MercadoPago.
    | This is optional but highly recommended for security.
    |
    */
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | PIX Configuration
    |--------------------------------------------------------------------------
    |
    | Default expiration time for PIX payments in ISO 8601 duration format.
    | Examples: PT30M (30 minutes), PT24H (24 hours), P30D (30 days)
    | Min: PT30M, Max: P30D, Default: PT24H
    |
    */
    'pix' => [
        'expiration_time' => env('MERCADOPAGO_PIX_EXPIRATION', 'PT24H'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Boleto Configuration
    |--------------------------------------------------------------------------
    |
    | Default expiration days for Boleto payments.
    | Typically 3-7 days. Default: 3 days
    |
    */
    'boleto' => [
        'expiration_days' => env('MERCADOPAGO_BOLETO_EXPIRATION_DAYS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Mode
    |--------------------------------------------------------------------------
    |
    | 'automatic' - MercadoPago automatically processes the payment
    | 'manual' - You need to manually approve/reject payments
    |
    */
    'processing_mode' => env('MERCADOPAGO_PROCESSING_MODE', 'automatic'),

    /*
    |--------------------------------------------------------------------------
    | Notification URL
    |--------------------------------------------------------------------------
    |
    | URL where MercadoPago will send webhook notifications.
    | Leave empty to use the default route: /api/mercadopago/webhook
    |
    */
    'notification_url' => env('MERCADOPAGO_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Statement Descriptor
    |--------------------------------------------------------------------------
    |
    | Text that will appear in the customer's credit card statement.
    | Max 13 characters for most acquirers.
    |
    */
    'statement_descriptor' => env('MERCADOPAGO_STATEMENT_DESCRIPTOR', 'YOUR_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable detailed logging of MercadoPago API requests and responses.
    | Useful for debugging but may impact performance.
    |
    */
    'logging' => [
        'enabled' => env('MERCADOPAGO_LOGGING_ENABLED', false),
        'channel' => env('MERCADOPAGO_LOGGING_CHANNEL', 'stack'),
    ],
];
