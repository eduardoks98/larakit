<?php

namespace Eduardoks98\PaymentAbacatePay;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Http\Middleware\VerifyAbacatePayWebhook;

class AbacatePayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/abacatepay.php', 'abacatepay');

        // Register AbacatePayService as singleton
        $this->app->singleton(AbacatePayService::class, function ($app) {
            return new AbacatePayService(
                config('abacatepay.token')
            );
        });

        // Create alias for easier access
        $this->app->alias(AbacatePayService::class, 'abacatepay');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/abacatepay.php' => config_path('abacatepay.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('abacatepay.webhook', VerifyAbacatePayWebhook::class);

        // Load routes if available
        if (file_exists(__DIR__ . '/Http/routes.php')) {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add Artisan commands here if needed
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AbacatePayService::class,
            'abacatepay',
        ];
    }
}
