<?php

namespace Eduardoks98\FacebookAuth\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Eduardoks98\FacebookAuth\FacebookAuthServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            FacebookAuthServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup Facebook Auth configuration
        $app['config']->set('facebook-auth.app_id', 'test-app-id');
        $app['config']->set('facebook-auth.app_secret', 'test-app-secret');
        $app['config']->set('facebook-auth.graph_api_version', 'v19.0');
        $app['config']->set('facebook-auth.redirect_uri', 'http://localhost/api/facebook-auth/callback');
    }
}
