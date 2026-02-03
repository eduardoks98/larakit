# Google Analytics (GA4) Package for Laravel

Simple and flexible Google Analytics 4 (gtag.js) integration for Laravel applications.

## Installation

```bash
composer require eduardoks98/analytics-google
```

## Configuration

Add to your `.env`:

```env
GA_MEASUREMENT_ID=G-XXXXXXXXXX
GA_ENABLED=true
GA_DEBUG=false
GA_ANONYMIZE_IP=true
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=google-analytics-config
```

## Usage

### Basic Usage (Blade)

Add the gtag component in your layout's `<head>`:

```blade
<head>
    <x-ga-gtag />
    <!-- other head elements -->
</head>
```

### Track Custom Events

```blade
{{-- Track a page-specific event --}}
<x-ga-event name="signup" :params="['method' => 'google']" />

{{-- Track game events --}}
<x-ga-event name="game_start" :params="['game_name' => 'BangShot', 'mode' => 'ranked']" />
<x-ga-event name="game_end" :params="['game_name' => 'BangShot', 'result' => 'win', 'duration' => 120]" />
```

### Using the Facade

```php
use Eduardoks98\AnalyticsGoogle\Facades\GoogleAnalytics;

// Check if tracking is enabled
if (GoogleAnalytics::isEnabled()) {
    // Do something
}

// Get the measurement ID
$id = GoogleAnalytics::getMeasurementId();

// Generate event script
$script = GoogleAnalytics::event('purchase', [
    'transaction_id' => 'T12345',
    'value' => 9.99,
    'currency' => 'BRL'
]);
```

### Using the Service

```php
use Eduardoks98\AnalyticsGoogle\Services\GoogleAnalyticsService;

public function __construct(GoogleAnalyticsService $ga)
{
    $this->ga = $ga;
}

public function trackPurchase()
{
    return $this->ga->event('purchase', ['value' => 19.99]);
}
```

## Recommended Events for Games

### Player Events
- `login` - When player logs in
- `sign_up` - New player registration
- `tutorial_begin` - Started tutorial
- `tutorial_complete` - Finished tutorial

### Game Events
- `game_start` - Match started
- `game_end` - Match ended
- `level_up` - Player leveled up
- `unlock_achievement` - Achievement unlocked

### Monetization Events
- `purchase` - In-app purchase
- `ad_impression` - Ad was shown
- `earn_virtual_currency` - Earned in-game currency
- `spend_virtual_currency` - Spent in-game currency

## JavaScript Helper for SPA/React

For frontend tracking (React, Vue, etc.), you can use gtag directly:

```javascript
// Track page view
gtag('event', 'page_view', {
    page_title: document.title,
    page_location: window.location.href
});

// Track game match
gtag('event', 'game_end', {
    game_name: 'BangShot',
    result: 'win',
    duration_seconds: 120,
    player_count: 2
});
```

## Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `measurement_id` | GA4 Measurement ID | `null` |
| `enabled` | Enable/disable tracking | `true` |
| `debug` | Enable debug mode | `false` |
| `anonymize_ip` | Anonymize visitor IPs | `true` |
| `track_in_environments` | Environments to track | `['production']` |
| `excluded_routes` | Routes to skip tracking | `['admin/*', 'api/*']` |

## License

MIT
