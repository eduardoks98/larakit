<?php

namespace Eduardoks98\WhatsAppConverx;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\WhatsAppConverx\Services\ConverxService;

/**
 * WhatsApp Converx Service Provider
 */
class WhatsAppConverxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/converx.php' => config_path('converx.php'),
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/converx.php', 'converx'
        );

        // Register ConverxService singleton
        $this->app->singleton(ConverxService::class, function ($app) {
            return new ConverxService();
        });

        // Register alias
        $this->app->alias(ConverxService::class, 'converx');
    }
}
