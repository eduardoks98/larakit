<?php

namespace Eduardoks98\AdsUnity\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\AdsUnity\AdsUnityServiceProvider;
use Eduardoks98\Monetization\MonetizationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonetizationServiceProvider::class,
            AdsUnityServiceProvider::class,
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

        $app['config']->set('ads-unity.s2s.enabled', true);
        $app['config']->set('ads-unity.s2s.secret_key', 'test_secret_key');
    }
}
