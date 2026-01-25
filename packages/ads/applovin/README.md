# AppLovin MAX S2S Package for Laravel

Server-to-Server (S2S) callback validation and revenue reporting for AppLovin MAX in Laravel applications.

## Installation

```bash
composer require eduardoks98/ads-applovin
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ads-applovin-config
```

## Environment Variables

```env
APPLOVIN_S2S_ENABLED=true
APPLOVIN_EVENT_KEY=your_event_key
APPLOVIN_CALLBACK_PATH=/api/ads/applovin/callback

APPLOVIN_REPORT_KEY=your_report_key
APPLOVIN_SDK_KEY=your_sdk_key
APPLOVIN_PACKAGE_NAME=com.example.app
APPLOVIN_PLATFORM=android

APPLOVIN_DEFAULT_REWARD_ITEM=coins
APPLOVIN_DEFAULT_REWARD_AMOUNT=10
APPLOVIN_SYNC_PROCESSING=false
```

## Getting the Event Key

1. Go to AppLovin MAX Dashboard
2. Navigate to your app settings
3. Find the "Keys" section under Account > General
4. Copy the Event Key for S2S callback verification

## Usage

### Setting Up the Callback URL

In your AppLovin MAX Dashboard:

1. Go to Ad Units
2. Edit your rewarded ad unit
3. Set the "Server Side Callback URL" to:
   ```
   https://your-domain.com/api/ads/applovin/callback
   ```

### Passing User ID in Your App

**Android (Kotlin):**
```kotlin
MaxRewardedAd.setExtraParameter("user_id", userId.toString())
// Or use custom data
MaxRewardedAd.setLocalExtraParameter("custom_data", userId.toString())
```

**iOS (Swift):**
```swift
rewardedAd.setExtraParameterForKey("user_id", value: String(userId))
// Or use custom data
rewardedAd.setLocalExtraParameterForKey("custom_data", value: String(userId))
```

### Callback Parameters

AppLovin sends the following parameters:

| Parameter | Description |
|-----------|-------------|
| `event_id` | Unique transaction ID |
| `event_token` | Token for verification |
| `user_id` | User identifier |
| `amount` | Reward amount |
| `currency` | Reward currency/type |
| `ad_unit_id` | Ad unit ID |
| `placement` | Placement name |
| `network` | Ad network that served the ad |
| `country` | User's country code |
| `idfa` | iOS advertising ID |
| `gaid` | Google advertising ID |
| `idfv` | iOS vendor ID |
| `android_id` | Android device ID |
| `custom_data` | Custom data from the app |

### How Verification Works

1. AppLovin sends a callback with `event_token`
2. The package compares `event_token` with your configured `event_key`
3. If they match, the callback is valid
4. A reward is created and processed

### Using the Callback Service

```php
use Eduardoks98\AdsApplovin\Services\MaxCallbackService;

$callbackService = app(MaxCallbackService::class);

// Verify a request
$isValid = $callbackService->verifyCallback($request);

// Extract user ID
$userId = $callbackService->extractUserId($request);

// Extract reward amount
$amount = $callbackService->extractRewardAmount($request);
```

### Revenue Reporting API

```php
use Eduardoks98\AdsApplovin\Services\MaxReportingService;
use Carbon\Carbon;

$reportingService = app(MaxReportingService::class);

// Check if configured
if ($reportingService->isConfigured()) {
    // Get user-level revenue report for a specific date
    $report = $reportingService->getUserAdRevenueReport(
        Carbon::yesterday()
    );

    // Get revenue for a date range
    $reports = $reportingService->getRevenueReport(
        Carbon::now()->subDays(7),
        Carbon::now()
    );

    // Calculate total revenue
    $total = $reportingService->calculateTotalRevenue($report);

    // Get unique users
    $users = $reportingService->getUniqueUsers($report);

    // Group by user
    $byUser = $reportingService->groupByUser($report);
}
```

## Important Notes

- **Callbacks are only sent for live ads**, not test ads
- Test your integration in production or with a production build
- The callback URL must be accessible without authentication
- Keep your `event_key` secure

## Mediation Support

AppLovin MAX supports mediation with multiple ad networks:

- AdMob
- Facebook Audience Network
- Unity Ads
- Vungle
- ironSource
- And more...

The `network` parameter in callbacks indicates which network served the ad.

## Security

- Callbacks are verified using the event token
- Duplicate transactions are automatically detected and rejected
- All callbacks are logged for auditing

## Troubleshooting

### Callbacks Not Being Received

1. Ensure the callback URL is publicly accessible
2. Check that HTTPS is properly configured
3. Verify the ad unit has S2S callbacks enabled
4. Test with a production build (not test ads)

### Invalid Event Token

1. Verify the event key in your `.env` matches the dashboard
2. Check for extra whitespace in the environment variable
3. Regenerate the key in the dashboard if needed

### Testing Locally

Use a service like ngrok to expose your local server:

```bash
ngrok http 8000
```

Then update the callback URL in AppLovin dashboard.

## Integration with Monetization Package

This package integrates seamlessly with `eduardoks98/monetization`:

- Rewards are automatically created and processed
- Impressions are tracked in the `ad_impressions` table
- Virtual currency is credited to the user
- Ad network attribution is tracked

## License

MIT License
