<?php

namespace Eduardoks98\AdsApplovin\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\AdsApplovin\AdsApplovinServiceProvider;
use Eduardoks98\Monetization\MonetizationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonetizationServiceProvider::class,
            AdsApplovinServiceProvider::class,
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

        $app['config']->set('ads-applovin.s2s.enabled', true);
        $app['config']->set('ads-applovin.s2s.event_key', 'test_event_key');
    }
}
