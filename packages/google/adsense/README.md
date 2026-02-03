# Google AdSense Package for Laravel

Google AdSense integration for Laravel web applications with Filament 3 admin panel support. Manage ad units, track revenue, and easily integrate display ads into your web projects.

## Features

- Ad Unit management with Filament admin panel
- Multiple ad formats (Banner, Leaderboard, Rectangle, Skyscraper, Responsive)
- Multi-game/project support
- AdSense Management API integration for revenue reporting
- React component for frontend integration
- Position-based ad unit retrieval
- Caching for performance
- Dashboard widget with revenue stats

## Installation

```bash
composer require eduardoks98/ads-adsense
```

For revenue reporting (optional):

```bash
composer require google/apiclient
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=adsense-config
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=adsense-migrations
php artisan migrate
```

## Environment Variables

```env
# Required
ADSENSE_PUBLISHER_ID=ca-pub-XXXXXXXXXXXXXXXX
ADSENSE_ENABLED=true

# Optional - Revenue Reporting API
ADSENSE_API_ENABLED=false
ADSENSE_CREDENTIALS_PATH=/path/to/credentials.json
ADSENSE_ACCOUNT_ID=pub-XXXXXXXXXXXXXXXX

# Optional - Defaults
ADSENSE_TEST_MODE=false
ADSENSE_CACHE_ENABLED=true
ADSENSE_CACHE_TTL=300

# Optional - Filament
ADSENSE_FILAMENT_ENABLED=true
```

## Usage

### Managing Ad Units

Ad units can be managed through the Filament admin panel at `/admin/ad-units`.

Programmatically:

```php
use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;

// Create an ad unit
$adUnit = AdUnit::create([
    'name' => 'Homepage Banner',
    'slot_id' => '1234567890',
    'format' => AdFormat::LEADERBOARD,
    'position' => 'header',
    'is_active' => true,
]);

// With game association
$adUnit = AdUnit::create([
    'game_id' => $game->id,
    'name' => 'Game Sidebar',
    'slot_id' => '0987654321',
    'format' => AdFormat::RECTANGLE,
    'position' => 'sidebar',
]);
```

### Using the Service

```php
use Eduardoks98\AdsAdsense\Services\AdsenseService;

$adsense = app(AdsenseService::class);

// Get all ad units for a game
$adUnits = $adsense->getAdUnitsForGame($gameId);

// Get ad unit by position
$headerAd = $adsense->getAdUnitByPosition('header', $gameId);

// Render ad HTML
echo $adsense->renderAdUnit('header', $gameId);

// Get script tag
echo $adsense->getScriptTag();
```

### API Endpoints

#### Get Ad Units
```
GET /api/ads/units?game={game_id}
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Header Banner",
      "slot_id": "1234567890",
      "format": "leaderboard",
      "position": "header",
      "ad_client": "ca-pub-xxx",
      "is_responsive": false
    }
  ],
  "publisher_id": "ca-pub-xxx"
}
```

#### Get Ad Units by Position
```
GET /api/ads/units?game={game_id}&position=header
```

#### Get Revenue Report (Authenticated)
```
GET /api/ads/revenue?start=2025-01-01&end=2025-01-25
Authorization: Bearer {token}
```

#### Get Revenue Summary (Authenticated)
```
GET /api/ads/revenue/summary
Authorization: Bearer {token}
```

### Frontend Integration (React)

Copy the component from `resources/js/AdBanner.tsx` to your React project.

```tsx
import { AdBanner, AdByPosition } from './AdBanner';

// Direct usage
<AdBanner
  publisherId="ca-pub-xxx"
  slotId="1234567890"
  format="rectangle"
/>

// By position (fetches from API)
<AdByPosition
  position="header"
  gameId="bangshot"
  fallback={<div>No ad available</div>}
/>

// Responsive ad
<AdBanner
  publisherId="ca-pub-xxx"
  slotId="1234567890"
  format="responsive"
  style={{ marginBottom: '20px' }}
/>
```

### Blade Usage

```blade
{{-- Include AdSense script in head --}}
{!! app(\Eduardoks98\AdsAdsense\Services\AdsenseService::class)->getScriptTag() !!}

{{-- Render ad by position --}}
{!! app(\Eduardoks98\AdsAdsense\Services\AdsenseService::class)->renderAdUnit('header') !!}
```

### Revenue Reporting

```php
use Eduardoks98\AdsAdsense\Services\AdsenseReportingService;
use Carbon\Carbon;

$reporting = app(AdsenseReportingService::class);

// Check if API is configured
if ($reporting->isApiConfigured()) {
    // Get revenue report
    $report = $reporting->getRevenueReport(
        Carbon::now()->subDays(30),
        Carbon::now()
    );

    // Get today's revenue
    $today = $reporting->getTodayRevenue();

    // Get monthly revenue
    $month = $reporting->getMonthRevenue();

    // Compare periods
    $comparison = $reporting->getRevenueComparison(
        Carbon::now()->subDays(7),
        Carbon::now()
    );
}
```

## Ad Formats

| Format | Dimensions | Description |
|--------|------------|-------------|
| `banner` | 468x60 | Standard banner |
| `leaderboard` | 728x90 | Wide banner for headers |
| `rectangle` | 300x250 | Medium rectangle |
| `skyscraper` | 120x600 | Tall sidebar ad |
| `large_rectangle` | 336x280 | Large rectangle |
| `responsive` | Auto | Adapts to container |

## Multi-Game Support

Configure the game model in `config/adsense.php`:

```php
'game_model' => \App\Models\Game::class,
```

Ad units with `game_id = null` are global and available to all games.

## Filament Dashboard Widget

Add the widget to your Filament panel:

```php
use Eduardoks98\AdsAdsense\Filament\Widgets\AdsenseRevenueWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->widgets([
                AdsenseRevenueWidget::class,
            ]);
    }
}
```

## Setting Up Revenue Reporting API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create or select a project
3. Enable the AdSense Management API
4. Create a Service Account
5. Download the JSON credentials
6. Grant the service account access to your AdSense account
7. Set environment variables:

```env
ADSENSE_API_ENABLED=true
ADSENSE_CREDENTIALS_PATH=/path/to/credentials.json
ADSENSE_ACCOUNT_ID=pub-XXXXXXXXXXXXXXXX
```

## Comparison: AdSense vs AdMob

| Aspect | AdSense (this package) | AdMob (ads-google) |
|--------|------------------------|-------------------|
| Platform | Web sites | Mobile apps |
| Callback | None | S2S with signature |
| Integration | JavaScript tag | Native SDK |
| Reports | Management API | Via SDK |
| Rewards | No (display only) | Yes (rewarded ads) |

## License

MIT License
