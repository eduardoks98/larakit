<?php

namespace Eduardoks98\AdsFacebook;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\AdsFacebook\Services\AudienceNetworkService;

class AdsFacebookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ads-facebook.php', 'ads-facebook');

        $this->app->singleton(AudienceNetworkService::class, function ($app) {
            return new AudienceNetworkService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ads-facebook.php' => config_path('ads-facebook.php'),
        ], 'ads-facebook-config');

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
            AudienceNetworkService::class,
        ];
    }
}
