# 🤖 reCAPTCHA - Smart Context-Aware Validation

Google reCAPTCHA v3/Enterprise with smart trust scoring and context-aware validation for Laravel APIs.

## 📦 Installation

```bash
composer require eduardoks98/recaptcha
php artisan vendor:publish --provider="Eduardoks98\Recaptcha\RecaptchaServiceProvider"
php artisan migrate
```

## 🚀 Features

- ✅ **reCAPTCHA v3 & Enterprise** - Support for both standard and Enterprise APIs
- ✅ **Smart Trust Scoring** - Multi-factor analysis beyond Google's score
- ✅ **Context-Aware Decisions** - Auto-approve/reject based on historical data
- ✅ **IP Reputation** - Track IP success rates over time
- ✅ **User History** - Known users get higher trust scores
- ✅ **Time Pattern Analysis** - Business hours vs off-hours scoring
- ✅ **Geolocation Risk** - Country-based risk assessment
- ✅ **Bot Detection** - User-Agent analysis
- ✅ **Analytics & Logging** - Detailed validation logs

## 📖 Documentation

See the [complete documentation](../../docs/packages/recaptcha.md) for detailed examples.

## 🔧 Quick Start

### 1. Get reCAPTCHA Keys

Visit [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin) and create a v3 site.

### 2. Configure Environment

```env
RECAPTCHA_V3_SECRET=your_secret_key
RECAPTCHA_V3_SITE_KEY=your_site_key
RECAPTCHA_THRESHOLD=0.5
```

### 3. Apply Middleware

```php
// In routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['recaptcha:login,login']);

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware(['recaptcha:register,register']);
```

### 4. Frontend Integration

```html
<!-- Include reCAPTCHA script -->
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY"></script>

<script>
grecaptcha.ready(function() {
    grecaptcha.execute('YOUR_SITE_KEY', {action: 'login'}).then(function(token) {
        // Include token in your API request
        fetch('/api/v1/auth/login', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                username: 'user@example.com',
                password: 'password',
                recaptcha_token: token
            })
        });
    });
});
</script>
```

## 🎯 Smart Trust Scoring

### How It Works

The package calculates a **composite trust score** from multiple factors:

1. **reCAPTCHA Score** (30%) - Google's bot detection score
2. **IP Reputation** (25%) - Historical success rate from this IP
3. **User History** (20%) - Known user behavior
4. **Time Pattern** (10%) - Business hours vs off-hours
5. **Geolocation** (10%) - Country risk assessment
6. **User-Agent** (5%) - Bot signature detection

### Decision Logic

```
Score ≥ 0.8: Auto-approve (skip reCAPTCHA validation)
Score ≥ 0.5: Validate with reCAPTCHA
Score < 0.2: Auto-reject (suspicious)
```

### Example Trust Score Calculation

```php
use function Eduardoks98\Recaptcha\checkRecaptcha;

$result = checkRecaptcha($token, 'login', [
    'ip' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0...',
    'user_id' => 123,
    'login_context' => 'login',
]);

/*
Result:
[
    'success' => true,
    'score' => 0.85,                    // Combined score
    'trust_score' => 0.92,              // Smart trust score
    'decision' => 'high_trust',
    'reason' => 'Validation passed',
    'factors' => [
        'ip_reputation' => 0.95,        // 95% historical success
        'user_history' => 0.95,         // Known good user
        'time_pattern' => 0.9,          // Business hours
        'geolocation' => 0.8,           // Normal country
        'user_agent' => 0.9,            // Normal browser
    ],
    'log_id' => 1234
]
*/
```

## ⚙️ Configuration

```php
// config/recaptcha.php
return [
    'enabled' => true,

    // reCAPTCHA v3
    'v3_secret' => env('RECAPTCHA_V3_SECRET'),
    'v3_site_key' => env('RECAPTCHA_V3_SITE_KEY'),

    // Enterprise (optional)
    'enterprise_enabled' => false,
    'enterprise_api_key' => env('RECAPTCHA_ENTERPRISE_API_KEY'),
    'enterprise_project_id' => env('RECAPTCHA_PROJECT_ID'),

    // Score thresholds
    'threshold' => 0.5,
    'auto_approve_threshold' => 0.8,
    'auto_reject_threshold' => 0.2,

    // Trust score weights
    'trust_weights' => [
        'recaptcha_score' => 0.30,
        'ip_reputation' => 0.25,
        'user_history' => 0.20,
        'time_pattern' => 0.10,
        'geolocation' => 0.10,
        'user_agent' => 0.05,
    ],

    // Risk factors
    'high_risk_countries' => [],        // ['CN', 'RU']
    'bot_patterns' => ['/bot/i', '/crawler/i'],

    // Analytics
    'log_enabled' => true,
    'log_only_failures' => false,
];
```

