# Usage Examples

This document provides practical examples of using the Facebook Auth package.

## Basic Usage

### 1. Simple Login Flow

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected FacebookAuthService $facebookAuth
    ) {}

    public function redirectToFacebook()
    {
        $authUrl = $this->facebookAuth->getAuthorizationUrl();
        $state = $this->facebookAuth->getState();

        // Store state in session for verification
        session(['facebook_state' => $state]);

        return redirect($authUrl);
    }

    public function handleFacebookCallback(Request $request)
    {
        $code = $request->input('code');
        $state = $request->input('state');

        // Verify state (CSRF protection)
        if ($state !== session('facebook_state')) {
            abort(403, 'Invalid state parameter');
        }

        // Handle authentication
        $result = $this->facebookAuth->handleCallback($code, $state);

        // Log the user in
        auth()->login($result['user']);

        return redirect('/dashboard');
    }
}
```

### 2. API-Only Authentication

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;
use Illuminate\Http\Request;

class ApiAuthController extends Controller
{
    public function __construct(
        protected FacebookAuthService $facebookAuth
    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $result = $this->facebookAuth->handleCallback($request->code);

            return response()->json([
                'access_token' => $result['token'],
                'user' => $result['user'],
                'facebook_profile' => $result['facebook_user'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
```

## Advanced Usage

### 3. Custom Scopes

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;

class CustomAuthController extends Controller
{
    public function __construct(
        protected FacebookAuthService $facebookAuth
    ) {}

    public function redirectWithCustomScopes()
    {
        // Request additional permissions
        $authUrl = $this->facebookAuth->getAuthorizationUrl([
            'scope' => [
                'email',
                'public_profile',
                'user_birthday',
                'user_location',
            ],
        ]);

        return redirect($authUrl);
    }
}
```

### 4. Linking Facebook to Existing Account

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;
use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        protected FacebookAuthService $facebookAuth
    ) {}

    public function linkFacebook(Request $request)
    {
        $code = $request->input('code');
        $user = $request->user(); // Already authenticated user

        try {
            // Get Facebook access token and user data
            $accessToken = $this->facebookAuth->getAccessToken($code);
            $facebookUser = $this->facebookAuth->getFacebookUser($accessToken);

            // Check if Facebook account is already linked
            $existing = FacebookUser::findByFacebookId($facebookUser->getId());
            if ($existing && $existing->user_id !== $user->id) {
                return response()->json([
                    'error' => 'This Facebook account is already linked to another user',
                ], 400);
            }

            // Link Facebook account
            FacebookUser::createOrUpdate([
                'user_id' => $user->id,
                'facebook_id' => $facebookUser->getId(),
                'email' => $facebookUser->getEmail(),
                'name' => $facebookUser->getName(),
                'avatar_url' => $facebookUser->getPictureUrl(),
            ]);

            return response()->json([
                'message' => 'Facebook account linked successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to link Facebook account',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### 5. Using Facebook Profile Data

```php
use Eduardoks98\FacebookAuth\Models\FacebookUser;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $facebookUser = $user->facebookUser;

        if (!$facebookUser) {
            return response()->json([
                'message' => 'No Facebook account linked',
            ], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'facebook' => [
                'id' => $facebookUser->facebook_id,
                'name' => $facebookUser->name,
                'email' => $facebookUser->email,
                'avatar' => $facebookUser->avatar_url,
                'linked_at' => $facebookUser->created_at,
            ],
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();
        $facebookUser = $user->facebookUser;

        if ($facebookUser && $facebookUser->avatar_url) {
            // Use Facebook avatar for user profile
            $user->update([
                'avatar_url' => $facebookUser->avatar_url,
            ]);

            return response()->json([
                'message' => 'Avatar updated from Facebook profile',
                'avatar_url' => $facebookUser->avatar_url,
            ]);
        }

        return response()->json([
            'error' => 'No Facebook avatar available',
        ], 404);
    }
}
```

### 6. Middleware for Facebook Users

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireFacebookAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->facebookUser) {
            return response()->json([
                'error' => 'Facebook account required',
                'message' => 'Please link your Facebook account to access this feature',
            ], 403);
        }

        return $next($request);
    }
}
```

Usage in routes:

```php
Route::middleware(['auth:sanctum', 'require-facebook'])->group(function () {
    Route::get('/facebook-exclusive-feature', [FeatureController::class, 'index']);
});
```

### 7. Event Listeners

```php
namespace App\Listeners;

use Eduardoks98\FacebookAuth\Events\FacebookUserLinked;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    public function handle(FacebookUserLinked $event)
    {
        $user = $event->user;
        $facebookUser = $event->facebookUser;

        Log::info('New Facebook user linked', [
            'user_id' => $user->id,
            'facebook_id' => $facebookUser->facebook_id,
        ]);

        // Send welcome email
        // Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}
```

