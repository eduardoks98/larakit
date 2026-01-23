<?php

namespace Eduardoks98\Banking;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\Banking\Services\PixService;
use Eduardoks98\Banking\Services\BankService;
use Eduardoks98\Banking\Services\BoletoService;

/**
 * Banking Service Provider
 */
class BankingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/banking.php' => config_path('banking.php'),
        ], 'config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/banking.php',
            'banking'
        );

        // Register PixService singleton
        $this->app->singleton(PixService::class, function ($app) {
            return new PixService();
        });

        // Register BankService singleton
        $this->app->singleton(BankService::class, function ($app) {
            return new BankService();
        });

        // Register BoletoService singleton
        $this->app->singleton(BoletoService::class, function ($app) {
            return new BoletoService();
        });

        // Register aliases
        $this->app->alias(PixService::class, 'pix');
        $this->app->alias(BankService::class, 'bank');
        $this->app->alias(BoletoService::class, 'boleto');
    }
}
