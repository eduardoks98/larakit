<?php

namespace Eduardoks98\WhatsAppOfficial;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Eduardoks98\WhatsAppOfficial\Services\WhatsAppService;

/**
 * WhatsApp Official Service Provider
 */
class WhatsAppOfficialServiceProvider extends ServiceProvider
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
            __DIR__.'/../config/whatsapp.php' => config_path('whatsapp.php'),
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
            __DIR__.'/../config/whatsapp.php', 'whatsapp'
        );

        // Register WhatsAppService singleton
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService();
        });

        // Register alias
        $this->app->alias(WhatsAppService::class, 'whatsapp');
    }

    /**
     * Register package routes
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/webhooks/whatsapp',
            'middleware' => ['api'],
        ], function () {
            // Webhook verification (GET)
            Route::get('/', [\Eduardoks98\WhatsAppOfficial\Http\Controllers\WhatsAppWebhookController::class, 'verify'])
                ->name('whatsapp.webhook.verify');

            // Webhook handler (POST)
            Route::post('/', [\Eduardoks98\WhatsAppOfficial\Http\Controllers\WhatsAppWebhookController::class, 'handleWebhook'])
                ->name('whatsapp.webhook.handle');
        });
    }
}
