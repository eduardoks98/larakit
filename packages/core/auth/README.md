# 🔐 Auth - Modern Sanctum Authentication

Modern Laravel Sanctum authentication with token abilities, device management, refresh tokens, and optional 2FA/LDAP.

## 📦 Installation

```bash
composer require eduardoks98/auth
php artisan vendor:publish --provider="Eduardoks98\Auth\AuthServiceProvider"
php artisan migrate
```

## 🚀 Features

- ✅ **Laravel Sanctum** - Modern token-based authentication
- ✅ **Access + Refresh Tokens** - Short-lived access tokens with refresh rotation
- ✅ **Token Abilities** - Granular permissions per token
- ✅ **Device Management** - Track and manage devices/sessions
- ✅ **Session Tracking** - Monitor user activity across devices
- ✅ **Token Expiration** - Automatic expiry and cleanup
- ✅ **2FA Support** - Google Authenticator integration (optional)
- ✅ **LDAP Integration** - Active Directory authentication (optional)

## 📖 Documentation

See the [complete documentation](../../docs/packages/auth.md) for detailed examples.

## 🔧 Quick Start

### 1. Configure Environment

```env
# Token expiration (in minutes)
SANCTUM_ACCESS_TOKEN_EXPIRATION=15      # 15 minutes
SANCTUM_REFRESH_TOKEN_EXPIRATION=10080  # 7 days

# Device tracking
SANCTUM_DEVICE_ID_ENABLED=true
SANCTUM_MAX_DEVICES=5

# Session tracking
SESSION_TRACKING_ENABLED=true
```

### 2. Add Routes

```php
// routes/api.php
use Eduardoks98\Auth\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/sessions', [AuthController::class, 'sessions']);
        Route::delete('/sessions/{deviceId}', [AuthController::class, 'revokeSession']);
    });
});
```

### 3. Login Flow

```bash
# 1. Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "user@example.com",
    "password": "password",
    "device_name": "iPhone 14 Pro"
  }'

# Response:
{
  "access_token": "1|abc123...",
  "refresh_token": "2|xyz789...",
  "token_type": "Bearer",
  "expires_in": 900,
  "refresh_expires_in": 604800
}

# 2. Make authenticated requests
curl http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer 1|abc123..."

# 3. Refresh when access token expires
curl -X POST http://localhost:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "2|xyz789..."
  }'

# 4. Logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer 1|abc123..."
```

## 🎯 Token Abilities (Permissions)

### Define Abilities

```php
// config/auth-package.php
'abilities' => [
    'users:read' => 'Read user information',
    'users:create' => 'Create new users',
    'users:edit' => 'Edit user information',
    'users:delete' => 'Delete users',
    'admin:*' => 'Full administrator access',
],
```

### Create Tokens with Abilities

```php
use function Eduardoks98\Auth\createAccessToken;

// Limited permissions token
$token = createAccessToken($user, 'Mobile App', $deviceId, [
    'users:read',
    'profile:edit',
]);

// Admin token
$token = createAccessToken($user, 'Admin Panel', $deviceId, ['admin:*']);
```

### Protect Routes

```php
// Require specific ability
Route::delete('/users/{id}', [UserController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'abilities:users:delete']);

// Require multiple abilities
Route::put('/users/{id}', [UserController::class, 'update'])
    ->middleware(['auth:sanctum', 'abilities:users:read,users:edit']);
```

### Check Abilities in Code

```php
use function Eduardoks98\Auth\hasTokenAbility;

if (hasTokenAbility('users:delete')) {
    // User can delete
}

// Or using request
if ($request->user()->tokenCan('users:delete')) {
    // User can delete
}
```

## 📱 Device Management

### Get User Devices

```php
use function Eduardoks98\Auth\getUserDevices;

$devices = getUserDevices($user);

/*
[
    [
        'device_id' => 'abc123...',
        'device_name' => 'iPhone 14 Pro',
        'last_used_at' => '2024-01-23 10:30:00',
        'created_at' => '2024-01-20 08:00:00',
    ],
    [
        'device_id' => 'def456...',
        'device_name' => 'Chrome on Windows',
        'last_used_at' => '2024-01-23 09:15:00',
        'created_at' => '2024-01-21 14:30:00',
    ],
]
*/
```

