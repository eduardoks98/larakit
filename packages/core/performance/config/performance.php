<?php

return [
    'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
    'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 1000),  // ms
    'log_queries' => env('LOG_QUERIES', false),
    'log_memory' => env('LOG_MEMORY', true),
    'sample_rate' => env('PERFORMANCE_SAMPLE_RATE', 1.0),
    'pulse_enabled' => env('PULSE_ENABLED', false),  // Laravel 11+
];
