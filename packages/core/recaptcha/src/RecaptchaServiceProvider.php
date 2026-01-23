<?php

namespace Eduardoks98\Recaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Eduardoks98\Recaptcha\Services\RecaptchaService;
use Eduardoks98\Recaptcha\Services\SmartRecaptchaService;
use Eduardoks98\Recaptcha\Http\Middleware\VerifyRecaptcha;

class RecaptchaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/recaptcha.php', 'recaptcha');

        // Register services as singletons
        $this->app->singleton(RecaptchaService::class, function ($app) {
            return new RecaptchaService();
        });

        $this->app->singleton(SmartRecaptchaService::class, function ($app) {
            return new SmartRecaptchaService($app->make(RecaptchaService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/recaptcha.php' => config_path('recaptcha.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('recaptcha', VerifyRecaptcha::class);

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
            RecaptchaService::class,
            SmartRecaptchaService::class,
        ];
    }
}