### Revoke Device Access

```php
use function Eduardoks98\Auth\revokeDeviceTokens;

// Revoke all tokens for a specific device
revokeDeviceTokens($user, $deviceId);
```

### Limit Devices Per User

```php
// config/auth-package.php
'sanctum' => [
    'max_devices_per_user' => 5,  // 0 = unlimited
],
```

## 📊 Session Tracking

### View Active Sessions

```bash
curl http://localhost:8000/api/v1/auth/sessions \
  -H "Authorization: Bearer 1|abc123..."
```

### Session Model Properties

```php
$session->ip;               // IP address
$session->user_agent;       // Browser/device info
$session->device_id;        // Unique device identifier
$session->last_activity;    // Last activity timestamp
$session->browser;          // Parsed browser name
$session->platform;         // Parsed platform (Windows, macOS, etc.)
$session->isActive();       // Check if active (< 30 min)
```

### Cleanup Old Sessions

```php
use function Eduardoks98\Auth\cleanupOldSessions;

// Run in scheduled task
$deletedCount = cleanupOldSessions();
```

## ⚙️ Controller Example

```php
use Eduardoks98\Auth\Http\Controllers\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{
    public function login(Request $request)
    {
        // Custom login logic before calling parent

        return parent::login($request);
    }
}
```

## 🔑 Helper Functions

```php
use function Eduardoks98\Auth\{
    createAccessToken,
    createRefreshToken,
    refreshAccessToken,
    revokeDeviceTokens,
    getUserDevices,
    getUserSessions,
    hasTokenAbility,
    cleanupExpiredTokens
};

// Token management
$accessToken = createAccessToken($user, 'Web App', $deviceId);
$refreshToken = createRefreshToken($user, 'Web App', $deviceId);
$newToken = refreshAccessToken($refreshTokenString);

// Device management
$devices = getUserDevices($user);
revokeDeviceTokens($user, $deviceId);

// Session management
$sessions = getUserSessions($user);
endUserSession($user, $deviceId);

// Abilities
if (hasTokenAbility('users:delete')) {
    // ...
}

// Maintenance
cleanupExpiredTokens();
cleanupOldSessions();
```

## 🗃️ Database Tables

### user_sessions
```sql
- id (bigint)
- user_id (bigint) - User who owns the session
- ip (string) - IP address
- user_agent (text) - Browser/device info
- device_id (string) - Unique device identifier
- last_activity (timestamp) - Last activity time
- created_at, updated_at
```

### personal_access_tokens (extended)
```sql
Added columns:
- type (string) - 'access' or 'refresh'
- device_id (string) - Associated device
- expires_at (timestamp) - Token expiration
```

## 🔒 Security Best Practices

### 1. Short-Lived Access Tokens
```php
'access_token_expiration' => 15,  // 15 minutes
```

### 2. Token Rotation
Refresh tokens are single-use and rotated on each refresh.

### 3. Device Limits
```php
'max_devices_per_user' => 5,
```

### 4. Session Monitoring
Track all active sessions and allow users to revoke suspicious devices.

### 5. Granular Permissions
Use token abilities instead of role-based access for API tokens.

## 📈 Maintenance Tasks

### Schedule in app/Console/Kernel.php

```php
protected function schedule(Schedule $schedule)
{
    // Clean up expired tokens daily
    $schedule->call(function () {
        cleanupExpiredTokens();
    })->daily();

    // Clean up old sessions weekly
    $schedule->call(function () {
        cleanupOldSessions();
    })->weekly();
}
```

## 🔗 Integration with Other Packages

Works seamlessly with:
- **eduardoks98/rate-limiter** - Rate limit login attempts
- **eduardoks98/recaptcha** - Validate logins with reCAPTCHA
- **eduardoks98/security** - IP blocking for suspicious logins

```php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware([
        'throttle.login:5,15',           // Max 5 attempts per 15 min
        'recaptcha:login,login',         // reCAPTCHA validation
    ]);
```

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
