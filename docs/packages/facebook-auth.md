# Facebook Auth Package

> Facebook OAuth integration with Graph API v19.0 for Laravel applications.

## Overview

The `eduardoks98/facebook-auth` package provides Facebook OAuth authentication with access to the Facebook Graph API v19.0, supporting profile data, email access, and automatic Sanctum integration.

## Installation

```bash
composer require eduardoks98/facebook-auth
```

## Configuration

### Environment Variables

```env
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=https://yourapp.com/auth/facebook/callback
FACEBOOK_GRAPH_VERSION=v19.0
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\FacebookAuth\FacebookAuthServiceProvider" --tag="config"
```

## Usage

### Basic Authentication Flow

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;

class FacebookAuthController extends Controller
{
    public function __construct(
        private FacebookAuthService $facebookAuth
    ) {}

    public function redirect()
    {
        return redirect($this->facebookAuth->getAuthorizationUrl([
            'scope' => ['email', 'public_profile']
        ]));
    }

    public function callback(Request $request)
    {
        $user = $this->facebookAuth->handleCallback($request->get('code'));

        return response()->json([
            'user' => $user,
            'token' => $user->currentAccessToken()
        ]);
    }
}
```

### Get User Profile

```php
$profile = $this->facebookAuth->getUserProfile($accessToken);

// Returns:
// - id: Facebook user ID
// - email: User's email
// - name: Full name
// - first_name: First name
// - last_name: Last name
// - picture: Profile picture URL
```

## Features

- OAuth 2.0 flow
- Graph API v19.0 integration
- Automatic user creation/linking
- Profile synchronization
- Customizable permissions (scopes)
- Token refresh handling
- Sanctum integration

## Permissions (Scopes)

```php
// Available scopes
$scopes = [
    'email',           // User's email
    'public_profile',  // Basic profile info
    'user_friends',    // Friend list (requires app review)
    'user_birthday',   // Birthday (requires app review)
];
```

## Dependencies

- `league/oauth2-facebook` ^2.0
- `eduardoks98/base-api` ^1.0

## Routes

```
GET  /auth/facebook          -> Redirect to Facebook
GET  /auth/facebook/callback -> Handle callback
POST /auth/facebook/token    -> Exchange token (mobile apps)
```

## Security

- State parameter validation
- Long-lived token exchange
- Secure token storage
- App secret proof validation

## Related

- [Google Auth](./google-auth.md)
- [Microsoft Auth](./microsoft-auth.md)
- [Auth Package](./auth.md)
