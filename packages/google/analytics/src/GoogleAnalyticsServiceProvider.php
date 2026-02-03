<?php

namespace Eduardoks98\AnalyticsGoogle;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Eduardoks98\AnalyticsGoogle\Services\GoogleAnalyticsService;

class GoogleAnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/google-analytics.php', 'google-analytics');

        $this->app->singleton(GoogleAnalyticsService::class, function ($app) {
            return new GoogleAnalyticsService();
        });

        $this->app->alias(GoogleAnalyticsService::class, 'google-analytics');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'google-analytics');

        // Register Blade components
        Blade::component('google-analytics::components.gtag', 'ga-gtag');
        Blade::component('google-analytics::components.event', 'ga-event');

        $this->publishes([
            __DIR__ . '/../config/google-analytics.php' => config_path('google-analytics.php'),
        ], 'google-analytics-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/google-analytics'),
        ], 'google-analytics-views');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            GoogleAnalyticsService::class,
            'google-analytics',
        ];
    }
}
