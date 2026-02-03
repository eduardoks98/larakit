<?php

namespace Eduardoks98\AdsGoogle\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\AdsGoogle\AdsGoogleServiceProvider;
use Eduardoks98\Monetization\MonetizationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonetizationServiceProvider::class,
            AdsGoogleServiceProvider::class,
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

        $app['config']->set('ads-google.ssv.enabled', true);
    }
}
