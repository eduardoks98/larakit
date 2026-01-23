<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ViaCEP Configuration
    |--------------------------------------------------------------------------
    |
    | ViaCEP is a free Brazilian postal code lookup service.
    |
    */
    'viacep' => [
        'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),
        'timeout' => env('VIACEP_TIMEOUT', 10),
        'cache_ttl' => env('VIACEP_CACHE_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | IBGE API Configuration
    |--------------------------------------------------------------------------
    |
    | IBGE provides official Brazilian geographic data.
    |
    */
    'ibge' => [
        'base_url' => env('IBGE_BASE_URL', 'https://servicodados.ibge.gov.br/api/v1'),
        'timeout' => env('IBGE_TIMEOUT', 10),
        'cache_ttl' => env('IBGE_CACHE_TTL', 604800), // 7 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Geocoding Configuration
    |--------------------------------------------------------------------------
    |
    | Configure geocoding providers for coordinate lookup.
    |
    */
    'geocoding' => [
        'provider' => env('GEOCODING_PROVIDER', 'nominatim'), // nominatim, google, here

        'nominatim' => [
            'base_url' => env('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'),
            'user_agent' => env('NOMINATIM_USER_AGENT', 'LaravelApp/1.0'),
            'timeout' => env('NOMINATIM_TIMEOUT', 10),
        ],

        'google' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
            'base_url' => 'https://maps.googleapis.com/maps/api/geocode/json',
            'timeout' => env('GOOGLE_MAPS_TIMEOUT', 10),
        ],

        'here' => [
            'api_key' => env('HERE_API_KEY'),
            'base_url' => 'https://geocode.search.hereapi.com/v1/geocode',
            'timeout' => env('HERE_TIMEOUT', 10),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Distance Calculation
    |--------------------------------------------------------------------------
    |
    | Default settings for distance calculations.
    |
    */
    'distance' => [
        'unit' => env('DISTANCE_UNIT', 'km'), // km, mi, m
        'earth_radius_km' => 6371,
        'earth_radius_mi' => 3959,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for API responses.
    |
    */
    'cache' => [
        'enabled' => env('GEOLOCATION_CACHE_ENABLED', true),
        'prefix' => env('GEOLOCATION_CACHE_PREFIX', 'geolocation'),
        'driver' => env('GEOLOCATION_CACHE_DRIVER'), // null = default driver
    ],

    /*
    |--------------------------------------------------------------------------
    | Brazilian States
    |--------------------------------------------------------------------------
    |
    | List of Brazilian states with codes.
    |
    */
    'states' => [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ],
];
