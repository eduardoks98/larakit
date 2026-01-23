<?php

namespace Eduardoks98\Geolocation\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Eduardoks98\Geolocation\Exceptions\GeolocationException;

/**
 * Geocoding Service
 *
 * Convert addresses to coordinates and vice versa.
 * Supports multiple providers: Nominatim (free), Google Maps, HERE.
 */
class GeocodingService
{
    protected array $config;
    protected string $provider;

    public function __construct()
    {
        $this->config = config('geolocation.geocoding');
        $this->provider = $this->config['provider'] ?? 'nominatim';
    }

    /**
     * Get coordinates from an address (geocoding).
     *
     * @param string $address Full address string
     * @param array $options Additional options
     * @return array|null Coordinates [lat, lng] or null if not found
     * @throws GeolocationException
     */
    public function geocode(string $address, array $options = []): ?array
    {
        $cacheKey = $this->getCacheKey("geocode:" . md5($address));

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = match ($this->provider) {
            'google' => $this->geocodeWithGoogle($address, $options),
            'here' => $this->geocodeWithHere($address, $options),
            default => $this->geocodeWithNominatim($address, $options),
        };

        if ($result && $this->isCacheEnabled()) {
            Cache::put($cacheKey, $result, 86400); // 24 hours
        }

        return $result;
    }

    /**
     * Get address from coordinates (reverse geocoding).
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param array $options Additional options
     * @return array|null Address data or null if not found
     * @throws GeolocationException
     */
    public function reverseGeocode(float $lat, float $lng, array $options = []): ?array
    {
        $cacheKey = $this->getCacheKey("reverse:{$lat},{$lng}");

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = match ($this->provider) {
            'google' => $this->reverseGeocodeWithGoogle($lat, $lng, $options),
            'here' => $this->reverseGeocodeWithHere($lat, $lng, $options),
            default => $this->reverseGeocodeWithNominatim($lat, $lng, $options),
        };

        if ($result && $this->isCacheEnabled()) {
            Cache::put($cacheKey, $result, 86400);
        }

        return $result;
    }

