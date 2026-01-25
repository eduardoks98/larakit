<?php

namespace Eduardoks98\Monetization\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\Monetization\MonetizationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonetizationServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('monetization.currency.name', 'coins');
        $app['config']->set('monetization.currency.initial_balance', 0);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
