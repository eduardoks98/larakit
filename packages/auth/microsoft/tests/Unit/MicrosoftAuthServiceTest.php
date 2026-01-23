<?php

namespace Eduardoks98\MicrosoftAuth\Tests\Unit;

use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;
use Orchestra\Testbench\TestCase;

class MicrosoftAuthServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Eduardoks98\MicrosoftAuth\MicrosoftAuthServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('microsoft.client_id', 'test-client-id');
        $app['config']->set('microsoft.client_secret', 'test-client-secret');
        $app['config']->set('microsoft.tenant', 'common');
        $app['config']->set('microsoft.redirect_uri', 'http://localhost/api/auth/microsoft/callback');
    }

    public function test_service_can_generate_authorization_url()
    {
        $service = app(MicrosoftAuthService::class);
        $url = $service->getAuthorizationUrl();

        $this->assertStringContainsString('login.microsoftonline.com', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('response_type=code', $url);
    }

    public function test_service_can_get_state()
    {
        $service = app(MicrosoftAuthService::class);
        $service->getAuthorizationUrl();
        $state = $service->getState();

        $this->assertNotEmpty($state);
        $this->assertIsString($state);
    }
}
