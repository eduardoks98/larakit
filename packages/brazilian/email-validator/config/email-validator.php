<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Checks
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific validation checks.
    |
    */
    'checks' => [
        'syntax' => env('EMAIL_CHECK_SYNTAX', true),
        'dns' => env('EMAIL_CHECK_DNS', true),
        'mx' => env('EMAIL_CHECK_MX', true),
        'disposable' => env('EMAIL_CHECK_DISPOSABLE', true),
        'role' => env('EMAIL_CHECK_ROLE', true),
        'smtp' => env('EMAIL_CHECK_SMTP', false), // Slow, use with caution
    ],

    /*
    |--------------------------------------------------------------------------
    | Disposable Email Domains
    |--------------------------------------------------------------------------
    |
    | List of known disposable email domains. This list is checked locally
    | before making any API calls.
    |
    */
    'disposable_domains' => [
        // Common disposable domains
        '10minutemail.com',
        'guerrillamail.com',
        'guerrillamail.org',
        'guerrillamail.net',
        'mailinator.com',
        'maildrop.cc',
        'tempmail.com',
        'temp-mail.org',
        'throwaway.email',
        'throwawaymail.com',
        'yopmail.com',
        'yopmail.fr',
        'fakeinbox.com',
        'tempinbox.com',
        'trashmail.com',
        'trashmail.net',
        'discard.email',
        'sharklasers.com',
        'grr.la',
        'guerrillamail.info',
        'pokemail.net',
        'spam4.me',
        'mytemp.email',
        'mohmal.com',
        'tempail.com',
        'emailondeck.com',
        'getnada.com',
        'mintemail.com',
        'burnermail.io',
        'mailnesia.com',
        'mailsac.com',
        'mailcatch.com',
        'spamgourmet.com',
        'jetable.org',
        'tmpmail.org',
        'tmpmail.net',
        'tempr.email',
        'dispostable.com',
        'fakemailgenerator.com',
        'emailfake.com',
        'crazymailing.com',
        'tempmailo.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Email Prefixes
    |--------------------------------------------------------------------------
    |
    | Email addresses starting with these prefixes are typically role-based
    | (not personal) and might have deliverability issues.
    |
    */
    'role_prefixes' => [
        'admin',
        'administrator',
        'info',
        'contact',
        'support',
        'sales',
        'marketing',
        'help',
        'helpdesk',
        'noreply',
        'no-reply',
        'webmaster',
        'postmaster',
        'hostmaster',
        'abuse',
        'security',
        'billing',
        'accounts',
        'hr',
        'jobs',
        'careers',
        'press',
        'media',
        'newsletter',
        'subscribe',
        'unsubscribe',
        'feedback',
        'team',
        'staff',
        'office',
        'reception',
        'hello',
        'hi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Domains
    |--------------------------------------------------------------------------
    |
    | Domains that are always considered valid (skip some checks).
    |
    */
    'trusted_domains' => [
        'gmail.com',
        'googlemail.com',
        'outlook.com',
        'hotmail.com',
        'live.com',
        'yahoo.com',
        'yahoo.com.br',
        'icloud.com',
        'me.com',
        'mac.com',
        'protonmail.com',
        'proton.me',
        'aol.com',
        'uol.com.br',
        'bol.com.br',
        'terra.com.br',
        'ig.com.br',
        'globo.com',
        'globomail.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Providers (Optional)
    |--------------------------------------------------------------------------
    |
    | External API providers for enhanced validation.
    |
    */
    'providers' => [
        'hunter' => [
            'enabled' => env('HUNTER_ENABLED', false),
            'api_key' => env('HUNTER_API_KEY'),
            'base_url' => 'https://api.hunter.io/v2',
        ],
        'zerobounce' => [
            'enabled' => env('ZEROBOUNCE_ENABLED', false),
            'api_key' => env('ZEROBOUNCE_API_KEY'),
            'base_url' => 'https://api.zerobounce.net/v2',
        ],
        'abstract' => [
            'enabled' => env('ABSTRACT_ENABLED', false),
            'api_key' => env('ABSTRACT_API_KEY'),
            'base_url' => 'https://emailvalidation.abstractapi.com/v1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache validation results to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('EMAIL_VALIDATOR_CACHE_ENABLED', true),
        'ttl' => env('EMAIL_VALIDATOR_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('EMAIL_VALIDATOR_CACHE_PREFIX', 'email_validator'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMTP Verification Settings
    |--------------------------------------------------------------------------
    |
    | Settings for SMTP verification (when enabled).
    |
    */
    'smtp' => [
        'timeout' => env('EMAIL_SMTP_TIMEOUT', 10),
        'from_email' => env('EMAIL_SMTP_FROM', 'verify@example.com'),
        'from_name' => env('EMAIL_SMTP_FROM_NAME', 'Email Verifier'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring
    |--------------------------------------------------------------------------
    |
    | Quality score configuration.
    |
    */
    'scoring' => [
        'weights' => [
            'syntax' => 20,
            'dns' => 20,
            'mx' => 25,
            'not_disposable' => 20,
            'not_role' => 10,
            'trusted_domain' => 5,
        ],
        'thresholds' => [
            'excellent' => 90,
            'good' => 70,
            'acceptable' => 50,
            'poor' => 30,
        ],
    ],
];
