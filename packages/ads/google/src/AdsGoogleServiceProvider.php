<?php

namespace Eduardoks98\AdsGoogle;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\AdsGoogle\Services\AdMobSsvService;
use Eduardoks98\AdsGoogle\Http\Middleware\VerifyAdMobSignature;

class AdsGoogleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ads-google.php', 'ads-google');

        $this->app->singleton(AdMobSsvService::class, function ($app) {
            return new AdMobSsvService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ads-google.php' => config_path('ads-google.php'),
        ], 'ads-google-config');

        $this->registerMiddleware();
        $this->registerRoutes();
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('admob.verify', VerifyAdMobSignature::class);
    }

    /**
     * Register routes.
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
            AdMobSsvService::class,
        ];
    }
}
