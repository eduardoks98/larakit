<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel Sanctum token-based authentication.
    |
    */

    'sanctum' => [
        // Token expiration times (in minutes)
        'access_token_expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 15),  // 15 minutes
        'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 10080),  // 7 days

        // Enable device tracking
        'device_id_enabled' => env('SANCTUM_DEVICE_ID_ENABLED', true),

        // Token prefix for identification
        'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

        // Maximum devices per user (0 = unlimited)
        'max_devices_per_user' => env('SANCTUM_MAX_DEVICES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Abilities Configuration
    |--------------------------------------------------------------------------
    |
    | Define available token abilities (permissions) for granular access control.
    |
    */

    'abilities' => [
        // User management
        'users:read' => 'Read user information',
        'users:create' => 'Create new users',
        'users:edit' => 'Edit user information',
        'users:delete' => 'Delete users',

        // Profile management
        'profile:read' => 'Read own profile',
        'profile:edit' => 'Edit own profile',

        // Admin abilities
        'admin:*' => 'Full administrator access',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Tracking
    |--------------------------------------------------------------------------
    |
    | Track user sessions across multiple devices for security monitoring.
    |
    */

    'session_tracking' => [
        'enabled' => env('SESSION_TRACKING_ENABLED', true),
        'log_user_agent' => true,
        'log_ip_address' => true,
        'cleanup_old_sessions' => true,
        'cleanup_after_days' => env('SESSION_CLEANUP_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration (Optional)
    |--------------------------------------------------------------------------
    |
    | If you need JWT tokens in addition to Sanctum, configure here.
    |
    */

    'jwt' => [
        'enabled' => env('JWT_ENABLED', false),
        'algorithm' => env('JWT_ALGORITHM', 'RS256'),
        'ttl' => env('JWT_TTL', 60),  // 60 minutes
        'private_key' => env('JWT_PRIVATE_KEY'),
        'public_key' => env('JWT_PUBLIC_KEY'),
        'issuer' => env('JWT_ISSUER', env('APP_URL')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication (2FA)
    |--------------------------------------------------------------------------
    |
    | Enable Google Authenticator-based two-factor authentication.
    |
    */

    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'issuer' => env('TWO_FACTOR_ISSUER', env('APP_NAME', 'API Base')),
        'digits' => 6,
        'period' => 30,
        'algorithm' => 'sha1',
        'qr_code_size' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication (Optional)
    |--------------------------------------------------------------------------
    |
    | Configure LDAP/Active Directory integration for enterprise environments.
    |
    */

    'ldap' => [
        'enabled' => env('LDAP_ENABLED', false),
        'host' => env('LDAP_HOST'),
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN'),
        'username' => env('LDAP_USERNAME'),
        'password' => env('LDAP_PASSWORD'),
        'use_ssl' => env('LDAP_USE_SSL', false),
        'use_tls' => env('LDAP_USE_TLS', false),
        'timeout' => env('LDAP_TIMEOUT', 5),

        // User synchronization
        'sync_users' => env('LDAP_SYNC_USERS', true),
        'user_filter' => env('LDAP_USER_FILTER', '(&(objectClass=user)(sAMAccountName={username}))'),
        'username_attribute' => env('LDAP_USERNAME_ATTRIBUTE', 'sAMAccountName'),
        'email_attribute' => env('LDAP_EMAIL_ATTRIBUTE', 'mail'),
        'name_attribute' => env('LDAP_NAME_ATTRIBUTE', 'displayName'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Enforce password complexity requirements.
    |
    */

    'password_policy' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL', false),
        'expires_after_days' => env('PASSWORD_EXPIRES_DAYS', 0),  // 0 = never
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Security
    |--------------------------------------------------------------------------
    |
    | Additional security measures for login attempts.
    |
    */

    'login_security' => [
        'log_attempts' => env('LOGIN_LOG_ATTEMPTS', true),
        'notify_on_new_device' => env('LOGIN_NOTIFY_NEW_DEVICE', false),
        'require_email_verification' => env('LOGIN_REQUIRE_EMAIL_VERIFICATION', false),
    ],

];
