# Integration Guide

Complete guide for integrating Microsoft Auth with existing Laravel applications.

## Table of Contents

- [Installation](#installation)
- [Existing User Integration](#existing-user-integration)
- [Frontend Integration](#frontend-integration)
- [Custom User Model](#custom-user-model)
- [Multi-Authentication](#multi-authentication)
- [API Integration](#api-integration)

## Installation

### 1. Install Package

```bash
composer require eduardoks98/microsoft-auth
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=microsoft-config
php artisan vendor:publish --tag=microsoft-migrations
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Configure Environment

Add to `.env`:

```env
MICROSOFT_CLIENT_ID=your_azure_client_id
MICROSOFT_CLIENT_SECRET=your_azure_client_secret
MICROSOFT_TENANT=common
MICROSOFT_REDIRECT_URI=${APP_URL}/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=${FRONTEND_URL}/auth/callback
```

## Existing User Integration

### Scenario 1: Allow Existing Users to Link Microsoft Account

```php
// routes/api.php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

Route::middleware(['auth:sanctum'])->group(function () {
    // Check if user has Microsoft account linked
    Route::get('/user/microsoft-status', function (Request $request) {
        $user = $request->user();
        $hasMicrosoft = $user->microsoftUser !== null;

        return response()->json([
            'has_microsoft_account' => $hasMicrosoft,
            'microsoft_account' => $hasMicrosoft ? $user->microsoftUser : null,
        ]);
    });

    // Initiate linking process
    Route::get('/user/link-microsoft', function () {
        // Save state that indicates this is a linking operation
        session(['microsoft_action' => 'link', 'user_id' => auth()->id()]);
        return redirect('/api/auth/microsoft/redirect');
    });
});
```

### Scenario 2: Prevent Duplicate Accounts

```php
// app/Providers/AppServiceProvider.php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

public function boot()
{
    // Hook into Microsoft user creation
    MicrosoftUser::creating(function ($microsoftUser) {
        // Check if a user with this email already exists
        $existingUser = \App\Models\User::where('email', $microsoftUser->email)->first();

        if ($existingUser) {
            // Link to existing user instead of creating new one
            $microsoftUser->user_id = $existingUser->id;

            // Optionally update user data
            $existingUser->update([
                'email_verified_at' => $existingUser->email_verified_at ?? now(),
            ]);
        }
    });
}
```

### Scenario 3: Custom User Creation Logic

```php
// config/microsoft.php
'auto_create_user' => false, // Disable auto-creation

// Then handle manually in your controller
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

Route::get('/auth/microsoft/custom-callback', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);

    // Get Microsoft user data
    $code = $request->input('code');
    $token = $microsoftAuth->getAccessToken($code);
    $userData = $microsoftAuth->getUserInfo($token);
    $microsoftUser = $microsoftAuth->findOrCreateMicrosoftUser($userData, $token);

    // Custom user logic
    $user = \App\Models\User::where('email', $microsoftUser->email)->first();

    if (!$user) {
        // Check if user is allowed to register
        $domain = explode('@', $microsoftUser->email)[1];
        if (!in_array($domain, ['yourcompany.com', 'partner.com'])) {
            return response()->json(['error' => 'Unauthorized domain'], 403);
        }

        // Create user with custom fields
        $user = \App\Models\User::create([
            'name' => $microsoftUser->name,
            'email' => $microsoftUser->email,
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
            'department' => $microsoftUser->job_title,
            'organization_id' => $this->determineOrganization($microsoftUser->tenant_id),
        ]);
    }

    $microsoftUser->update(['user_id' => $user->id]);

    // Create token
    $token = $user->createToken('microsoft-oauth')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user]);
});
```

## Frontend Integration

### React + TypeScript Example

```typescript
// src/services/authService.ts
export const microsoftAuthService = {
  async loginWithMicrosoft() {
    // Redirect to Microsoft OAuth
    window.location.href = '/api/auth/microsoft/redirect';
  },

  async handleCallback() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    const error = params.get('error');

    if (error) {
      throw new Error(params.get('message') || 'Authentication failed');
    }

    if (token) {
      localStorage.setItem('auth_token', token);
      return token;
    }

    throw new Error('No token received');
  },

  async getCurrentUser() {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to fetch user');
    }

    return response.json();
  },

  async linkMicrosoftAccount() {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/link-microsoft', {
      headers: {
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();
    window.location.href = data.authorization_url;
  },

  async unlinkMicrosoftAccount() {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/auth/microsoft/unlink', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    return response.json();
  },
};

// src/components/LoginPage.tsx
import { microsoftAuthService } from '../services/authService';

export default function LoginPage() {
  return (
    <div>
      <h1>Login</h1>
      <button onClick={() => microsoftAuthService.loginWithMicrosoft()}>
        <img src="/microsoft-logo.svg" alt="Microsoft" />
        Sign in with Microsoft
      </button>
    </div>
  );
}

// src/components/AuthCallback.tsx
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { microsoftAuthService } from '../services/authService';

export default function AuthCallback() {
  const navigate = useNavigate();

  useEffect(() => {
    microsoftAuthService.handleCallback()
      .then(() => navigate('/dashboard'))
      .catch((error) => {
        console.error(error);
        navigate('/login?error=' + encodeURIComponent(error.message));
      });
  }, [navigate]);

  return <div>Processing authentication...</div>;
}
```

### Vue 3 + Composition API Example

```vue
<!-- src/composables/useMicrosoftAuth.ts -->
<script setup lang="ts">
import { ref } from 'vue';

export function useMicrosoftAuth() {
  const loading = ref(false);
  const error = ref<string | null>(null);

  const loginWithMicrosoft = () => {
    window.location.href = '/api/auth/microsoft/redirect';
  };

  const handleCallback = async () => {
    loading.value = true;
    error.value = null;

    try {
      const params = new URLSearchParams(window.location.search);
      const token = params.get('token');
      const errorParam = params.get('error');

      if (errorParam) {
        throw new Error(params.get('message') || 'Authentication failed');
      }

      if (token) {
        localStorage.setItem('auth_token', token);
        return token;
      }

      throw new Error('No token received');
    } catch (e: any) {
      error.value = e.message;
      throw e;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    loginWithMicrosoft,
    handleCallback,
  };
}
</script>

<!-- src/views/LoginView.vue -->
<template>
  <div>
    <h1>Login</h1>
    <button @click="loginWithMicrosoft">
      <img src="/microsoft-logo.svg" alt="Microsoft" />
      Sign in with Microsoft
    </button>
  </div>
</template>

<script setup lang="ts">
import { useMicrosoftAuth } from '@/composables/useMicrosoftAuth';

const { loginWithMicrosoft } = useMicrosoftAuth();
</script>
```

## Custom User Model

If you're using a custom user model:

```php
// config/microsoft.php
'user_model' => App\Models\CustomUser::class,

// app/Models/CustomUser.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class CustomUser extends Authenticatable
{
    use HasApiTokens;

    // The microsoftUser relationship is automatically added by the package
    // You can customize it if needed:
    public function microsoftAccount()
    {
        return $this->hasOne(
            \Eduardoks98\MicrosoftAuth\Models\MicrosoftUser::class,
            'user_id'
        );
    }
}
```

## Multi-Authentication

Support both email/password and Microsoft authentication:

```php
// routes/api.php
Route::prefix('auth')->group(function () {
    // Traditional email/password
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Microsoft OAuth (automatically registered by package)
    // GET /auth/microsoft/redirect
    // GET /auth/microsoft/callback

    // Common endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Link/unlink Microsoft account
        Route::post('link-microsoft', function (Request $request) {
            session(['microsoft_linking' => true, 'linking_user_id' => auth()->id()]);
            return response()->json([
                'redirect_url' => '/api/auth/microsoft/redirect'
            ]);
        });

        Route::post('unlink-microsoft', [MicrosoftAuthController::class, 'unlink']);
    });
});

