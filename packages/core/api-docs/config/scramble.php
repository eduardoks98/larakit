<?php

return [
    'api_path' => 'api/v1',
    'api_domain' => null,

    'info' => [
        'title' => env('APP_NAME', 'API') . ' Documentation',
        'version' => '1.0.0',
        'description' => 'RESTful API built with Larakit packages',
    ],

    'servers' => [
        ['url' => env('APP_URL') . '/api/v1', 'description' => 'API Server'],
    ],

    'middleware' => ['web'],

    'ui' => 'stoplight',
];
