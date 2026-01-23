# Geolocation Package

> Brazilian geolocation package with ViaCEP, address lookup, geocoding, and distance calculation.

## Overview

The `eduardoks98/geolocation` package provides comprehensive geolocation services for Brazil including postal code lookup (ViaCEP), geocoding with multiple providers, and distance calculations.

## Installation

```bash
composer require eduardoks98/geolocation
```

## Configuration

### Environment Variables

```env
# ViaCEP
VIACEP_TIMEOUT=10
VIACEP_CACHE_TTL=86400

# Geocoding Provider (nominatim, google, here)
GEOCODING_PROVIDER=nominatim

# Nominatim (free, no API key)
NOMINATIM_USER_AGENT=MyApp/1.0

# Google Maps (optional)
GOOGLE_MAPS_API_KEY=your_api_key

# HERE (optional)
HERE_API_KEY=your_api_key

# Distance
DISTANCE_UNIT=km

# Cache
GEOLOCATION_CACHE_ENABLED=true
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\Geolocation\GeolocationServiceProvider" --tag="config"
```

## Usage

### ViaCEP - Postal Code Lookup

```php
use Eduardoks98\Geolocation\Services\ViaCepService;

$viaCep = app(ViaCepService::class);

// Find address by CEP
$address = $viaCep->findByCep('01310-100');
// Returns:
// [
//     'cep' => '01310-100',
//     'street' => 'Avenida Paulista',
//     'complement' => 'de 1047 a 1865 - lado ímpar',
//     'neighborhood' => 'Bela Vista',
//     'city' => 'São Paulo',
//     'state' => 'SP',
//     'state_name' => 'São Paulo',
//     'ibge_code' => '3550308',
//     'ddd' => '11',
//     'formatted' => 'Avenida Paulista, Bela Vista, São Paulo/SP, CEP: 01310-100',
// ]

// Search by state, city, and street
$addresses = $viaCep->search('SP', 'São Paulo', 'Paulista');

// Validate CEP
$isValid = $viaCep->isValidCep('01310-100'); // true
```

### Geocoding

```php
use Eduardoks98\Geolocation\Services\GeocodingService;

$geocoding = app(GeocodingService::class);

// Address to coordinates
$coords = $geocoding->geocode('Avenida Paulista, 1000, São Paulo, SP');
// Returns: ['lat' => -23.5632, 'lng' => -46.6541, ...]

// Coordinates to address
$address = $geocoding->reverseGeocode(-23.5632, -46.6541);
```

### Distance Calculation

```php
use Eduardoks98\Geolocation\Services\DistanceService;

$distance = app(DistanceService::class);

// Calculate distance between two points
$km = $distance->calculate(
    -23.5505, -46.6333,  // São Paulo
    -22.9068, -43.1729   // Rio de Janeiro
);
// Returns: 357.86 (km)

// Find closest point
$closest = $distance->findClosest($origin, $points);

// Find points within radius
$nearby = $distance->findWithinRadius($center, $points, 5); // 5km

// Sort by distance
$sorted = $distance->sortByDistance($origin, $points);

// Calculate center point
$center = $distance->calculateCenter($points);

// Get bounding box
$bbox = $distance->getBoundingBox(-23.5505, -46.6333, 10);
```

## Geocoding Providers

| Provider | API Key | Rate Limit | Best For |
|----------|---------|------------|----------|
| Nominatim | No | 1 req/sec | Development, low volume |
| Google Maps | Yes | High | Production, high accuracy |
| HERE | Yes | Moderate | Production, alternative |

## Features

- ViaCEP integration (Brazilian postal codes)
- Address search by state/city/street
- Multiple geocoding providers
- Reverse geocoding
- Haversine distance calculation
- Find closest/within radius
- Response caching
- Brazilian states list

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [ViaCEP](https://viacep.com.br/)
- [Nominatim](https://nominatim.org/)
- [Google Maps Geocoding](https://developers.google.com/maps/documentation/geocoding)
- [HERE Geocoding](https://developer.here.com/documentation/geocoding-search-api)
