<?php

namespace Eduardoks98\FacebookAuth;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;

/**
 * Facebook Auth Service Provider
 *
 * Registers Facebook authentication services and publishes configuration
 */
class FacebookAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/facebook-auth.php', 'facebook-auth');

        // Register FacebookAuthService as singleton
        $this->app->singleton(FacebookAuthService::class, function ($app) {
            return new FacebookAuthService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/facebook-auth.php' => config_path('facebook-auth.php'),
        ], 'facebook-auth-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'facebook-auth-migrations');

        // Register routes
        $this->registerRoutes();
    }

    /**
     * Register package routes.
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
            FacebookAuthService::class,
        ];
    }
}
