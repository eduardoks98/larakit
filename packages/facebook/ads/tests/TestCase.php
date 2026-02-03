<?php

namespace Eduardoks98\AdsFacebook\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Eduardoks98\AdsFacebook\AdsFacebookServiceProvider;
use Eduardoks98\Monetization\MonetizationServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            MonetizationServiceProvider::class,
            AdsFacebookServiceProvider::class,
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

        $app['config']->set('ads-facebook.rewards.enabled', true);
        $app['config']->set('ads-facebook.rewards.require_auth', false);
    }
}
