<?php

namespace Eduardoks98\Auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\Auth\Services\TokenService;
use Eduardoks98\Auth\Services\SessionService;
use Eduardoks98\Auth\Http\Middleware\CheckTokenAbilities;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/auth.php', 'auth');

        // Register services as singletons
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService();
        });

        $this->app->singleton(SessionService::class, function ($app) {
            return new SessionService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/auth.php' => config_path('auth-package.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('abilities', CheckTokenAbilities::class);

        // Load helper functions
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
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
            TokenService::class,
            SessionService::class,
        ];
    }
}
