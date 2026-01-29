<?php

namespace Eduardoks98\DiscordAuth;

use Eduardoks98\DiscordAuth\Services\DiscordAuthService;
use Illuminate\Support\ServiceProvider;

class DiscordAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package config with application config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/discord-auth.php',
            'discord-auth'
        );

        // Register DiscordAuthService as singleton
        $this->app->singleton(DiscordAuthService::class, function ($app) {
            return new DiscordAuthService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/discord-auth.php' => config_path('discord-auth.php'),
        ], 'discord-auth-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'discord-auth-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
