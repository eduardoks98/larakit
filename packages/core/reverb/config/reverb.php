<?php

return [
    'servers' => [
        [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_SERVER_HOSTNAME', 'localhost'),
            'max_request_size' => 10000,
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
            ],
        ],
    ],

    'apps' => [
        [
            'app_id' => env('REVERB_APP_ID'),
            'app_key' => env('REVERB_APP_KEY'),
            'app_secret' => env('REVERB_APP_SECRET'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
            ],
        ],
    ],
];
