<?php

namespace Eduardoks98\AdsUnity;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\AdsUnity\Services\UnityCallbackService;
use Eduardoks98\AdsUnity\Services\UnityStatsService;

class AdsUnityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ads-unity.php', 'ads-unity');

        $this->app->singleton(UnityCallbackService::class, function ($app) {
            return new UnityCallbackService();
        });

        $this->app->singleton(UnityStatsService::class, function ($app) {
            return new UnityStatsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ads-unity.php' => config_path('ads-unity.php'),
        ], 'ads-unity-config');

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
            UnityCallbackService::class,
            UnityStatsService::class,
        ];
    }
}
