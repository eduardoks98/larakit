<?php

namespace Eduardoks98\SmsComtele;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Eduardoks98\SmsComtele\Services\ComteleService;

/**
 * SMS Comtele Service Provider
 */
class SmsComteleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/sms-comtele.php' => config_path('sms-comtele.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register routes
        $this->registerRoutes();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/sms-comtele.php', 'sms-comtele'
        );

        // Register ComteleService singleton
        $this->app->singleton(ComteleService::class, function ($app) {
            return new ComteleService();
        });

        // Register alias
        $this->app->alias(ComteleService::class, 'comtele');
    }

    /**
     * Register package routes
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/webhooks/comtele',
            'middleware' => ['api'],
        ], function () {
            // Status callback webhook
            Route::post('/status', [\Eduardoks98\SmsComtele\Http\Controllers\ComteleWebhookController::class, 'handleStatusCallback'])
                ->name('comtele.webhook.status');

            // Reply callback webhook
            Route::post('/reply', [\Eduardoks98\SmsComtele\Http\Controllers\ComteleWebhookController::class, 'handleReplyCallback'])
                ->name('comtele.webhook.reply');
        });
    }
}
