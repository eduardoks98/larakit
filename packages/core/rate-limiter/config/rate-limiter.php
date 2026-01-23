<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiter Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the rate limiter globally.
    |
    */

    'enabled' => env('RATE_LIMITER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Decay Minutes
    |--------------------------------------------------------------------------
    |
    | The number of minutes that requests are counted for rate limiting.
    |
    */

    'decay_minutes' => env('RATE_LIMITER_TIME_DECAY_MINUTES', 1),

    /*
    |--------------------------------------------------------------------------
    | Max Attempts Before Ban
    |--------------------------------------------------------------------------
    |
    | Maximum number of requests from a single IP across ALL routes before
    | the IP is globally banned (Tier 3).
    |
    */

    'max_attempts_before_ban' => env('MAX_ATTEMPTS_BEFORE_BAN_IP', 100),

    /*
    |--------------------------------------------------------------------------
    | Ban Duration (minutes)
    |--------------------------------------------------------------------------
    |
    | How long an IP should remain banned after exceeding limits.
    |
    */

    'ban_duration_minutes' => env('RATE_LIMITER_BAN_DURATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Route Max Attempts (Tier 1)
    |--------------------------------------------------------------------------
    |
    | Maximum number of requests allowed per route globally (all IPs combined).
    | Default: 60 requests per minute per route.
    |
    */

    'route_max_attempts' => env('RATE_LIMITER_MAX_ATTEMPTS', 60),

    /*
    |--------------------------------------------------------------------------
    | IP + Route Max Attempts (Tier 2)
    |--------------------------------------------------------------------------
    |
    | Maximum number of requests allowed from a single IP to a specific route.
    | Default: 30 requests per minute per IP per route.
    |
    */

    'ip_route_max_attempts' => env('THROTTLE_MAX_ATTEMPTS', 30),

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist Enabled
    |--------------------------------------------------------------------------
    |
    | Enable IP whitelist functionality to bypass rate limiting for
    | trusted IPs or IP ranges.
    |
    */

    'whitelist_enabled' => env('RATE_LIMITER_WHITELIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Geolocation Enabled
    |--------------------------------------------------------------------------
    |
    | Enable geolocation tracking for request analytics and optional
    | country-based blocking.
    |
    */

    'geolocation_enabled' => env('RATE_LIMITER_GEOLOCATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Injection Detection
    |--------------------------------------------------------------------------
    |
    | Scan request payloads for SQL injection, XSS, and other attack patterns.
    | Automatically ban IPs that send malicious payloads.
    |
    */

    'injection_detection' => env('RATE_LIMITER_INJECTION_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | Volume Anomaly Detection
    |--------------------------------------------------------------------------
    |
    | Detect unusual traffic spikes (e.g., 3x normal traffic) and
    | automatically trigger additional security measures.
    |
    */

    'volume_anomaly_detection' => env('RATE_LIMITER_VOLUME_ANOMALY_DETECTION', true),

    /*
    |--------------------------------------------------------------------------
    | Anomaly Threshold Multiplier
    |--------------------------------------------------------------------------
    |
    | Traffic spike multiplier to trigger anomaly detection.
    | Example: 3 means trigger if traffic is 3x the average.
    |
    */

    'anomaly_threshold_multiplier' => env('RATE_LIMITER_ANOMALY_THRESHOLD', 3),

    /*
    |--------------------------------------------------------------------------
    | High Risk Countries
    |--------------------------------------------------------------------------
    |
    | List of country codes (ISO 3166-1 alpha-2) to apply stricter limits
    | or block entirely. Empty array = no country-based restrictions.
    |
    | Example: ['CN', 'RU', 'KP']
    |
    */

    'high_risk_countries' => explode(',', env('RATE_LIMITER_HIGH_RISK_COUNTRIES', '')),

    /*
    |--------------------------------------------------------------------------
    | High Risk Country Action
    |--------------------------------------------------------------------------
    |
    | Action to take for high-risk countries: 'block', 'strict', or 'log'.
    | - block: Immediately return 403
    | - strict: Apply 50% lower rate limits
    | - log: Log but allow (for analytics)
    |
    */

    'high_risk_action' => env('RATE_LIMITER_HIGH_RISK_ACTION', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    |
    | Log all API requests to database for analytics and security auditing.
    | Warning: This can generate significant database traffic.
    |
    */

    'log_requests' => env('RATE_LIMITER_LOG_REQUESTS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Only Blocked Requests
    |--------------------------------------------------------------------------
    |
    | If true, only log requests that were rate-limited or blocked.
    | If false, log all requests (use with caution in high-traffic apps).
    |
    */

    'log_only_blocked' => env('RATE_LIMITER_LOG_ONLY_BLOCKED', false),

    /*
    |--------------------------------------------------------------------------
    | Fail2Ban Integration
    |--------------------------------------------------------------------------
    |
    | Enable integration with fail2ban for system-level IP blocking.
    | Requires fail2ban to be installed and configured on the server.
    |
    */

    'fail2ban_enabled' => env('RATE_LIMITER_FAIL2BAN_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Fail2Ban Jail Name
    |--------------------------------------------------------------------------
    |
    | The fail2ban jail name to use when banning IPs at the system level.
    |
    */

    'fail2ban_jail' => env('RATE_LIMITER_FAIL2BAN_JAIL', 'laravel-api'),

];