### 8. Custom User Model

Create a trait for your User model:

```php
namespace App\Models\Traits;

use Eduardoks98\FacebookAuth\Models\FacebookUser;

trait HasFacebookAccount
{
    /**
     * Get the user's Facebook account.
     */
    public function facebookUser()
    {
        return $this->hasOne(FacebookUser::class);
    }

    /**
     * Check if user has linked Facebook account.
     */
    public function hasFacebookAccount(): bool
    {
        return $this->facebookUser()->exists();
    }

    /**
     * Get Facebook avatar URL.
     */
    public function getFacebookAvatar(): ?string
    {
        return $this->facebookUser?->avatar_url;
    }

    /**
     * Get Facebook ID.
     */
    public function getFacebookId(): ?string
    {
        return $this->facebookUser?->facebook_id;
    }
}
```

Use in your User model:

```php
namespace App\Models;

use App\Models\Traits\HasFacebookAccount;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFacebookAccount;

    // ... rest of your User model
}
```

### 9. Testing

```php
namespace Tests\Feature;

use App\Models\User;
use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_facebook_profile()
    {
        $user = User::factory()->create();
        $facebookUser = FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '123456789',
            'email' => 'test@facebook.com',
            'name' => 'Test User',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/facebook-auth/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'facebook_id' => '123456789',
                    'name' => 'Test User',
                ],
            ]);
    }

    /** @test */
    public function user_can_disconnect_facebook_account()
    {
        $user = User::factory()->create();
        FacebookUser::create([
            'user_id' => $user->id,
            'facebook_id' => '123456789',
            'email' => 'test@facebook.com',
            'name' => 'Test User',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/facebook-auth/disconnect');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('facebook_users', [
            'user_id' => $user->id,
        ]);
    }
}
```

### 10. Queue Jobs

```php
namespace App\Jobs;

use Eduardoks98\FacebookAuth\Models\FacebookUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SyncFacebookAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected FacebookUser $facebookUser
    ) {}

    public function handle()
    {
        // Download and store Facebook avatar locally
        $avatarUrl = $this->facebookUser->avatar_url;

        if (!$avatarUrl) {
            return;
        }

        try {
            $response = Http::get($avatarUrl);

            if ($response->successful()) {
                $filename = "avatars/{$this->facebookUser->user_id}.jpg";
                Storage::put($filename, $response->body());

                // Update user avatar
                $this->facebookUser->user->update([
                    'avatar' => $filename,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync Facebook avatar', [
                'user_id' => $this->facebookUser->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

Dispatch the job:

```php
use App\Jobs\SyncFacebookAvatar;

// After Facebook authentication
SyncFacebookAvatar::dispatch($result['facebook_user']);
```

## Configuration Examples

### Environment-Specific Configurations

#### Development (.env.development)

```env
FACEBOOK_APP_ID=dev-app-id
FACEBOOK_APP_SECRET=dev-app-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/api/facebook-auth/callback
FACEBOOK_FRONTEND_REDIRECT_URL=http://localhost:3000/auth/callback
FACEBOOK_AUTH_LOGGING_ENABLED=true
```

#### Production (.env.production)

```env
FACEBOOK_APP_ID=prod-app-id
FACEBOOK_APP_SECRET=prod-app-secret
FACEBOOK_REDIRECT_URI=https://api.yourdomain.com/api/facebook-auth/callback
FACEBOOK_FRONTEND_REDIRECT_URL=https://yourdomain.com/auth/callback
FACEBOOK_AUTH_LOGGING_ENABLED=false
```

### Custom Configuration

```php
// config/facebook-auth.php

return [
    // Override default scopes
    'scopes' => [
        'email',
        'public_profile',
        'user_birthday',
    ],

    // Custom user fields
    'user_fields' => [
        'id',
        'name',
        'email',
        'birthday',
        'location',
        'picture.width(500).height(500)',
    ],

    // Custom token expiration (in minutes)
    'token' => [
        'name' => 'facebook-token',
        'abilities' => ['read', 'write'],
        'expires_in' => 60 * 24 * 7, // 7 days
    ],
];
```

## Best Practices

1. **Always verify the state parameter** for CSRF protection
2. **Store tokens securely** - never expose them in client-side code
3. **Handle errors gracefully** - provide user-friendly error messages
4. **Use HTTPS** in production environments
5. **Keep Facebook SDK updated** - check for security updates
6. **Log authentication events** for security monitoring
7. **Implement rate limiting** on authentication endpoints
8. **Validate Facebook data** before storing in database
9. **Handle edge cases** - missing email, duplicate accounts, etc.
10. **Test thoroughly** - write tests for all authentication flows
