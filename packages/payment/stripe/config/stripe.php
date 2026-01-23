<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | Your Stripe API keys from https://dashboard.stripe.com/apikeys
    | Use test keys (sk_test_..., pk_test_...) for development
    | Use live keys (sk_live_..., pk_live_...) for production
    |
    */

    'secret_key' => env('STRIPE_SECRET_KEY', ''),
    'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook secret from https://dashboard.stripe.com/webhooks
    | Used to verify webhook signatures (whsec_...)
    |
    */

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
    'webhook_tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300), // seconds

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Stripe API version to use. Leave null to use account default.
    | Format: YYYY-MM-DD (e.g., '2023-10-16')
    |
    */

    'api_version' => env('STRIPE_API_VERSION', null),

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Default currency for payments (ISO 4217)
    | Stripe supports 135+ currencies: https://stripe.com/docs/currencies
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Payment Intents API
    |
    */

    'payment' => [
        // Automatic payment methods (card, ideal, sepa_debit, etc.)
        'automatic_payment_methods' => env('STRIPE_AUTOMATIC_PAYMENT_METHODS', true),

        // Capture method: automatic or manual
        'capture_method' => env('STRIPE_CAPTURE_METHOD', 'automatic'),

        // Confirmation method: automatic or manual
        'confirmation_method' => env('STRIPE_CONFIRMATION_METHOD', 'automatic'),

        // Payment method types to enable
        'payment_method_types' => ['card'],

        // Setup future usage: off_session, on_session
        'setup_future_usage' => env('STRIPE_SETUP_FUTURE_USAGE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe Subscriptions
    |
    */

    'subscription' => [
        // Default billing cycle anchor
        'billing_cycle_anchor' => null,

        // Collection method: charge_automatically or send_invoice
        'collection_method' => env('STRIPE_COLLECTION_METHOD', 'charge_automatically'),

        // Days until due (for invoices)
        'days_until_due' => env('STRIPE_DAYS_UNTIL_DUE', 30),

        // Proration behavior
        'proration_behavior' => env('STRIPE_PRORATION_BEHAVIOR', 'create_prorations'),

        // Trial period days
        'trial_period_days' => env('STRIPE_TRIAL_PERIOD_DAYS', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe Customers
    |
    */

    'customer' => [
        // Automatically create Stripe customer for users
        'auto_create' => env('STRIPE_AUTO_CREATE_CUSTOMER', true),

        // Customer metadata to include
        'metadata' => [
            'app_name' => env('APP_NAME', 'Laravel'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Events
    |--------------------------------------------------------------------------
    |
    | List of Stripe webhook events to handle
    | See: https://stripe.com/docs/api/events/types
    |
    */

    'webhook_events' => [
        // Payment Intent events
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
        'payment_intent.canceled',
        'payment_intent.created',
        'payment_intent.processing',

        // Subscription events
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'customer.subscription.trial_will_end',

        // Invoice events
        'invoice.created',
        'invoice.finalized',
        'invoice.paid',
        'invoice.payment_failed',

        // Customer events
        'customer.created',
        'customer.updated',
        'customer.deleted',

        // Payment Method events
        'payment_method.attached',
        'payment_method.detached',

        // Charge events
        'charge.succeeded',
        'charge.failed',
        'charge.refunded',
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
        'enabled' => env('STRIPE_LOGGING_ENABLED', true),
        'channel' => env('STRIPE_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Table names for storing Stripe data
    |
    */

    'tables' => [
        'payments' => 'stripe_payments',
        'customers' => 'stripe_customers',
        'subscriptions' => 'stripe_subscriptions',
    ],

];
