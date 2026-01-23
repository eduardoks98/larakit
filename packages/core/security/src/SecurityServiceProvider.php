<?php

namespace Eduardoks98\Security;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\Security\Services\IpBlockingService;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/security.php',
            'security'
        );

        // Register services
        $this->app->singleton(IpBlockingService::class, function ($app) {
            return new IpBlockingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/security.php' => config_path('security.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $this->registerMiddleware();

        // Load helpers
        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('security.headers', \Eduardoks98\Security\Http\Middleware\SecurityHeaders::class);
        $router->aliasMiddleware('banned.ip', \Eduardoks98\Security\Http\Middleware\BannedIP::class);
    }
}
