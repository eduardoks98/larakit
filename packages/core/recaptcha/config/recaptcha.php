<?php

return [

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable reCAPTCHA validation globally.
    |
    */

    'enabled' => env('RECAPTCHA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3 Credentials
    |--------------------------------------------------------------------------
    |
    | Your Google reCAPTCHA v3 site key and secret key.
    | Get your keys at: https://www.google.com/recaptcha/admin
    |
    */

    'v3_secret' => env('RECAPTCHA_V3_SECRET'),
    'v3_site_key' => env('RECAPTCHA_V3_SITE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Enterprise Credentials (Optional)
    |--------------------------------------------------------------------------
    |
    | If you're using reCAPTCHA Enterprise, provide your API key and project ID.
    | Leave empty to use standard reCAPTCHA v3.
    |
    */

    'enterprise_enabled' => env('RECAPTCHA_ENTERPRISE_ENABLED', false),
    'enterprise_api_key' => env('RECAPTCHA_ENTERPRISE_API_KEY'),
    'enterprise_project_id' => env('RECAPTCHA_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Verification URL
    |--------------------------------------------------------------------------
    |
    | The Google API endpoint for verifying reCAPTCHA tokens.
    |
    */

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    'enterprise_verify_url' => 'https://recaptchaenterprise.googleapis.com/v1/projects/{projectId}/assessments',

    /*
    |--------------------------------------------------------------------------
    | Score Thresholds
    |--------------------------------------------------------------------------
    |
    | reCAPTCHA v3 returns a score from 0.0 (bot) to 1.0 (human).
    | Define thresholds for different trust levels.
    |
    */

    'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),
    'high_trust_threshold' => env('RECAPTCHA_HIGH_TRUST', 0.7),
    'medium_trust_threshold' => env('RECAPTCHA_MEDIUM_TRUST', 0.5),
    'low_trust_threshold' => env('RECAPTCHA_LOW_TRUST', 0.3),
    'suspicious_threshold' => env('RECAPTCHA_SUSPICIOUS', 0.1),

    /*
    |--------------------------------------------------------------------------
    | Trust Score Weights
    |--------------------------------------------------------------------------
    |
    | Weights for different factors in the smart trust score calculation.
    | Total should equal 1.0 (100%).
    |
    */

    'trust_weights' => [
        'recaptcha_score' => 0.30,      // Google's reCAPTCHA score
        'ip_reputation' => 0.25,        // IP history (success rate)
        'user_history' => 0.20,         // Known user behavior
        'time_pattern' => 0.10,         // Request timing (business hours)
        'geolocation' => 0.10,          // Country risk
        'user_agent' => 0.05,           // Bot signature detection
    ],

    /*
    |--------------------------------------------------------------------------
    | High Risk Countries
    |--------------------------------------------------------------------------
    |
    | List of country codes (ISO 3166-1 alpha-2) considered high-risk.
    | Lower trust scores for requests from these countries.
    |
    */

    'high_risk_countries' => explode(',', env('RECAPTCHA_HIGH_RISK_COUNTRIES', '')),

    /*
    |--------------------------------------------------------------------------
    | Bot User-Agent Patterns
    |--------------------------------------------------------------------------
    |
    | Regular expressions to detect known bot signatures in User-Agent strings.
    |
    */

    'bot_patterns' => [
        '/bot/i',
        '/crawler/i',
        '/spider/i',
        '/scraper/i',
        '/curl/i',
        '/wget/i',
        '/python-requests/i',
        '/headless/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Hours
    |--------------------------------------------------------------------------
    |
    | Define business hours for time pattern analysis.
    | Higher trust during business hours, lower during off-hours.
    |
    */

    'business_hours' => [
        'start' => env('RECAPTCHA_BUSINESS_HOUR_START', 8),  // 8 AM
        'end' => env('RECAPTCHA_BUSINESS_HOUR_END', 18),     // 6 PM
        'timezone' => env('RECAPTCHA_TIMEZONE', 'America/Sao_Paulo'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP History Tracking
    |--------------------------------------------------------------------------
    |
    | Track IP reputation based on historical success/failure rate.
    |
    */

    'ip_history_enabled' => env('RECAPTCHA_IP_HISTORY_ENABLED', true),
    'ip_history_days' => env('RECAPTCHA_IP_HISTORY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Auto-Approve/Reject Thresholds
    |--------------------------------------------------------------------------
    |
    | Automatically approve or reject based on trust score without checking reCAPTCHA.
    |
    */

    'auto_approve_threshold' => env('RECAPTCHA_AUTO_APPROVE', 0.8),
    'auto_reject_threshold' => env('RECAPTCHA_AUTO_REJECT', 0.2),

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    |
    | Log all reCAPTCHA validations for analytics and debugging.
    |
    */

    'log_enabled' => env('RECAPTCHA_LOG_ENABLED', true),
    'log_only_failures' => env('RECAPTCHA_LOG_ONLY_FAILURES', false),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Disable SSL verification for local development.
    | NEVER disable in production!
    |
    */

    'verify_ssl' => env('RECAPTCHA_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout for reCAPTCHA API calls (in seconds).
    |
    */

    'timeout' => env('RECAPTCHA_TIMEOUT', 10),

];