    /**
     * Geocode using Nominatim (OpenStreetMap).
     */
    protected function geocodeWithNominatim(string $address, array $options = []): ?array
    {
        $config = $this->config['nominatim'];

        $client = new Client([
            'base_uri' => $config['base_url'],
            'timeout' => $config['timeout'] ?? 10,
            'headers' => [
                'User-Agent' => $config['user_agent'] ?? 'LaravelApp/1.0',
            ],
        ]);

        try {
            $response = $client->get('/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'countrycodes' => $options['country'] ?? 'br',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            $result = $data[0];

            return [
                'lat' => (float) $result['lat'],
                'lng' => (float) $result['lon'],
                'display_name' => $result['display_name'] ?? null,
                'type' => $result['type'] ?? null,
                'importance' => $result['importance'] ?? null,
                'provider' => 'nominatim',
            ];
        } catch (GuzzleException $e) {
            Log::error('Nominatim geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('Nominatim', $e->getMessage());
        }
    }

    /**
     * Reverse geocode using Nominatim.
     */
    protected function reverseGeocodeWithNominatim(float $lat, float $lng, array $options = []): ?array
    {
        $config = $this->config['nominatim'];

        $client = new Client([
            'base_uri' => $config['base_url'],
            'timeout' => $config['timeout'] ?? 10,
            'headers' => [
                'User-Agent' => $config['user_agent'] ?? 'LaravelApp/1.0',
            ],
        ]);

        try {
            $response = $client->get('/reverse', [
                'query' => [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data) || isset($data['error'])) {
                return null;
            }

            $address = $data['address'] ?? [];

            return [
                'lat' => (float) $data['lat'],
                'lng' => (float) $data['lon'],
                'display_name' => $data['display_name'] ?? null,
                'street' => $address['road'] ?? $address['pedestrian'] ?? null,
                'number' => $address['house_number'] ?? null,
                'neighborhood' => $address['suburb'] ?? $address['neighbourhood'] ?? null,
                'city' => $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
                'state' => $address['state'] ?? null,
                'country' => $address['country'] ?? null,
                'postcode' => $address['postcode'] ?? null,
                'provider' => 'nominatim',
            ];
        } catch (GuzzleException $e) {
            Log::error('Nominatim reverse geocoding failed', [
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('Nominatim', $e->getMessage());
        }
    }

    /**
     * Geocode using Google Maps.
     */
    protected function geocodeWithGoogle(string $address, array $options = []): ?array
    {
        $config = $this->config['google'];

        if (empty($config['api_key'])) {
            throw GeolocationException::missingApiKey('Google Maps');
        }

        $client = new Client([
            'timeout' => $config['timeout'] ?? 10,
        ]);

        try {
            $response = $client->get($config['base_url'], [
                'query' => [
                    'address' => $address,
                    'key' => $config['api_key'],
                    'components' => 'country:BR',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return null;
            }

            $result = $data['results'][0];
            $location = $result['geometry']['location'];

            return [
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'display_name' => $result['formatted_address'] ?? null,
                'place_id' => $result['place_id'] ?? null,
                'types' => $result['types'] ?? [],
                'provider' => 'google',
            ];
        } catch (GuzzleException $e) {
            Log::error('Google Maps geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('Google Maps', $e->getMessage());
        }
    }

    /**
     * Reverse geocode using Google Maps.
     */
    protected function reverseGeocodeWithGoogle(float $lat, float $lng, array $options = []): ?array
    {
        $config = $this->config['google'];

        if (empty($config['api_key'])) {
            throw GeolocationException::missingApiKey('Google Maps');
        }

        $client = new Client([
            'timeout' => $config['timeout'] ?? 10,
        ]);

        try {
            $response = $client->get($config['base_url'], [
                'query' => [
                    'latlng' => "{$lat},{$lng}",
                    'key' => $config['api_key'],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return null;
            }

            $result = $data['results'][0];
            $components = $this->parseGoogleAddressComponents($result['address_components'] ?? []);

            return [
                'lat' => $lat,
                'lng' => $lng,
                'display_name' => $result['formatted_address'] ?? null,
                'street' => $components['route'] ?? null,
                'number' => $components['street_number'] ?? null,
                'neighborhood' => $components['sublocality'] ?? $components['neighborhood'] ?? null,
                'city' => $components['locality'] ?? $components['administrative_area_level_2'] ?? null,
                'state' => $components['administrative_area_level_1'] ?? null,
                'country' => $components['country'] ?? null,
                'postcode' => $components['postal_code'] ?? null,
                'provider' => 'google',
            ];
        } catch (GuzzleException $e) {
            Log::error('Google Maps reverse geocoding failed', [
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('Google Maps', $e->getMessage());
        }
    }

    /**
     * Parse Google address components.
     */
    protected function parseGoogleAddressComponents(array $components): array
    {
        $result = [];

        foreach ($components as $component) {
            foreach ($component['types'] as $type) {
                $result[$type] = $component['long_name'];
            }
        }

        return $result;
    }

    /**
     * Geocode using HERE.
     */
    protected function geocodeWithHere(string $address, array $options = []): ?array
    {
        $config = $this->config['here'];

        if (empty($config['api_key'])) {
            throw GeolocationException::missingApiKey('HERE');
        }

        $client = new Client([
            'timeout' => $config['timeout'] ?? 10,
        ]);

        try {
            $response = $client->get($config['base_url'], [
                'query' => [
                    'q' => $address,
                    'apiKey' => $config['api_key'],
                    'in' => 'countryCode:BRA',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['items'])) {
                return null;
            }

            $result = $data['items'][0];
            $position = $result['position'];

            return [
                'lat' => $position['lat'],
                'lng' => $position['lng'],
                'display_name' => $result['title'] ?? null,
                'provider' => 'here',
            ];
        } catch (GuzzleException $e) {
            Log::error('HERE geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('HERE', $e->getMessage());
        }
    }

    /**
     * Reverse geocode using HERE.
     */
    protected function reverseGeocodeWithHere(float $lat, float $lng, array $options = []): ?array
    {
        $config = $this->config['here'];

        if (empty($config['api_key'])) {
            throw GeolocationException::missingApiKey('HERE');
        }

        $client = new Client([
            'timeout' => $config['timeout'] ?? 10,
        ]);

        try {
            $response = $client->get('https://revgeocode.search.hereapi.com/v1/revgeocode', [
                'query' => [
                    'at' => "{$lat},{$lng}",
                    'apiKey' => $config['api_key'],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['items'])) {
                return null;
            }

            $result = $data['items'][0];
            $address = $result['address'] ?? [];

            return [
                'lat' => $lat,
                'lng' => $lng,
                'display_name' => $result['title'] ?? null,
                'street' => $address['street'] ?? null,
                'number' => $address['houseNumber'] ?? null,
                'neighborhood' => $address['district'] ?? null,
                'city' => $address['city'] ?? null,
                'state' => $address['state'] ?? null,
                'country' => $address['countryName'] ?? null,
                'postcode' => $address['postalCode'] ?? null,
                'provider' => 'here',
            ];
        } catch (GuzzleException $e) {
            Log::error('HERE reverse geocoding failed', [
                'lat' => $lat,
                'lng' => $lng,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('HERE', $e->getMessage());
        }
    }

    /**
     * Check if cache is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return config('geolocation.cache.enabled', true);
    }

    /**
     * Get cache key with prefix.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('geolocation.cache.prefix', 'geolocation');
        return "{$prefix}:{$key}";
    }
}
