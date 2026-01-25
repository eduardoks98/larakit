# Facebook Audience Network Package for Laravel

Facebook Audience Network (FAN) integration for Laravel applications with revenue reporting and client-side reward handling.

## Installation

```bash
composer require eduardoks98/ads-facebook
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ads-facebook-config
```

## Environment Variables

```env
FACEBOOK_ADS_APP_ID=your_app_id
FACEBOOK_ADS_APP_SECRET=your_app_secret
FACEBOOK_ADS_ACCESS_TOKEN=your_access_token
FACEBOOK_ADS_API_VERSION=v21.0

FACEBOOK_ADS_REWARDS_ENABLED=true
FACEBOOK_ADS_CALLBACK_PATH=/api/ads/facebook/reward
FACEBOOK_ADS_DEFAULT_REWARD_ITEM=coins
FACEBOOK_ADS_DEFAULT_REWARD_AMOUNT=10
FACEBOOK_ADS_REQUIRE_AUTH=true
```

## Important Note About FAN

Unlike AdMob, Unity, or AppLovin, **Facebook Audience Network does NOT support server-to-server (S2S) callbacks** for rewarded ads. This package provides:

1. **Revenue Reporting** - via Graph API
2. **Client-Side Reward Endpoint** - your app reports completed ads

## Usage

### Reporting Completed Ads from Your App

Since FAN doesn't have S2S callbacks, your mobile app must report completed rewarded ads.

**Android (Kotlin):**
```kotlin
// After user completes watching the ad
val transactionId = UUID.randomUUID().toString()

val retrofit = Retrofit.Builder()
    .baseUrl("https://your-domain.com/")
    .build()

val api = retrofit.create(YourApi::class.java)

api.reportReward(
    RewardRequest(
        placementId = "your_placement_id",
        transactionId = transactionId,
        rewardAmount = 10,
        rewardItem = "coins"
    ),
    authToken = "Bearer $userToken"
)
```

**iOS (Swift):**
```swift
// After user completes watching the ad
let transactionId = UUID().uuidString

let request = RewardRequest(
    placementId: "your_placement_id",
    transactionId: transactionId,
    rewardAmount: 10,
    rewardItem: "coins"
)

APIClient.shared.reportReward(request, token: userToken)
```

### API Endpoints

#### Report Reward (POST)
```
POST /api/ads/facebook/reward
Authorization: Bearer {token}
Content-Type: application/json

{
    "placement_id": "your_placement_id",
    "transaction_id": "unique_transaction_id",
    "reward_item": "coins",        // optional
    "reward_amount": 10,           // optional
    "metadata": {                  // optional
        "level": 5,
        "session_id": "abc123"
    }
}
```

**Response:**
```json
{
    "success": true,
    "reward_id": 123,
    "amount": 10,
    "item": "coins"
}
```

#### Generate Transaction ID (GET)
```
GET /api/ads/facebook/reward/transaction-id
Authorization: Bearer {token}
```

**Response:**
```json
{
    "transaction_id": "fan_123_550e8400-e29b-41d4-a716-446655440000"
}
```

### Revenue Reporting via Graph API

```php
use Eduardoks98\AdsFacebook\Services\AudienceNetworkService;
use Carbon\Carbon;

$fanService = app(AudienceNetworkService::class);

// Check if configured
if ($fanService->isConfigured()) {
    // Get general insights
    $insights = $fanService->getInsights(
        Carbon::now()->subDays(7),
        Carbon::now()
    );

    // Get revenue stats
    $revenue = $fanService->getRevenueStats(
        Carbon::now()->subDays(30),
        Carbon::now()
    );

    // Get impression stats
    $impressions = $fanService->getImpressionStats(
        Carbon::now()->subDays(7),
        Carbon::now()
    );

    // Get stats by country
    $byCountry = $fanService->getStatsByCountry(
        Carbon::now()->subDays(7),
        Carbon::now()
    );

    // Parse insights into usable format
    $parsed = $fanService->parseInsights($insights);
    /*
    [
        'impressions' => 10000,
        'clicks' => 500,
        'revenue' => 25.50,
        'requests' => 12000,
        'filled_requests' => 10000,
        'fill_rate' => 83.33,
        'cpm' => 2.55,
    ]
    */
}
```

### Getting an Access Token

1. Go to [Facebook for Developers](https://developers.facebook.com/)
2. Create or select your app
3. Go to Tools > Graph API Explorer
4. Select your app and generate an access token
5. For long-lived tokens, exchange it using the [Token Exchange endpoint](https://developers.facebook.com/docs/facebook-login/guides/access-tokens/get-long-lived)

### Available Metrics

| Metric | Description |
|--------|-------------|
| `fb_ad_network_imp` | Impressions |
| `fb_ad_network_click` | Clicks |
| `fb_ad_network_revenue` | Revenue |
| `fb_ad_network_request` | Ad requests |
| `fb_ad_network_filled_request` | Filled requests |
| `fb_ad_network_fill_rate` | Fill rate |
| `fb_ad_network_cpm` | CPM |

## Security Considerations

Since rewards are reported by the client (not server-to-server):

1. **Always require authentication** - Set `FACEBOOK_ADS_REQUIRE_AUTH=true`
2. **Use unique transaction IDs** - Prevent replay attacks
3. **Rate limit the endpoint** - Prevent abuse
4. **Monitor for anomalies** - Watch for suspicious patterns
5. **Consider additional validation** - Implement custom checks

### Adding Rate Limiting

```php
// In RouteServiceProvider or route file
Route::middleware(['auth:sanctum', 'throttle:10,1'])
    ->post('/api/ads/facebook/reward', [FanRewardController::class, 'handle']);
```

## Disabling Authentication (Not Recommended)

For testing only, you can disable authentication:

```env
FACEBOOK_ADS_REQUIRE_AUTH=false
```

**Warning:** Never disable authentication in production!

## Integration with Monetization Package

This package integrates with `eduardoks98/monetization`:

- Rewards are automatically created and processed
- Impressions are tracked in the `ad_impressions` table
- Virtual currency is credited to the user

## Troubleshooting

### Access Token Expired

Facebook access tokens expire. For production:
1. Use a long-lived token
2. Implement token refresh logic
3. Consider using a System User token

### Insights Not Loading

1. Verify your App ID is correct
2. Check the access token has the required permissions
3. Ensure the app has been reviewed for Audience Network access

### Rewards Not Processing

1. Check authentication is working
2. Verify the user exists
3. Look at the Laravel logs for errors

## License

MIT License
