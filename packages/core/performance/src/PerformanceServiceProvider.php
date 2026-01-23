<?php

namespace Eduardoks98\Performance;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Database\Eloquent\Model;
use Eduardoks98\Performance\Http\Middleware\PerformanceMonitor;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/performance.php', 'performance');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/performance.php' => config_path('performance.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('performance.monitor', PerformanceMonitor::class);

        if (file_exists(__DIR__ . '/helpers.php')) {
            require_once __DIR__ . '/helpers.php';
        }

        // Enable N+1 query detection in development
        if (!$this->app->isProduction()) {
            Model::preventLazyLoading();
        }
    }
}
