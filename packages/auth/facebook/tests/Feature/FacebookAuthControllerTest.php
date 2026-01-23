<?php

namespace Eduardoks98\FacebookAuth\Tests\Feature;

use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Mockery;

class FacebookAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return ['Eduardoks98\FacebookAuth\FacebookAuthServiceProvider'];
    }

    /** @test */
    public function it_can_generate_facebook_authorization_url()
    {
        $response = $this->getJson('/api/facebook-auth/redirect');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'authorization_url',
                    'state',
                ],
                'message',
            ]);
    }

    /** @test */
    public function it_requires_code_parameter_for_callback()
    {
        $response = $this->getJson('/api/facebook-auth/callback');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid callback parameters',
            ]);
    }

    /** @test */
    public function it_can_get_authenticated_user_facebook_profile()
    {
        // Create a user with Facebook profile
        $user = $this->createUser();
        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/facebook-auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'facebook_id' => '1234567890',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_facebook_profile_not_found()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/facebook-auth/profile');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Facebook profile not found',
            ]);
    }

    /** @test */
    public function it_can_disconnect_facebook_account()
    {
        $user = $this->createUser();
        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/facebook-auth/disconnect');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Facebook account disconnected successfully',
            ]);

        $this->assertDatabaseMissing('facebook_users', [
            'id' => $facebookUser->id,
        ]);
    }

    protected function createUser()
    {
        $userModel = config('facebook-auth.user_model', 'App\\Models\\User');
        return $userModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
