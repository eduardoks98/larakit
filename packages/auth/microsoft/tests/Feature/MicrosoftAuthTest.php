<?php

namespace Eduardoks98\MicrosoftAuth\Tests\Feature;

use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Mockery;

class MicrosoftAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Eduardoks98\MicrosoftAuth\MicrosoftAuthServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('microsoft.client_id', 'test-client-id');
        $app['config']->set('microsoft.client_secret', 'test-client-secret');
        $app['config']->set('microsoft.tenant', 'common');
        $app['config']->set('microsoft.redirect_uri', 'http://localhost/api/auth/microsoft/callback');
    }

    public function test_redirect_returns_authorization_url()
    {
        $response = $this->getJson('/api/auth/microsoft/redirect');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'authorization_url',
                'state',
            ]);

        $this->assertStringContainsString('login.microsoftonline.com', $response->json('authorization_url'));
    }

    public function test_microsoft_user_model_exists()
    {
        $this->assertTrue(class_exists(MicrosoftUser::class));
    }

    public function test_microsoft_auth_service_exists()
    {
        $service = app(MicrosoftAuthService::class);
        $this->assertInstanceOf(MicrosoftAuthService::class, $service);
    }

    public function test_microsoft_user_can_check_token_expiration()
    {
        $microsoftUser = new MicrosoftUser([
            'microsoft_id' => 'test-123',
            'email' => 'test@example.com',
            'token_expires_at' => now()->addHour(),
        ]);

        $this->assertFalse($microsoftUser->isTokenExpired());

        $microsoftUser->token_expires_at = now()->subHour();
        $this->assertTrue($microsoftUser->isTokenExpired());
    }

    public function test_configuration_is_loaded()
    {
        $this->assertEquals('test-client-id', config('microsoft.client_id'));
        $this->assertEquals('common', config('microsoft.tenant'));
        $this->assertIsArray(config('microsoft.scopes'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
