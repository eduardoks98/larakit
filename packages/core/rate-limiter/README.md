# ⏱️ Rate Limiter - Advanced 3-Tier Throttling

Advanced rate limiting with 3-tier throttling, geolocation, IP whitelisting, and fail2ban integration for Laravel APIs.

## 📦 Installation

```bash
composer require eduardoks98/rate-limiter
php artisan vendor:publish --provider="Eduardoks98\RateLimiter\RateLimiterServiceProvider"
php artisan migrate
```

## 🚀 Features

- ✅ **3-Tier Rate Limiting** - Route-level, IP+Route, and Global IP limits
- ✅ **IP Whitelisting** - Bypass rate limits for trusted IPs/ranges
- ✅ **Geolocation** - Country-based blocking and analytics
- ✅ **Injection Detection** - SQL/XSS/Path traversal detection
- ✅ **Volume Anomaly Detection** - Detect unusual traffic spikes
- ✅ **Request Logging** - Detailed analytics and audit trail
- ✅ **fail2ban Integration** - System-level IP blocking
- ✅ **OWASP Compliance** - API4:2023 (Unrestricted Resource Consumption)

## 📖 Documentation

See the [complete documentation](../../docs/packages/rate-limiter.md) for detailed examples.

## 🔧 Quick Start

### 1. Apply Generic Throttling

```php
// In routes/api.php
Route::middleware(['throttle.generic'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/posts', [PostController::class, 'index']);
});
```

### 2. Apply Login Throttling

```php
// In routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['throttle.login:5,15']); // 5 attempts per 15 minutes
```

### 3. Whitelist an IP

```php
use function Eduardoks98\RateLimiter\whitelistIp;

// Whitelist single IP
whitelistIp('192.168.1.100', 'Office IP');

// Whitelist CIDR range
whitelistCidr('192.168.1.0/24', 'Internal network');
```

## 🎯 3-Tier System

### Tier 1: Per-Route Global
- Limits total requests to a route from ALL IPs
- Default: 60 requests/minute per route
- Example: `/api/users` max 60 req/min total

### Tier 2: Per-IP + Route
- Limits requests from single IP to specific route
- Default: 30 requests/minute per IP per route
- Example: IP `1.2.3.4` → `/api/users` max 30 req/min

### Tier 3: Global IP Ban
- Ban IP globally after threshold across all routes
- Default: 100 requests/minute across all routes
- Ban duration: configurable (default 60 minutes)

## ⚙️ Configuration

```php
// config/rate-limiter.php
return [
    'enabled' => true,
    'decay_minutes' => 1,
    'max_attempts_before_ban' => 100,           // Tier 3
    'route_max_attempts' => 60,                 // Tier 1
    'ip_route_max_attempts' => 30,              // Tier 2
    'ban_duration_minutes' => 60,
    'whitelist_enabled' => true,
    'geolocation_enabled' => true,
    'injection_detection' => true,
    'volume_anomaly_detection' => true,
    'high_risk_countries' => [],                // ['CN', 'RU']
    'high_risk_action' => 'strict',             // 'block', 'strict', or 'log'
    'log_requests' => true,
    'fail2ban_enabled' => false,
];
```

## 📊 Helper Functions

```php
use function Eduardoks98\RateLimiter\{
    whitelistIp,
    whitelistCidr,
    removeWhitelistedIp,
    getApiRequestLogs,
    getTopRequestIps,
    clearRateLimitForIp,
    banIpOnSSH
};

// Whitelist management
whitelistIp('10.0.0.1', 'Load balancer');
whitelistCidr('10.0.0.0/8', 'Private network');

// Analytics
$logs = getApiRequestLogs(['ip' => '1.2.3.4', 'failed' => true]);
$topIps = getTopRequestIps(10);

// Manual controls
clearRateLimitForIp('1.2.3.4');
banIpOnSSH('1.2.3.4'); // Requires fail2ban
```

## 🔒 Security Features

### Injection Detection
Automatically detects and blocks:
- SQL injection attempts
- XSS attacks
- Path traversal attempts

### Volume Anomaly Detection
Triggers when traffic exceeds 3x normal:
```php
'anomaly_threshold_multiplier' => 3,
```

### Geolocation Blocking
Block or restrict by country:
```php
'high_risk_countries' => ['CN', 'RU', 'KP'],
'high_risk_action' => 'block', // or 'strict', 'log'
```

## 📈 Response Headers

Rate-limited requests include standard headers:
```
X-RateLimit-Limit: 30
X-RateLimit-Remaining: 15
X-RateLimit-Reset: 1704067200
Retry-After: 45
```

## 🗃️ Database Tables

### api_request_logs
Detailed request logs with payload, response, timing.

### api_request_stats
Aggregated daily statistics by IP and route.

### ip_whitelist
Whitelisted IPs and IP ranges.

## 🔗 Integration with fail2ban

### 1. Install fail2ban
```bash
sudo apt install fail2ban
```

### 2. Configure jail
```ini
# /etc/fail2ban/jail.local
[laravel-api]
enabled = true
port = http,https
logpath = /var/log/laravel.log
maxretry = 5
bantime = 3600
```

### 3. Enable in config
```php
'fail2ban_enabled' => true,
'fail2ban_jail' => 'laravel-api',
```

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