// app/Http/Controllers/AuthController.php
class AuthController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'microsoft_account' => $user->microsoftUser,
            'auth_methods' => [
                'email' => true, // User has password
                'microsoft' => $user->microsoftUser !== null,
            ],
        ]);
    }
}
```

## API Integration

### Protected Routes with Microsoft Token

```php
Route::middleware(['auth:sanctum', 'microsoft.token'])->group(function () {
    // These routes require both Sanctum auth and valid Microsoft token

    Route::get('/microsoft/emails', function (Request $request) {
        $microsoftUser = $request->attributes->get('microsoft_user');
        $microsoftAuth = app(\Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService::class);

        $emails = $microsoftAuth->graphApiRequest(
            $microsoftUser->access_token,
            'me/messages?$top=10',
            'GET'
        );

        return response()->json($emails);
    });

    Route::get('/microsoft/calendar', function (Request $request) {
        $microsoftUser = $request->attributes->get('microsoft_user');
        $microsoftAuth = app(\Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService::class);

        $events = $microsoftAuth->graphApiRequest(
            $microsoftUser->access_token,
            'me/calendar/events',
            'GET'
        );

        return response()->json($events);
    });
});
```

### Optional Microsoft Integration

```php
Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    $user = $request->user();
    $data = ['user' => $user];

    // Include Microsoft data if linked
    if ($user->microsoftUser && $user->microsoftUser->hasValidToken()) {
        $data['microsoft_account'] = [
            'email' => $user->microsoftUser->email,
            'upn' => $user->microsoftUser->user_principal_name,
            'job_title' => $user->microsoftUser->job_title,
            'office' => $user->microsoftUser->office_location,
        ];
    }

    return response()->json($data);
});
```

## Advanced Integration

### SSO for Organization

```php
// Force users from specific domain to use Microsoft auth
public function login(Request $request)
{
    $email = $request->input('email');
    $domain = explode('@', $email)[1];

    // Force Microsoft SSO for company domain
    if ($domain === 'yourcompany.com') {
        return response()->json([
            'message' => 'Please use Microsoft SSO',
            'redirect_to' => '/api/auth/microsoft/redirect',
        ], 302);
    }

    // Regular authentication for others
    // ...
}
```

### Sync User Data from Microsoft

```php
use Illuminate\Console\Command;
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

