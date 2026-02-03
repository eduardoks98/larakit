<?php

namespace Eduardoks98\AdsApplovin;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\AdsApplovin\Services\MaxCallbackService;
use Eduardoks98\AdsApplovin\Services\MaxReportingService;

class AdsApplovinServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ads-applovin.php', 'ads-applovin');

        $this->app->singleton(MaxCallbackService::class, function ($app) {
            return new MaxCallbackService();
        });

        $this->app->singleton(MaxReportingService::class, function ($app) {
            return new MaxReportingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ads-applovin.php' => config_path('ads-applovin.php'),
        ], 'ads-applovin-config');

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
            MaxCallbackService::class,
            MaxReportingService::class,
        ];
    }
}
