# Geolocation Package

> Brazilian geolocation package with ViaCEP, address lookup, geocoding, and distance calculation.

## Features

- ViaCEP integration (Brazilian postal code lookup)
- Address search by state, city, and street
- Geocoding (address to coordinates)
- Reverse geocoding (coordinates to address)
- Distance calculation (Haversine formula)
- Multiple geocoding providers (Nominatim, Google Maps, HERE)
- Response caching
- Brazilian states list

## Installation

```bash
composer require eduardoks98/geolocation
```

## Configuration

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\Geolocation\GeolocationServiceProvider" --tag="config"
```

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

## Usage

### ViaCEP - Postal Code Lookup

```php
use Eduardoks98\Geolocation\Services\ViaCepService;

$viaCep = app(ViaCepService::class);

// Find address by CEP
$address = $viaCep->findByCep('01310-100');
// or
$address = $viaCep->findByCep('01310100');

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
```

### Search Address

```php
// Search by state, city, and street
$addresses = $viaCep->search('SP', 'São Paulo', 'Paulista');

// Returns array of matching addresses
```

### Validate CEP

```php
// Check if CEP exists
$isValid = $viaCep->isValidCep('01310-100'); // true
$isValid = $viaCep->isValidCep('00000-000'); // false
```

### Geocoding

```php
use Eduardoks98\Geolocation\Services\GeocodingService;

$geocoding = app(GeocodingService::class);

// Address to coordinates
$coords = $geocoding->geocode('Avenida Paulista, 1000, São Paulo, SP');
// Returns:
// [
//     'lat' => -23.5632,
//     'lng' => -46.6541,
//     'display_name' => 'Avenida Paulista, São Paulo, SP, Brasil',
//     'provider' => 'nominatim',
// ]

// Coordinates to address
$address = $geocoding->reverseGeocode(-23.5632, -46.6541);
// Returns:
// [
//     'lat' => -23.5632,
//     'lng' => -46.6541,
//     'street' => 'Avenida Paulista',
//     'number' => '1000',
//     'neighborhood' => 'Bela Vista',
//     'city' => 'São Paulo',
//     'state' => 'São Paulo',
//     'postcode' => '01310-100',
// ]
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

// Using array format
$km = $distance->between(
    ['lat' => -23.5505, 'lng' => -46.6333],
    ['lat' => -22.9068, 'lng' => -43.1729]
);

// With different units
$miles = $distance->calculate(..., ..., 'mi');
$meters = $distance->calculate(..., ..., 'm');
```

### Find Closest Point

```php
$origin = ['lat' => -23.5505, 'lng' => -46.6333];

$stores = [
    ['lat' => -23.5550, 'lng' => -46.6400],
    ['lat' => -23.5600, 'lng' => -46.6500],
    ['lat' => -23.5700, 'lng' => -46.6600],
];

$closest = $distance->findClosest($origin, $stores);
// Returns:
// [
//     'point' => ['lat' => -23.5550, 'lng' => -46.6400],
//     'index' => 0,
//     'distance' => 0.82,
//     'unit' => 'km',
// ]
```

### Find Points Within Radius

```php
$center = ['lat' => -23.5505, 'lng' => -46.6333];
$radius = 5; // km

$nearby = $distance->findWithinRadius($center, $stores, $radius);
// Returns sorted array of points within 5km
```

### Sort by Distance

```php
$sorted = $distance->sortByDistance($origin, $stores);
// Returns points sorted by distance (ascending)

$sorted = $distance->sortByDistance($origin, $stores, null, 'desc');
// Descending order
```

### Calculate Center Point

```php
$center = $distance->calculateCenter($stores);
// Returns center point of all locations
```

### Bounding Box

```php
// Get bounding box for a radius
$bbox = $distance->getBoundingBox(-23.5505, -46.6333, 10);
// Returns: [min_lat, min_lng, max_lat, max_lng]

// Check if point is within bounding box
$isWithin = $distance->isWithinBoundingBox($point, $bbox);
```

## Geocoding Providers

### Nominatim (Default - Free)

```env
GEOCODING_PROVIDER=nominatim
NOMINATIM_USER_AGENT=MyApp/1.0
```

- Free, no API key required
- Rate limited (1 request/second)
- Powered by OpenStreetMap
- **Important**: Set a valid User-Agent per [Nominatim policy](https://operations.osmfoundation.org/policies/nominatim/)

### Google Maps

```env
GEOCODING_PROVIDER=google
GOOGLE_MAPS_API_KEY=your_api_key
```

- Requires API key
- Higher accuracy
- **Cost**: $5/1,000 requests ($200/month free credit = 40,000 requests)

**How to get Google Maps API key:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Go to **APIs & Services** → **Enable APIs and Services**
4. Search and enable **Geocoding API**
5. Go to **APIs & Services** → **Credentials**
6. Click **Create Credentials** → **API key**
7. (Recommended) Restrict the key:
   - **Application restrictions**: HTTP referrers or IP addresses
   - **API restrictions**: Geocoding API only
8. Copy the API key to your `.env`

### HERE

```env
GEOCODING_PROVIDER=here
HERE_API_KEY=your_api_key
```

- Requires API key
- Good accuracy
- **Cost**: $1/1,000 requests (250,000 requests/month free)

**How to get HERE API key:**

1. Go to [HERE Developer Portal](https://developer.here.com/)
2. Create a free account
3. Go to **Projects** → **Create new project**
4. Give it a name and create
5. In the project, go to **REST** section
6. Click **Generate App** → **Create API key**
7. Copy the API key to your `.env`

### Provider Comparison

| Provider | Cost | Free Tier | Accuracy | Rate Limit |
|----------|------|-----------|----------|------------|
| Nominatim | Free | Unlimited | Good | 1 req/sec |
| Google Maps | $5/1k | 40k/month | Excellent | High |
| HERE | $1/1k | 250k/month | Very Good | High |

## Caching

Responses are cached by default:

```php
// ViaCEP: 24 hours
// Geocoding: 24 hours
// IBGE: 7 days
```

Disable caching:

```env
GEOLOCATION_CACHE_ENABLED=false
```

## Brazilian States

```php
$states = config('geolocation.states');

// [
//     'AC' => 'Acre',
//     'AL' => 'Alagoas',
//     'SP' => 'São Paulo',
//     ...
// ]
```

## Error Handling

```php
use Eduardoks98\Geolocation\Exceptions\GeolocationException;

try {
    $address = $viaCep->findByCep('invalid');
} catch (GeolocationException $e) {
    // Handle error
    echo $e->getMessage();
}
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [ViaCEP](https://viacep.com.br/) - Brazilian postal code API
- [Nominatim](https://nominatim.org/) - OpenStreetMap geocoding
- [IBGE](https://servicodados.ibge.gov.br/api/docs) - Brazilian geographic data

## License

MIT License
