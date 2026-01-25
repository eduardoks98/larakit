<?php

namespace Eduardoks98\Monetization;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Services\CurrencyService;
use Eduardoks98\Monetization\Services\AnalyticsService;

class MonetizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/monetization.php', 'monetization');

        $this->app->singleton(RewardService::class, function ($app) {
            return new RewardService();
        });

        $this->app->singleton(CurrencyService::class, function ($app) {
            return new CurrencyService();
        });

        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/monetization.php' => config_path('monetization.php'),
        ], 'monetization-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'monetization-migrations');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            RewardService::class,
            CurrencyService::class,
            AnalyticsService::class,
        ];
    }
}
