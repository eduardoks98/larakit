# AdSense Quick Start Guide

Get AdSense working in your Laravel project in 5 minutes.

## 1. Install

```bash
composer require eduardoks98/ads-adsense
```

## 2. Configure

Add to `.env`:

```env
ADSENSE_PUBLISHER_ID=ca-pub-XXXXXXXXXXXXXXXX
```

## 3. Migrate

```bash
php artisan migrate
```

## 4. Create Ad Unit

Go to Filament admin panel → Monetização → Ad Units → Create

Or via tinker:

```php
\Eduardoks98\AdsAdsense\Models\AdUnit::create([
    'name' => 'Header Banner',
    'slot_id' => '1234567890',
    'format' => 'leaderboard',
    'position' => 'header',
    'is_active' => true,
]);
```

## 5. Display Ads

### Blade

```blade
{{-- In <head> --}}
{!! app(\Eduardoks98\AdsAdsense\Services\AdsenseService::class)->getScriptTag() !!}

{{-- Where you want the ad --}}
{!! app(\Eduardoks98\AdsAdsense\Services\AdsenseService::class)->renderAdUnit('header') !!}
```

### React

```tsx
import { AdByPosition } from './AdBanner';

function Header() {
  return (
    <header>
      <AdByPosition position="header" />
    </header>
  );
}
```

## Done!

Your ads should now be displaying. Check the admin panel for ad management.

---

## Common Positions

- `header` - Top of page
- `sidebar` - Side column
- `between_content` - Between content sections
- `footer` - Bottom of page
- `in_article` - Within article content
- `between_matches` - Game-specific

## Test Mode

For development, enable test mode:

```env
ADSENSE_TEST_MODE=true
```

This shows test ads without affecting your AdSense account.
