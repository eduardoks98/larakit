<?php

namespace Eduardoks98\Health;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Eduardoks98\Health\Http\Controllers\HealthController;

class HealthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/health.php', 'health');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/health.php' => config_path('health.php'),
        ], 'config');

        // Register health check routes
        if (config('health.enabled', true)) {
            $this->registerRoutes();
        }
    }

    protected function registerRoutes(): void
    {
        Route::prefix(config('health.routes.prefix', 'health'))
            ->middleware(config('health.routes.middleware', []))
            ->group(function () {
                Route::get('/', [HealthController::class, 'index']);
                Route::get('/db', [HealthController::class, 'database']);
                Route::get('/cache', [HealthController::class, 'cache']);
                Route::get('/queue', [HealthController::class, 'queue']);
                Route::get('/full', [HealthController::class, 'full']);
            });
    }
}
