# Monetization Package for Laravel

A comprehensive monetization system for Laravel applications, providing ad impression tracking, reward management, and virtual currency for games and apps.

## Installation

```bash
composer require eduardoks98/monetization
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=monetization-config
```

Run migrations:

```bash
php artisan migrate
```

## Environment Variables

```env
MONETIZATION_CURRENCY_NAME=coins
MONETIZATION_CURRENCY_SYMBOL=
MONETIZATION_INITIAL_BALANCE=0
MONETIZATION_MAX_BALANCE=999999999
MONETIZATION_REWARD_QUEUE=default
MONETIZATION_MAX_RETRIES=3
MONETIZATION_RETRY_DELAY=60
MONETIZATION_TRACK_IMPRESSIONS=true
MONETIZATION_ANALYTICS_ENABLED=true
```

## Usage

### Add the Trait to Your User Model

```php
use Eduardoks98\Monetization\Traits\HasVirtualCurrency;

class User extends Authenticatable
{
    use HasVirtualCurrency;
}
```

### Virtual Currency Operations

```php
use Eduardoks98\Monetization\Services\CurrencyService;

$currencyService = app(CurrencyService::class);

// Get balance
$balance = $currencyService->getBalance($userId);

// Credit currency
$currencyService->credit($userId, 100, 'purchase', null, 'Purchased 100 coins');

// Debit currency
$currencyService->debit($userId, 50, 'spend', null, 'Spent on item');

// Check balance
if ($currencyService->hasSufficientBalance($userId, 50)) {
    // User can afford it
}
```

### Using Helper Functions

```php
// Get balance
$balance = monetization_balance($userId);

// Credit currency
monetization_credit($userId, 100, 'purchase');

// Debit currency
monetization_debit($userId, 50, 'spend');

// Check balance
if (monetization_has_balance($userId, 50)) {
    // Can afford
}

// Format currency
echo monetization_format_currency(1000); // "1,000 coins"
```

### Reward Management

```php
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardType;

$rewardService = app(RewardService::class);

// Create and dispatch a reward (processes async via queue)
$reward = $rewardService->createAndDispatch(
    userId: $user->id,
    provider: AdProvider::ADMOB,
    transactionId: 'unique_transaction_123',
    rewardItem: 'coins',
    rewardAmount: 50,
    adUnitId: 'ca-app-pub-xxx/xxx'
);

// Or create without dispatching
$reward = $rewardService->createReward(
    userId: $user->id,
    provider: AdProvider::UNITY,
    transactionId: 'unity_txn_456',
    rewardItem: 'gems',
    rewardAmount: 10
);

// Process immediately
$rewardService->processReward($reward);
```

### Ad Impression Tracking

```php
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Enums\AdProvider;

AdImpression::create([
    'user_id' => $userId,
    'provider' => AdProvider::ADMOB,
    'ad_unit_id' => 'ca-app-pub-xxx/xxx',
    'ad_type' => 'rewarded',
    'placement' => 'level_complete',
    'transaction_id' => 'unique_id',
    'revenue' => 0.01,
    'currency' => 'USD',
    'country' => 'BR',
    'platform' => 'android',
]);
```

### Analytics

```php
use Eduardoks98\Monetization\Services\AnalyticsService;

$analytics = app(AnalyticsService::class);

// Get dashboard stats (last 30 days)
$stats = $analytics->getDashboardStats();

// Get stats for specific date range
$stats = $analytics->getDashboardStats(
    startDate: now()->subDays(7),
    endDate: now()
);

// Get user-specific stats
$userStats = $analytics->getUserStats($userId);

// Get top users
$topEarners = $analytics->getTopUsers(10, 'rewards');
$topImpressions = $analytics->getTopUsers(10, 'impressions');
$topRevenue = $analytics->getTopUsers(10, 'revenue');
```

## Ad Provider Integration

This package provides the base monetization system. For specific ad provider integrations, install the corresponding packages:

- `eduardoks98/ads-google` - Google AdMob SSV
- `eduardoks98/ads-unity` - Unity Ads S2S
- `eduardoks98/ads-applovin` - AppLovin MAX
- `eduardoks98/ads-facebook` - Facebook Audience Network

## Enums

### AdProvider

```php
use Eduardoks98\Monetization\Enums\AdProvider;

AdProvider::ADMOB;      // Google AdMob
AdProvider::UNITY;      // Unity Ads
AdProvider::APPLOVIN;   // AppLovin MAX
AdProvider::FACEBOOK;   // Facebook Audience Network
AdProvider::IRONSOURCE; // ironSource
AdProvider::VUNGLE;     // Vungle
AdProvider::CHARTBOOST; // Chartboost
AdProvider::CUSTOM;     // Custom provider
```

### RewardStatus

```php
use Eduardoks98\Monetization\Enums\RewardStatus;

RewardStatus::PENDING;    // Waiting to be processed
RewardStatus::PROCESSING; // Currently being processed
RewardStatus::COMPLETED;  // Successfully processed
RewardStatus::FAILED;     // Processing failed
RewardStatus::CANCELLED;  // Cancelled
RewardStatus::DUPLICATE;  // Duplicate transaction detected
```

### RewardType

```php
use Eduardoks98\Monetization\Enums\RewardType;

RewardType::CURRENCY;     // Virtual currency
RewardType::ITEM;         // In-game item
RewardType::EXPERIENCE;   // Experience points
RewardType::SUBSCRIPTION; // Subscription time
RewardType::UNLOCK;       // Content unlock
RewardType::CUSTOM;       // Custom reward
```

## Database Tables

- `ad_impressions` - Tracks all ad impressions
- `rewards` - Stores reward requests and their status
- `virtual_currency_transactions` - Ledger of all currency transactions

## Queue Processing

Rewards are processed via Laravel's queue system. Make sure to run your queue worker:

```bash
php artisan queue:work
```

For failed rewards, you can retry them:

```php
$rewardService->retryFailed();
```

## License

MIT License
