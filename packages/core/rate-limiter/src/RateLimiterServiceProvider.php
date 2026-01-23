<?php

namespace Eduardoks98\RateLimiter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\RateLimiter\Services\ThrottleService;
use Eduardoks98\RateLimiter\Services\IpWhitelistService;
use Eduardoks98\RateLimiter\Http\Middleware\GenericThrottle;
use Eduardoks98\RateLimiter\Http\Middleware\LoginThrottle;

class RateLimiterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/rate-limiter.php', 'rate-limiter');

        // Register services as singletons
        $this->app->singleton(ThrottleService::class, function ($app) {
            return new ThrottleService($app->make(\Eduardoks98\Security\Services\IpBlockingService::class));
        });

        $this->app->singleton(IpWhitelistService::class, function ($app) {
            return new IpWhitelistService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/rate-limiter.php' => config_path('rate-limiter.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('throttle.generic', GenericThrottle::class);
        $router->aliasMiddleware('throttle.login', LoginThrottle::class);

        // Load helper functions
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ThrottleService::class,
            IpWhitelistService::class,
        ];
    }
}
