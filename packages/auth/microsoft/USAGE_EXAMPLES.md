# Microsoft Auth - Usage Examples

## Table of Contents

- [Basic OAuth Flow](#basic-oauth-flow)
- [Frontend Integration](#frontend-integration)
- [Backend API Usage](#backend-api-usage)
- [Microsoft Graph API](#microsoft-graph-api)
- [Advanced Scenarios](#advanced-scenarios)

## Basic OAuth Flow

### Step 1: User Clicks "Login with Microsoft"

```html
<!-- Frontend button -->
<button onclick="loginWithMicrosoft()">
    Login with Microsoft
</button>

<script>
function loginWithMicrosoft() {
    // Redirect to OAuth endpoint
    window.location.href = '/api/auth/microsoft/redirect';
}
</script>
```

### Step 2: Handle Callback

The user is redirected back to your frontend with a token:

```
https://your-frontend.com/auth/callback?token=1|xyz123&user_id=1
```

```javascript
// In your frontend callback handler
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const userId = urlParams.get('user_id');

// Store token
localStorage.setItem('auth_token', token);

// Make authenticated requests
fetch('/api/user', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
```

## Frontend Integration

### React Example

```jsx
import { useState, useEffect } from 'react';

function MicrosoftAuthButton() {
    const handleLogin = async () => {
        // Option 1: Direct redirect
        window.location.href = '/api/auth/microsoft/redirect';

        // Option 2: Get URL and redirect manually
        const response = await fetch('/api/auth/microsoft/redirect', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        window.location.href = data.authorization_url;
    };

    return (
        <button onClick={handleLogin}>
            <img src="/microsoft-logo.svg" alt="Microsoft" />
            Sign in with Microsoft
        </button>
    );
}

// Callback handler
function AuthCallback() {
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const error = urlParams.get('error');

        if (error) {
            console.error('Authentication failed:', error);
            return;
        }

        if (token) {
            localStorage.setItem('auth_token', token);
            // Redirect to dashboard or fetch user data
            window.location.href = '/dashboard';
        }
    }, []);

    return <div>Processing authentication...</div>;
}
```

### Vue Example

```vue
<template>
    <button @click="loginWithMicrosoft">
        <img src="/microsoft-logo.svg" alt="Microsoft" />
        Sign in with Microsoft
    </button>
</template>

<script>
export default {
    methods: {
        async loginWithMicrosoft() {
            try {
                const response = await fetch('/api/auth/microsoft/redirect', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                window.location.href = data.authorization_url;
            } catch (error) {
                console.error('Login failed:', error);
            }
        }
    }
}
</script>
```

## Backend API Usage

### Get Microsoft User Information

```php
use Illuminate\Http\Request;

Route::get('/api/profile', function (Request $request) {
    $user = $request->user();
    $microsoftUser = $user->microsoftUser;

    return response()->json([
        'user' => $user,
        'microsoft_account' => $microsoftUser ? [
            'email' => $microsoftUser->email,
            'name' => $microsoftUser->name,
            'upn' => $microsoftUser->user_principal_name,
            'job_title' => $microsoftUser->job_title,
            'office' => $microsoftUser->office_location,
            'last_login' => $microsoftUser->last_login_at,
        ] : null,
    ]);
})->middleware('auth:sanctum');
```

### Link Microsoft Account to Existing User

```php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

Route::post('/api/link-microsoft', function (Request $request) {
    // User must be authenticated
    $user = $request->user();

    // After Microsoft OAuth callback, find the unlinked Microsoft user
    $microsoftUser = MicrosoftUser::where('email', $user->email)
        ->whereNull('user_id')
        ->first();

    if ($microsoftUser) {
        $microsoftUser->update(['user_id' => $user->id]);
        return response()->json(['message' => 'Microsoft account linked']);
    }

    return response()->json(['error' => 'Microsoft account not found'], 404);
})->middleware('auth:sanctum');
```

## Microsoft Graph API

### Access User's Emails

```php
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

Route::get('/api/microsoft/emails', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);
    $microsoftUser = $request->user()->microsoftUser;

    if (!$microsoftUser || !$microsoftUser->hasValidToken()) {
        return response()->json(['error' => 'Microsoft account not linked'], 403);
    }

    // Get latest emails
    $emails = $microsoftAuth->graphApiRequest(
        $microsoftUser->access_token,
        'me/messages?$top=10&$select=subject,from,receivedDateTime',
        'GET'
    );

    return response()->json($emails);
})->middleware(['auth:sanctum', 'microsoft.token']);
```

### Access User's Calendar

```php
Route::get('/api/microsoft/calendar', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);
    $microsoftUser = $request->user()->microsoftUser;

    $events = $microsoftAuth->graphApiRequest(
        $microsoftUser->access_token,
        'me/calendar/events?$top=10',
        'GET'
    );

    return response()->json($events);
})->middleware(['auth:sanctum', 'microsoft.token']);
```

### Get User Photo

```php
Route::get('/api/microsoft/photo', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);
    $microsoftUser = $request->user()->microsoftUser;

    $photo = $microsoftAuth->getUserPhoto($microsoftUser->access_token);

    if ($photo) {
        return response($photo)->header('Content-Type', 'image/jpeg');
    }

    return response()->json(['error' => 'Photo not found'], 404);
})->middleware(['auth:sanctum', 'microsoft.token']);
```

### Send Email on Behalf of User

```php
Route::post('/api/microsoft/send-email', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);
    $microsoftUser = $request->user()->microsoftUser;

    $email = [
        'message' => [
            'subject' => $request->input('subject'),
            'body' => [
                'contentType' => 'HTML',
                'content' => $request->input('body'),
            ],
            'toRecipients' => [
                [
                    'emailAddress' => [
                        'address' => $request->input('to'),
                    ],
                ],
            ],
        ],
        'saveToSentItems' => true,
    ];

    $response = $microsoftAuth->graphApiRequest(
        $microsoftUser->access_token,
        'me/sendMail',
        'POST',
        ['body' => json_encode($email)]
    );

    return response()->json(['message' => 'Email sent']);
})->middleware(['auth:sanctum', 'microsoft.token']);
```

## Advanced Scenarios

### Custom Scopes

Request additional permissions during authentication:

```php
Route::get('/api/microsoft/redirect-with-mail', function () {
    $microsoftAuth = app(MicrosoftAuthService::class);

    $url = $microsoftAuth->getAuthorizationUrl([
        'scopes' => [
            'openid',
            'profile',
            'email',
            'User.Read',
            'Mail.Read',
            'Mail.Send',
            'Calendars.Read',
        ],
    ]);

    return redirect($url);
});
```

### Tenant-Specific Authentication

Authenticate only users from a specific organization:

```php
// In config/microsoft.php or .env
'tenant' => 'your-tenant-id', // or 'contoso.onmicrosoft.com'
```

### Manual Token Refresh

```php
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

Route::post('/api/microsoft/manual-refresh', function (Request $request) {
    $microsoftAuth = app(MicrosoftAuthService::class);
    $microsoftUser = $request->user()->microsoftUser;

    if (!$microsoftUser->refresh_token) {
        return response()->json(['error' => 'No refresh token'], 400);
    }

    try {
        $token = $microsoftAuth->refreshToken($microsoftUser->refresh_token);

        $microsoftUser->updateTokens(
            $token->getToken(),
            $token->getRefreshToken(),
            $token->getExpires() ? $token->getExpires() - time() : null
        );

        return response()->json(['message' => 'Token refreshed']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 401);
    }
})->middleware('auth:sanctum');
```

### Check Multiple Microsoft Accounts

```php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

Route::get('/api/microsoft/accounts', function (Request $request) {
    $user = $request->user();

    // Get all Microsoft accounts linked to this user
    $accounts = MicrosoftUser::where('user_id', $user->id)->get();

    return response()->json([
        'accounts' => $accounts->map(fn($acc) => [
            'email' => $acc->email,
            'upn' => $acc->user_principal_name,
            'tenant' => $acc->tenant_id,
            'last_login' => $acc->last_login_at,
            'has_valid_token' => $acc->hasValidToken(),
        ]),
    ]);
})->middleware('auth:sanctum');
```

### Organization-Only Login

Force users to use work/school accounts:

```env
MICROSOFT_TENANT=organizations
```

### Personal Accounts Only

Force users to use personal Microsoft accounts:

```env
MICROSOFT_TENANT=consumers
```

### Custom User Creation Logic

Override the auto-create behavior:

```php
// In AppServiceProvider or custom service provider
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

MicrosoftUser::creating(function ($microsoftUser) {
    // Custom logic before creating Microsoft user
    logger('Creating Microsoft user', ['email' => $microsoftUser->email]);
});

MicrosoftUser::created(function ($microsoftUser) {
    // Send welcome email, create related records, etc.
    if ($microsoftUser->user_id) {
        Mail::to($microsoftUser->email)->send(new WelcomeEmail($microsoftUser));
    }
});
```

### Multi-Tenant SaaS Application

```php
// Store tenant mapping
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

Route::post('/api/organizations/link-azure', function (Request $request) {
    $organization = $request->user()->organization;

    // Store Azure tenant ID for this organization
    $organization->update([
        'azure_tenant_id' => $request->input('tenant_id'),
    ]);

    return response()->json(['message' => 'Azure AD linked']);
})->middleware('auth:sanctum');

// Verify user belongs to organization's tenant
Route::middleware(['auth:sanctum'])->get('/api/verify-tenant', function (Request $request) {
    $user = $request->user();
    $organization = $user->organization;
    $microsoftUser = $user->microsoftUser;

    if ($microsoftUser->tenant_id !== $organization->azure_tenant_id) {
        return response()->json(['error' => 'Tenant mismatch'], 403);
    }

    return response()->json(['message' => 'Verified']);
});
```

## Error Handling

### Frontend Error Handling

```javascript
// Check for errors in callback
const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get('error');
const message = urlParams.get('message');

if (error) {
    switch (error) {
        case 'auth_failed':
            alert('Microsoft authentication failed: ' + message);
            break;
        case 'access_denied':
            alert('You denied access to your Microsoft account');
            break;
        default:
            alert('An error occurred during authentication');
    }
    // Redirect back to login
    window.location.href = '/login';
}
```

### Backend Error Handling

```php
Route::get('/api/microsoft/protected', function (Request $request) {
    try {
        $microsoftUser = $request->user()->microsoftUser;

        if (!$microsoftUser) {
            return response()->json([
                'error' => 'Microsoft account not linked',
                'action' => 'redirect_to_oauth',
            ], 403);
        }

        if ($microsoftUser->isTokenExpired() && !$microsoftUser->refresh_token) {
            return response()->json([
                'error' => 'Token expired',
                'action' => 'reauthorize',
            ], 401);
        }

        // Your logic here

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'An error occurred',
            'message' => $e->getMessage(),
        ], 500);
    }
})->middleware('auth:sanctum');
```
