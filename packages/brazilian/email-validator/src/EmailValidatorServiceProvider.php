<?php

namespace Eduardoks98\EmailValidator;

use Illuminate\Support\ServiceProvider;
use Eduardoks98\EmailValidator\Services\EmailValidatorService;

/**
 * Email Validator Service Provider
 */
class EmailValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/email-validator.php' => config_path('email-validator.php'),
        ], 'config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/email-validator.php',
            'email-validator'
        );

        // Register EmailValidatorService singleton
        $this->app->singleton(EmailValidatorService::class, function ($app) {
            return new EmailValidatorService();
        });

        // Register alias
        $this->app->alias(EmailValidatorService::class, 'email-validator');
    }
}