class SyncMicrosoftUsers extends Command
{
    protected $signature = 'microsoft:sync-users';

    public function handle()
    {
        $microsoftAuth = app(MicrosoftAuthService::class);

        MicrosoftUser::whereNotNull('user_id')
            ->where('token_expires_at', '>', now())
            ->chunk(100, function ($microsoftUsers) use ($microsoftAuth) {
                foreach ($microsoftUsers as $microsoftUser) {
                    try {
                        $userData = $microsoftAuth->getUserInfo(
                            new \TheNetworg\OAuth2\Client\Token\AccessToken([
                                'access_token' => $microsoftUser->access_token,
                            ])
                        );

                        $microsoftUser->update([
                            'name' => $userData['displayName'] ?? $microsoftUser->name,
                            'job_title' => $userData['jobTitle'] ?? $microsoftUser->job_title,
                            // ... other fields
                        ]);

                        $this->info("Synced: {$microsoftUser->email}");
                    } catch (\Exception $e) {
                        $this->error("Failed: {$microsoftUser->email} - {$e->getMessage()}");
                    }
                }
            });
    }
}
```

### Event-Based Integration

```php
// app/Providers/EventServiceProvider.php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

protected $listen = [
    // Add listeners for Microsoft user events
];

public function boot()
{
    MicrosoftUser::created(function ($microsoftUser) {
        // Send welcome email
        \Mail::to($microsoftUser->email)->send(new \App\Mail\WelcomeMicrosoft($microsoftUser));

        // Log to analytics
        \Log::info('Microsoft user created', ['id' => $microsoftUser->id]);
    });

    MicrosoftUser::updated(function ($microsoftUser) {
        // Sync changes to other services
        if ($microsoftUser->isDirty('job_title')) {
            // Update in CRM, etc.
        }
    });
}
```

## Testing Integration

```php
// tests/Feature/MicrosoftAuthIntegrationTest.php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;
use App\Models\User;

public function test_existing_user_can_link_microsoft_account()
{
    $user = User::factory()->create(['email' => 'test@example.com']);

    $microsoftUser = MicrosoftUser::create([
        'microsoft_id' => 'ms-123',
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    $microsoftUser->update(['user_id' => $user->id]);

    $this->assertNotNull($user->fresh()->microsoftUser);
    $this->assertEquals('ms-123', $user->microsoftUser->microsoft_id);
}

public function test_microsoft_user_creates_new_user()
{
    $microsoftUser = MicrosoftUser::create([
        'microsoft_id' => 'ms-456',
        'email' => 'newuser@example.com',
        'name' => 'New User',
    ]);

    // Trigger user creation logic
    $user = User::create([
        'email' => $microsoftUser->email,
        'name' => $microsoftUser->name,
        'password' => bcrypt('random'),
    ]);

    $microsoftUser->update(['user_id' => $user->id]);

    $this->assertNotNull($user);
    $this->assertEquals($microsoftUser->email, $user->email);
}
```

## Troubleshooting Integration

### Users Created Without Microsoft Link

```php
// Add check in user creation
User::creating(function ($user) {
    $microsoftUser = MicrosoftUser::where('email', $user->email)
        ->whereNull('user_id')
        ->first();

    if ($microsoftUser) {
        // Will be linked after user is created
        session(['link_microsoft_user_id' => $microsoftUser->id]);
    }
});

User::created(function ($user) {
    if (session()->has('link_microsoft_user_id')) {
        $microsoftUserId = session('link_microsoft_user_id');
        MicrosoftUser::find($microsoftUserId)->update(['user_id' => $user->id]);
        session()->forget('link_microsoft_user_id');
    }
});
```

## Summary

The package is designed to integrate seamlessly with existing Laravel applications while providing flexibility for customization. Key integration points:

1. **User Model**: Automatically adds `microsoftUser` relationship
2. **Authentication**: Works alongside existing auth systems
3. **API Routes**: Pre-configured routes for OAuth flow
4. **Middleware**: Token validation and auto-refresh
5. **Events**: Hook into creation/update events
6. **Configuration**: Extensive configuration options

For more details, see the main README.md and USAGE_EXAMPLES.md files.
