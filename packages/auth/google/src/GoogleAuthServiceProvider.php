<?php

namespace Eduardoks98\GoogleAuth;

use Eduardoks98\GoogleAuth\Services\GoogleAuthService;
use Illuminate\Support\ServiceProvider;

class GoogleAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package config with application config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/google-auth.php',
            'google-auth'
        );

        // Register GoogleAuthService as singleton
        $this->app->singleton(GoogleAuthService::class, function ($app) {
            return new GoogleAuthService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/google-auth.php' => config_path('google-auth.php'),
        ], 'google-auth-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'google-auth-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
