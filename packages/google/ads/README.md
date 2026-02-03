# Google AdMob SSV Package for Laravel

Server-Side Verification (SSV) for Google AdMob rewarded ads in Laravel applications.

## Installation

```bash
composer require eduardoks98/ads-google
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ads-google-config
```

## Environment Variables

```env
ADMOB_SSV_ENABLED=true
ADMOB_KEYS_CACHE_TTL=86400
ADMOB_TIME_DRIFT=300
ADMOB_CALLBACK_PATH=/api/ads/admob/callback
ADMOB_DEFAULT_REWARD_ITEM=coins
ADMOB_DEFAULT_REWARD_AMOUNT=10
ADMOB_SYNC_PROCESSING=false
ADMOB_LOGGING_ENABLED=true
```

## Usage

### Setting Up the Callback URL

In your AdMob dashboard, configure the SSV callback URL:

```
https://your-domain.com/api/ads/admob/callback
```

### Passing User ID to AdMob

In your mobile app, pass the user ID via the `custom_data` parameter when showing rewarded ads.

**Android (Kotlin):**
```kotlin
val rewardedAd = RewardedAd(this, "ca-app-pub-xxx/xxx")
val ssvOptions = ServerSideVerificationOptions.Builder()
    .setCustomData(userId.toString())
    .build()
rewardedAd.setServerSideVerificationOptions(ssvOptions)
```

**iOS (Swift):**
```swift
let options = GADServerSideVerificationOptions()
options.customRewardString = String(userId)
rewardedAd.serverSideVerificationOptions = options
```

### Callback Parameters

AdMob sends the following parameters:

| Parameter | Description |
|-----------|-------------|
| `ad_network` | The ad network that served the ad |
| `ad_unit` | Your ad unit ID |
| `custom_data` | The user ID you passed |
| `key_id` | ID of the key used to sign the callback |
| `reward_amount` | Amount configured in AdMob |
| `reward_item` | Item name configured in AdMob |
| `signature` | ECDSA signature for verification |
| `timestamp` | Unix timestamp of the callback |
| `transaction_id` | Unique transaction ID |

### How It Works

1. User watches a rewarded ad in your app
2. AdMob sends a signed callback to your server
3. The package verifies the ECDSA signature using Google's public keys
4. If valid, creates a reward and credits the user's virtual currency
5. Returns HTTP 200 to acknowledge receipt

### Custom Data Formats

**Plain format (default):**
```php
// config/ads-google.php
'custom_data' => [
    'format' => 'plain',
],
```
Pass just the user ID: `custom_data=123`

**JSON format:**
```php
// config/ads-google.php
'custom_data' => [
    'format' => 'json',
    'user_id_key' => 'user_id',
],
```
Pass JSON: `custom_data={"user_id":123,"session":"abc"}`

### Restricting Ad Units

You can restrict which ad units are accepted:

```php
// config/ads-google.php
'ad_units' => [
    'ca-app-pub-1234567890123456/1234567890',
    'ca-app-pub-1234567890123456/0987654321',
],
```

### Manual Signature Verification

```php
use Eduardoks98\AdsGoogle\Services\AdMobSsvService;

$ssvService = app(AdMobSsvService::class);

// Verify a request manually
$isValid = $ssvService->verifyCallback($request);

// Get public keys
$keys = $ssvService->getPublicKeys();

// Refresh keys cache
$ssvService->refreshKeys();
```

### Using the Middleware

You can use the middleware on your own routes:

```php
Route::middleware(['admob.verify'])->group(function () {
    Route::get('/my-custom-callback', [MyController::class, 'handle']);
});
```

## Security

- Callbacks are verified using ECDSA signatures
- Public keys are fetched from Google's servers and cached for 24 hours
- Timestamp drift is validated (default: 5 minutes)
- Duplicate transactions are detected and rejected

## Troubleshooting

### Signature Verification Fails

1. Ensure your server's clock is synchronized (NTP)
2. Check that the callback URL matches exactly what's configured in AdMob
3. Verify you're not modifying the query parameters before verification

### Keys Not Loading

```php
// Force refresh the public keys
app(AdMobSsvService::class)->refreshKeys();
```

### Testing Callbacks

For testing, you can temporarily disable SSV verification:

```env
ADMOB_SSV_ENABLED=false
```

**Warning:** Never disable verification in production!

## Integration with Monetization Package

This package integrates seamlessly with `eduardoks98/monetization`:

- Rewards are automatically created and processed
- Impressions are tracked in the `ad_impressions` table
- Virtual currency is credited to the user

## License

MIT License
