# 📊 Performance - Laravel Pulse Integration

Performance monitoring with request tracking, N+1 detection, and Laravel Pulse integration.

## Installation
```bash
composer require eduardoks98/performance
php artisan vendor:publish --provider="Eduardoks98\Performance\PerformanceServiceProvider"
php artisan migrate
```

## Features
- ✅ Request duration tracking
- ✅ Query count and time monitoring
- ✅ Memory usage tracking
- ✅ Slow request detection
- ✅ Laravel Pulse integration (Laravel 11+)

## Quick Start
```php
// Apply middleware
Route::middleware(['performance.monitor'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

## Configuration
```php
'slow_request_threshold' => 1000,  // ms
'sample_rate' => 1.0,  // 100% of requests
'pulse_enabled' => true,
```

## License
MIT - Eduardo Steffens (@eduardoks98)
