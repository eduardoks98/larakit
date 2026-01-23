<?php

namespace Eduardoks98\Geolocation;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\Geolocation\Services\ViaCepService;
use Eduardoks98\Geolocation\Services\GeocodingService;
use Eduardoks98\Geolocation\Services\DistanceService;

/**
 * Geolocation Service Provider
 */
class GeolocationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/geolocation.php' => config_path('geolocation.php'),
        ], 'config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/geolocation.php',
            'geolocation'
        );

        // Register ViaCepService singleton
        $this->app->singleton(ViaCepService::class, function ($app) {
            return new ViaCepService();
        });

        // Register GeocodingService singleton
        $this->app->singleton(GeocodingService::class, function ($app) {
            return new GeocodingService();
        });

        // Register DistanceService singleton
        $this->app->singleton(DistanceService::class, function ($app) {
            return new DistanceService();
        });

        // Register aliases
        $this->app->alias(ViaCepService::class, 'viacep');
        $this->app->alias(GeocodingService::class, 'geocoding');
        $this->app->alias(DistanceService::class, 'distance');
    }
}
