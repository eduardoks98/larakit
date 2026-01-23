<?php

namespace Eduardoks98\BaseApi;

use Illuminate\Support\ServiceProvider;

class BaseApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/base-api.php',
            'base-api'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/base-api.php' => config_path('base-api.php'),
        ], 'config');

        // Register middleware
        $this->registerMiddleware();

        // Load helpers
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('force.json', \Eduardoks98\BaseApi\Http\Middleware\ForceJsonResponse::class);
        $router->aliasMiddleware('api.headers', \Eduardoks98\BaseApi\Http\Middleware\SetApiHeaders::class);
    }
}
