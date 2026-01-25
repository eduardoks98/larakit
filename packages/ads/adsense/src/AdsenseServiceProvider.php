<?php

namespace Eduardoks98\AdsAdsense;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\AdsAdsense\Services\AdsenseService;
use Eduardoks98\AdsAdsense\Services\AdsenseReportingService;

class AdsenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/adsense.php', 'adsense');

        $this->app->singleton(AdsenseService::class, function ($app) {
            return new AdsenseService();
        });

        $this->app->singleton(AdsenseReportingService::class, function ($app) {
            return new AdsenseReportingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'adsense');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/adsense.php' => config_path('adsense.php'),
        ], 'adsense-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'adsense-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/adsense'),
        ], 'adsense-views');

        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/adsense/css'),
            __DIR__ . '/../resources/js' => public_path('vendor/adsense/js'),
        ], 'adsense-assets');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AdsenseService::class,
            AdsenseReportingService::class,
        ];
    }
}
