<?php

namespace Eduardoks98\PaymentStripe;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\PaymentStripe\Services\StripeService;
use Eduardoks98\PaymentStripe\Services\CustomerService;
use Eduardoks98\PaymentStripe\Services\SubscriptionService;
use Eduardoks98\PaymentStripe\Http\Middleware\VerifyStripeWebhook;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/stripe.php', 'stripe');

        // Register services as singletons
        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService();
        });

        $this->app->singleton(CustomerService::class, function ($app) {
            return new CustomerService();
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe-config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'stripe-migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('stripe.webhook', VerifyStripeWebhook::class);

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
            StripeService::class,
            CustomerService::class,
            SubscriptionService::class,
        ];
    }
}
