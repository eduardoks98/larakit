<?php

namespace Eduardoks98\GoogleAuth\Tests;

use Eduardoks98\GoogleAuth\Models\GoogleUser;
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Eduardoks98\GoogleAuth\GoogleAuthServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('google-auth.client_id', 'test-client-id');
        $app['config']->set('google-auth.client_secret', 'test-client-secret');
        $app['config']->set('google-auth.redirect_uri', 'http://localhost/auth/google/callback');
    }

    /** @test */
    public function it_can_generate_authorization_url()
    {
        $service = new GoogleAuthService();
        $url = $service->getAuthorizationUrl();

        $this->assertStringContainsString('accounts.google.com/o/oauth2/auth', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
    }

    /** @test */
    public function it_can_create_google_user()
    {
        $googleUser = GoogleUser::create([
            'google_id' => '123456789',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_in' => 3600,
            'last_login_at' => now(),
        ]);

        $this->assertDatabaseHas('google_users', [
            'google_id' => '123456789',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_can_check_if_token_is_expired()
    {
        $googleUser = GoogleUser::create([
            'google_id' => '123456789',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'access_token' => 'test-access-token',
            'expires_in' => -3600, // Expired 1 hour ago
            'last_login_at' => now(),
        ]);

        $this->assertTrue($googleUser->isTokenExpired());
    }

    /** @test */
    public function it_can_update_token()
    {
        $googleUser = GoogleUser::create([
            'google_id' => '123456789',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'access_token' => 'old-token',
            'expires_in' => 3600,
            'last_login_at' => now(),
        ]);

        $googleUser->updateToken('new-token', 'new-refresh-token', 7200);

        $this->assertEquals('new-token', $googleUser->fresh()->access_token);
        $this->assertEquals('new-refresh-token', $googleUser->fresh()->refresh_token);
        $this->assertEquals(7200, $googleUser->fresh()->expires_in);
    }
}
