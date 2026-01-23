# Google Auth Package

> Google OAuth 2.0 integration for Laravel applications.

## Overview

The `eduardoks98/google-auth` package provides seamless Google OAuth 2.0 authentication integration with automatic user profile synchronization and Sanctum token management.

## Installation

```bash
composer require eduardoks98/google-auth
```

## Configuration

### Environment Variables

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourapp.com/auth/google/callback
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\GoogleAuth\GoogleAuthServiceProvider" --tag="config"
```

## Usage

### Basic Authentication Flow

```php
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;

class GoogleAuthController extends Controller
{
    public function __construct(
        private GoogleAuthService $googleAuth
    ) {}

    public function redirect()
    {
        return redirect($this->googleAuth->getAuthorizationUrl());
    }

    public function callback(Request $request)
    {
        $user = $this->googleAuth->handleCallback($request->get('code'));

        // User is now authenticated with Sanctum token
        return response()->json([
            'user' => $user,
            'token' => $user->currentAccessToken()
        ]);
    }
}
```

### Get User Profile

```php
$profile = $this->googleAuth->getUserProfile($accessToken);

// Returns:
// - id: Google user ID
// - email: User's email
// - name: Full name
// - picture: Profile picture URL
// - verified_email: Email verification status
```

## Features

- OAuth 2.0 flow with PKCE support
- Automatic user creation/linking
- Profile synchronization (name, email, avatar)
- Token refresh handling
- Sanctum integration
- Customizable user model

## Dependencies

- `league/oauth2-google` ^4.0
- `eduardoks98/base-api` ^1.0

## Routes

The package registers the following routes:

```
GET  /auth/google          -> Redirect to Google
GET  /auth/google/callback -> Handle callback
POST /auth/google/token    -> Exchange token (mobile apps)
```

## Security

- State parameter validation (CSRF protection)
- PKCE support for mobile apps
- Secure token storage
- Automatic token refresh

## Related

- [Facebook Auth](./facebook-auth.md)
- [Microsoft Auth](./microsoft-auth.md)
- [Auth Package](./auth.md)
