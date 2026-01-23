<?php

namespace Eduardoks98\PaymentAbacatePay\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Eduardoks98\PaymentAbacatePay\AbacatePayServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load migrations if needed
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AbacatePayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up test configuration
        config()->set('abacatepay.token', getenv('ABACATEPAY_TOKEN'));
        config()->set('abacatepay.webhook_secret', getenv('ABACATEPAY_WEBHOOK_SECRET'));
        config()->set('abacatepay.store_billings', false); // Disable DB storage in tests
    }
}
