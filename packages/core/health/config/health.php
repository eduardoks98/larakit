<?php

return [
    'enabled' => env('HEALTH_CHECKS_ENABLED', true),

    'checks' => [
        'database' => env('HEALTH_CHECK_DATABASE', true),
        'cache' => env('HEALTH_CHECK_CACHE', true),
        'queue' => env('HEALTH_CHECK_QUEUE', true),
    ],

    'routes' => [
        'prefix' => 'health',
        'middleware' => [],
    ],
];
