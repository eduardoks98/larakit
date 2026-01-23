<?php

namespace Eduardoks98\PaymentMercadoPago;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;
use Eduardoks98\PaymentMercadoPago\Services\WebhookService;
use Eduardoks98\PaymentMercadoPago\Http\Middleware\VerifyMercadoPagoWebhook;

class PaymentMercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/payment-mercadopago.php',
            'payment-mercadopago'
        );

        // Register services as singletons
        $this->app->singleton(MercadoPagoService::class, function ($app) {
            return new MercadoPagoService();
        });

        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService($app->make(MercadoPagoService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/payment-mercadopago.php' => config_path('payment-mercadopago.php'),
        ], 'mercadopago-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mercadopago-migrations');

        // Register routes
        $this->registerRoutes();

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('mercadopago.webhook', VerifyMercadoPagoWebhook::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add Artisan commands here if needed
            ]);
        }
    }

    /**
     * Register package routes
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
            MercadoPagoService::class,
            WebhookService::class,
        ];
    }
}