## 📊 Helper Functions

```php
use function Eduardoks98\Recaptcha\{
    checkRecaptcha,
    updateRecaptchaLoginResult,
    getRecaptchaStats,
    getTopSuspiciousIps
};

// Smart validation
$result = checkRecaptcha($token, 'login', [
    'ip' => '1.2.3.4',
    'user_id' => 123,
    'login_context' => 'login',
]);

// Update login result
if ($result['success'] && isset($result['log_id'])) {
    updateRecaptchaLoginResult($result['log_id'], true);
}

// Analytics
$stats = getRecaptchaStats(7); // Last 7 days
$suspiciousIps = getTopSuspiciousIps(10);
```

## 🔒 Controller Example

```php
use function Eduardoks98\Recaptcha\checkRecaptcha;
use function Eduardoks98\Recaptcha\updateRecaptchaLoginResult;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        // Validate reCAPTCHA
        $recaptchaResult = checkRecaptcha(
            $request->input('recaptcha_token'),
            'login',
            [
                'ip' => $request->ip(),
                'login_context' => 'login',
            ]
        );

        if (!$recaptchaResult['success']) {
            return problemDetails(
                'https://api.example.com/errors/recaptcha-failed',
                'reCAPTCHA Validation Failed',
                403,
                $recaptchaResult['reason']
            );
        }

        // Attempt authentication
        if (Auth::attempt($request->only('username', 'password'))) {
            // Update reCAPTCHA log with success
            if (isset($recaptchaResult['log_id'])) {
                updateRecaptchaLoginResult($recaptchaResult['log_id'], true);
            }

            $user = Auth::user();
            $token = $user->createToken('api')->plainTextToken;

            return $this->success(['token' => $token]);
        }

        // Update reCAPTCHA log with failure
        if (isset($recaptchaResult['log_id'])) {
            updateRecaptchaLoginResult($recaptchaResult['log_id'], false, 'Invalid credentials');
        }

        return problemDetails(
            'https://api.example.com/errors/invalid-credentials',
            'Invalid Credentials',
            401,
            'Username or password is incorrect'
        );
    }
}
```

## 📈 Analytics Dashboard

```php
use function Eduardoks98\Recaptcha\{getRecaptchaStats, getTopSuspiciousIps};

// Get statistics
$stats = getRecaptchaStats(30); // Last 30 days

/*
[
    'total_validations' => 10000,
    'successful' => 9500,
    'failed' => 500,
    'suspicious' => 50,
    'success_rate' => 95.0,
    'login_attempts' => 5000,
    'successful_logins' => 4800,
    'failed_logins' => 200,
    'login_success_rate' => 96.0,
    'avg_score' => 0.85,
    'avg_trust_score' => 0.90,
]
*/

// Get suspicious IPs
$suspiciousIps = getTopSuspiciousIps(10, 7);
```

## 🗃️ Database Table

### recaptcha_logs
```sql
- ip (string) - Request IP address
- score (decimal) - Google reCAPTCHA score
- trust_score (decimal) - Smart trust score
- threshold (decimal) - Configured threshold
- success (boolean) - Validation result
- decision (string) - auto_approved, high_trust, etc.
- decision_reason (text) - Why this decision was made
- context (json) - Trust score factors
- user_agent (text) - Browser/client info
- user_id (bigint) - Associated user (if any)
- login_attempted (boolean) - Was this a login?
- login_successful (boolean) - Did login succeed?
- login_failure_reason (text) - Why login failed
- created_at, updated_at
```

## 🔗 Enterprise Edition

### Enable reCAPTCHA Enterprise

```env
RECAPTCHA_ENTERPRISE_ENABLED=true
RECAPTCHA_ENTERPRISE_API_KEY=your_api_key
RECAPTCHA_PROJECT_ID=your_project_id
```

The package automatically uses Enterprise API when enabled.

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
