<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Response Format
    |--------------------------------------------------------------------------
    |
    | Formato de resposta padrão da API.
    | Opções: 'rfc7807' (RFC 7807 Problem Details) ou 'custom'
    |
    */
    'response_format' => env('API_RESPONSE_FORMAT', 'rfc7807'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Versão padrão da API. Será incluída no header X-API-Version.
    |
    */
    'api_version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Configurações de paginação padrão para API Resources.
    |
    */
    'pagination' => [
        'default_per_page' => env('API_PAGINATION_DEFAULT', 15),
        'max_per_page' => env('API_PAGINATION_MAX', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    |
    | Configurações do cliente HTTP (Guzzle) para chamadas externas.
    |
    */
    'http_client' => [
        'timeout' => env('API_HTTP_TIMEOUT', 30),
        'retry_attempts' => env('API_HTTP_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('API_HTTP_RETRY_DELAY', 1000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configurações de tratamento de erros.
    |
    */
    'error_handling' => [
        'include_trace' => env('API_INCLUDE_TRACE', !app()->isProduction()),
        'log_errors' => env('API_LOG_ERRORS', true),
    ],
];
