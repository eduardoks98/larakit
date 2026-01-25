<?php

namespace Eduardoks98\AdsAdsense\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\AdsAdsense\AdsenseServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AdsenseServiceProvider::class,
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

        $app['config']->set('adsense.publisher_id', 'ca-pub-test123');
        $app['config']->set('adsense.enabled', true);
        $app['config']->set('adsense.cache.enabled', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
