<?php

namespace Eduardoks98\Geolocation\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Eduardoks98\Geolocation\Exceptions\GeolocationException;

/**
 * ViaCEP Service
 *
 * Brazilian postal code (CEP) lookup using ViaCEP API.
 * Free service with no authentication required.
 *
 * @see https://viacep.com.br/
 */
class ViaCepService
{
    protected Client $client;
    protected array $config;

    public function __construct()
    {
        $this->config = config('geolocation.viacep');

        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'] ?? 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Find address by CEP (postal code).
     *
     * @param string $cep CEP (with or without dash)
     * @return array|null Address data or null if not found
     * @throws GeolocationException
     */
    public function findByCep(string $cep): ?array
    {
        $cep = $this->normalizeCep($cep);

        if (!$this->isValidCepFormat($cep)) {
            throw GeolocationException::invalidCep($cep);
        }

        $cacheKey = $this->getCacheKey("cep:{$cep}");

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->get("/{$cep}/json/");
            $data = json_decode($response->getBody()->getContents(), true);

            // ViaCEP returns {"erro": true} for invalid CEPs
            if (isset($data['erro']) && $data['erro'] === true) {
                return null;
            }

            $result = $this->formatAddress($data);

            if ($this->isCacheEnabled()) {
                Cache::put($cacheKey, $result, $this->config['cache_ttl'] ?? 86400);
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('ViaCEP request failed', [
                'cep' => $cep,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('ViaCEP', $e->getMessage());
        }
    }

    /**
     * Search addresses by state, city, and street.
     *
     * @param string $uf State code (2 letters)
     * @param string $city City name
     * @param string $street Street name (at least 3 characters)
     * @return array List of addresses
     * @throws GeolocationException
     */
    public function search(string $uf, string $city, string $street): array
    {
        $uf = strtoupper($uf);
        $city = urlencode($city);
        $street = urlencode($street);

        if (strlen($street) < 3) {
            throw GeolocationException::invalidSearch('Street name must have at least 3 characters');
        }

        $cacheKey = $this->getCacheKey("search:{$uf}:{$city}:{$street}");

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->get("/{$uf}/{$city}/{$street}/json/");
            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data) || !is_array($data)) {
                return [];
            }

            $results = array_map(fn($item) => $this->formatAddress($item), $data);

            if ($this->isCacheEnabled()) {
                Cache::put($cacheKey, $results, $this->config['cache_ttl'] ?? 86400);
            }

            return $results;
        } catch (GuzzleException $e) {
            Log::error('ViaCEP search failed', [
                'uf' => $uf,
                'city' => $city,
                'street' => $street,
                'error' => $e->getMessage(),
            ]);
            throw GeolocationException::apiError('ViaCEP', $e->getMessage());
        }
    }

    /**
     * Validate if a CEP exists.
     *
     * @param string $cep
     * @return bool
     */
    public function isValidCep(string $cep): bool
    {
        try {
            $result = $this->findByCep($cep);
            return $result !== null;
        } catch (GeolocationException $e) {
            return false;
        }
    }

    /**
     * Get all CEPs from a city.
     *
     * @param string $uf State code
     * @param string $city City name
     * @return array List of CEP ranges
     */
    public function getCepsByCity(string $uf, string $city): array
    {
        // Search with a generic term to get multiple results
        try {
            return $this->search($uf, $city, 'a');
        } catch (GeolocationException $e) {
            return [];
        }
    }

    /**
     * Format address data from ViaCEP response.
     */
    protected function formatAddress(array $data): array
    {
        return [
            'cep' => $data['cep'] ?? null,
            'street' => $data['logradouro'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['localidade'] ?? null,
            'state' => $data['uf'] ?? null,
            'state_name' => $this->getStateName($data['uf'] ?? ''),
            'ibge_code' => $data['ibge'] ?? null,
            'gia' => $data['gia'] ?? null,
            'ddd' => $data['ddd'] ?? null,
            'siafi' => $data['siafi'] ?? null,
            // Formatted address
            'formatted' => $this->formatFullAddress($data),
        ];
    }

    /**
     * Format full address string.
     */
    protected function formatFullAddress(array $data): string
    {
        $parts = [];

        if (!empty($data['logradouro'])) {
            $parts[] = $data['logradouro'];
        }

        if (!empty($data['bairro'])) {
            $parts[] = $data['bairro'];
        }

        if (!empty($data['localidade']) && !empty($data['uf'])) {
            $parts[] = "{$data['localidade']}/{$data['uf']}";
        }

        if (!empty($data['cep'])) {
            $parts[] = "CEP: {$data['cep']}";
        }

        return implode(', ', $parts);
    }

    /**
     * Get state name from code.
     */
    protected function getStateName(string $uf): ?string
    {
        $states = config('geolocation.states', []);
        return $states[strtoupper($uf)] ?? null;
    }

    /**
     * Normalize CEP (remove non-digits).
     */
    protected function normalizeCep(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }

    /**
     * Check if CEP format is valid (8 digits).
     */
    protected function isValidCepFormat(string $cep): bool
    {
        return preg_match('/^\d{8}$/', $cep) === 1;
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
