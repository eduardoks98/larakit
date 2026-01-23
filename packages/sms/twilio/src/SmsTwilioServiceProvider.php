<?php

namespace Eduardoks98\SmsTwilio;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Eduardoks98\SmsTwilio\Services\TwilioService;

/**
 * SMS Twilio Service Provider
 */
class SmsTwilioServiceProvider extends ServiceProvider
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
            __DIR__.'/../config/sms-twilio.php' => config_path('sms-twilio.php'),
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
            __DIR__.'/../config/sms-twilio.php', 'sms-twilio'
        );

        // Register TwilioService singleton
        $this->app->singleton(TwilioService::class, function ($app) {
            return new TwilioService();
        });

        // Register alias
        $this->app->alias(TwilioService::class, 'twilio');
    }

    /**
     * Register package routes
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/webhooks/twilio',
            'middleware' => ['api'],
        ], function () {
            // Status callback webhook
            Route::post('/status', [\Eduardoks98\SmsTwilio\Http\Controllers\TwilioWebhookController::class, 'handleStatusCallback'])
                ->name('twilio.webhook.status');

            // Incoming message webhook
            Route::post('/incoming', [\Eduardoks98\SmsTwilio\Http\Controllers\TwilioWebhookController::class, 'handleIncomingMessage'])
                ->name('twilio.webhook.incoming');
        });
    }
}
