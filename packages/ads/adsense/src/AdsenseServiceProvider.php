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
        $this->publishes([
            __DIR__ . '/../config/adsense.php' => config_path('adsense.php'),
        ], 'adsense-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'adsense-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerRoutes();
    }

    /**
     * Register routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
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
