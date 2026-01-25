# Unity Ads S2S Package for Laravel

Server-to-Server (S2S) callback validation for Unity Ads rewarded videos in Laravel applications.

## Installation

```bash
composer require eduardoks98/ads-unity
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ads-unity-config
```

## Environment Variables

```env
UNITY_ADS_S2S_ENABLED=true
UNITY_ADS_SECRET_KEY=your_secret_key_from_unity
UNITY_ADS_GAME_ID=1234567
UNITY_ADS_CALLBACK_PATH=/api/ads/unity/callback
UNITY_ADS_DEFAULT_REWARD_ITEM=coins
UNITY_ADS_DEFAULT_REWARD_AMOUNT=10
UNITY_ADS_SYNC_PROCESSING=false

# Optional: Stats API
UNITY_ADS_STATS_API_KEY=your_api_key
UNITY_ADS_ORGANIZATION_ID=your_org_id
```

## Getting the Secret Key

To enable S2S callbacks, you need to contact Unity Support:

1. Go to Unity Dashboard
2. Open a support ticket
3. Provide your Game ID(s) and callback URL
4. Unity will provide you with a secret key

## Usage

### Setting Up the Callback URL

In your Unity Dashboard or via support, configure the S2S callback URL:

```
https://your-domain.com/api/ads/unity/callback
```

### Passing User ID in Your Game

**Unity C#:**
```csharp
using UnityEngine.Monetization;

// Set the user ID for S2S callbacks
Monetization.SetGamerSid(userId.ToString());

// Or use ServerCallbackOptions
var options = new ShowAdCallbackOptions();
options.serverCallbackId = userId.ToString();
```

### Callback Parameters

Unity sends the following parameters:

| Parameter | Description |
|-----------|-------------|
| `sid` | Server ID (your user identifier) |
| `oid` | Order ID (unique transaction ID) |
| `hmac` | HMAC-MD5 signature |
| `product` | Product/placement ID (optional) |
| `zone` | Zone ID (optional) |
| `gamer_sid` | Alternative user identifier |

### How HMAC Verification Works

1. Unity sends a callback with all parameters including `hmac`
2. The package removes `hmac` from the params
3. Remaining params are sorted alphabetically by key
4. A comma-separated `key=value` string is created
5. HMAC-MD5 is computed using your secret key
6. The computed hash is compared with the received `hmac`

**Example:**
```
Parameters: sid=123, oid=abc456, product=rewarded
Sorted: oid=abc456,product=rewarded,sid=123
HMAC: hash_hmac('md5', 'oid=abc456,product=rewarded,sid=123', $secretKey)
```

### Manual Signature Verification

```php
use Eduardoks98\AdsUnity\Services\UnityCallbackService;

$callbackService = app(UnityCallbackService::class);

// Verify a request
$isValid = $callbackService->verifyCallback($request);

// Generate a signature for testing
$signature = $callbackService->generateSignature([
    'sid' => '123',
    'oid' => 'test_order_123',
]);
```

### Using the Stats API

```php
use Eduardoks98\AdsUnity\Services\UnityStatsService;
use Carbon\Carbon;

$statsService = app(UnityStatsService::class);

// Check if configured
if ($statsService->isConfigured()) {
    // Get general stats
    $stats = $statsService->getStats(
        Carbon::now()->subDays(7),
        Carbon::now()
    );

    // Get daily revenue
    $revenue = $statsService->getDailyRevenue(
        Carbon::now()->subDays(30),
        Carbon::now()
    );

    // Get stats by country
    $byCountry = $statsService->getStatsByCountry(
        Carbon::now()->subDays(7),
        Carbon::now()
    );
}
```

## Response Format

Unity expects:
- **Success:** HTTP 200 with `1` in the response body
- **Failure:** HTTP 400-500 range with error message

The package automatically returns the correct format.

## Security

- Callbacks are verified using HMAC-MD5
- The secret key is provided by Unity and should be kept secure
- Duplicate transactions are automatically detected and rejected

## Troubleshooting

### Signature Verification Fails

1. Ensure the secret key is correctly configured
2. Check that parameters are not being modified by middleware
3. Verify the callback URL matches exactly what Unity has configured

### Testing Callbacks

You can generate test signatures:

```php
$service = app(UnityCallbackService::class);

$params = [
    'sid' => '123',
    'oid' => 'test_' . time(),
];

$signature = $service->generateSignature($params);

// Use this URL for testing:
// /api/ads/unity/callback?sid=123&oid=test_xxx&hmac={signature}
```

## Integration with Monetization Package

This package integrates seamlessly with `eduardoks98/monetization`:

- Rewards are automatically created and processed
- Impressions are tracked in the `ad_impressions` table
- Virtual currency is credited to the user

## License

MIT License
