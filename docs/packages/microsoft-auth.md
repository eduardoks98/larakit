# Microsoft Auth Package

> Microsoft/Azure AD OAuth integration with Office 365 support for Laravel applications.

## Overview

The `eduardoks98/microsoft-auth` package provides Microsoft OAuth authentication with Azure AD support, enabling Office 365 integration and Microsoft Graph API access.

## Installation

```bash
composer require eduardoks98/microsoft-auth
```

## Configuration

### Environment Variables

```env
MICROSOFT_CLIENT_ID=your_azure_app_id
MICROSOFT_CLIENT_SECRET=your_azure_app_secret
MICROSOFT_REDIRECT_URI=https://yourapp.com/auth/microsoft/callback
MICROSOFT_TENANT_ID=common
```

### Tenant Options

```env
# Single tenant (your organization only)
MICROSOFT_TENANT_ID=your-tenant-id

# Multi-tenant (any Azure AD)
MICROSOFT_TENANT_ID=organizations

# Personal accounts only
MICROSOFT_TENANT_ID=consumers

# Any Microsoft account (default)
MICROSOFT_TENANT_ID=common
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\MicrosoftAuth\MicrosoftAuthServiceProvider" --tag="config"
```

## Usage

### Basic Authentication Flow

```php
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

class MicrosoftAuthController extends Controller
{
    public function __construct(
        private MicrosoftAuthService $microsoftAuth
    ) {}

    public function redirect()
    {
        return redirect($this->microsoftAuth->getAuthorizationUrl([
            'scope' => ['openid', 'profile', 'email', 'User.Read']
        ]));
    }

    public function callback(Request $request)
    {
        $user = $this->microsoftAuth->handleCallback($request->get('code'));

        return response()->json([
            'user' => $user,
            'token' => $user->currentAccessToken()
        ]);
    }
}
```

### Get User Profile (Graph API)

```php
$profile = $this->microsoftAuth->getUserProfile($accessToken);

// Returns:
// - id: Microsoft user ID
// - email: User's email (userPrincipalName)
// - displayName: Full name
// - givenName: First name
// - surname: Last name
// - jobTitle: Job title
// - officeLocation: Office location
```

### Microsoft Graph API

```php
// Get user's calendar events
$events = $this->microsoftAuth->graphRequest(
    $accessToken,
    '/me/calendar/events',
    ['$top' => 10]
);

// Get user's OneDrive files
$files = $this->microsoftAuth->graphRequest(
    $accessToken,
    '/me/drive/root/children'
);
```

## Features

- OAuth 2.0 flow with Azure AD
- Multi-tenant support
- Microsoft Graph API integration
- Office 365 integration
- Automatic user creation/linking
- Profile synchronization
- Token refresh handling
- Sanctum integration

## Scopes

```php
// Common scopes
$scopes = [
    'openid',           // OpenID Connect
    'profile',          // Basic profile
    'email',            // Email address
    'User.Read',        // Read user profile
    'Calendars.Read',   // Read calendar (requires admin consent)
    'Files.Read',       // Read OneDrive files
    'Mail.Read',        // Read emails
];
```

## Dependencies

- `thenetworg/oauth2-azure` ^2.0
- `eduardoks98/base-api` ^1.0

## Routes

```
GET  /auth/microsoft          -> Redirect to Microsoft
GET  /auth/microsoft/callback -> Handle callback
POST /auth/microsoft/token    -> Exchange token (mobile apps)
```

## Azure AD Setup

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to Azure Active Directory > App registrations
3. Create new registration
4. Configure redirect URI
5. Create client secret
6. Configure API permissions

## Security

- State parameter validation
- PKCE support
- Secure token storage
- Automatic token refresh
- Admin consent handling

## Related

- [Google Auth](./google-auth.md)
- [Facebook Auth](./facebook-auth.md)
- [Auth Package](./auth.md)
