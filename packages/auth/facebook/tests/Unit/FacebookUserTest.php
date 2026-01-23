<?php

namespace Eduardoks98\FacebookAuth\Tests\Unit;

use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class FacebookUserTest extends TestCase
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
    public function it_can_find_facebook_user_by_facebook_id()
    {
        $user = $this->createUser();
        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $found = FacebookUser::findByFacebookId('1234567890');

        $this->assertNotNull($found);
        $this->assertEquals($facebookUser->id, $found->id);
    }

    /** @test */
    public function it_returns_null_when_facebook_user_not_found()
    {
        $found = FacebookUser::findByFacebookId('nonexistent');

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_create_or_update_facebook_user()
    {
        $user = $this->createUser();

        // Create
        $facebookUser = FacebookUser::createOrUpdate([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $this->assertNotNull($facebookUser);
        $this->assertEquals('1234567890', $facebookUser->facebook_id);
        $this->assertEquals('Test User', $facebookUser->name);

        // Update
        $updated = FacebookUser::createOrUpdate([
            'facebook_id' => '1234567890',
            'name' => 'Updated Name',
        ]);

        $this->assertEquals($facebookUser->id, $updated->id);
        $this->assertEquals('Updated Name', $updated->name);
    }

    /** @test */
    public function it_hides_access_token_in_serialization()
    {
        $user = $this->createUser();
        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'access_token' => 'secret-token',
        ]);

        $array = $facebookUser->toArray();

        $this->assertArrayNotHasKey('access_token', $array);
    }

    /** @test */
    public function it_casts_metadata_to_array()
    {
        $user = $this->createUser();
        $metadata = ['key' => 'value', 'expires_at' => 123456];

        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '1234567890',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'metadata' => $metadata,
        ]);

        $this->assertIsArray($facebookUser->metadata);
        $this->assertEquals($metadata, $facebookUser->metadata);
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
