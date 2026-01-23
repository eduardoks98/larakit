# Microsoft Auth Package

Microsoft Azure AD OAuth 2.0 authentication package for Laravel using Sanctum. Supports Office 365, Azure AD, and personal Microsoft accounts.

## Features

- Microsoft OAuth 2.0 / Azure AD authentication
- Support for multiple tenant types (common, organizations, consumers, specific tenant)
- Microsoft Graph API integration
- Sanctum token-based authentication
- Automatic user creation and linking
- Token refresh support
- Office 365 integration ready
- User profile sync from Microsoft Graph

## Installation

1. Install via Composer:

```bash
composer require eduardoks98/microsoft-auth
```

2. Publish configuration:

```bash
php artisan vendor:publish --tag=microsoft-config
```

3. Publish and run migrations:

```bash
php artisan vendor:publish --tag=microsoft-migrations
php artisan migrate
```

4. Configure environment variables:

```env
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_client_secret
MICROSOFT_TENANT=common
MICROSOFT_REDIRECT_URI=https://your-app.com/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=https://your-app.com/auth/callback
```

## Azure AD App Registration

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** > **App registrations** > **New registration**
3. Configure:
   - **Name**: Your application name
   - **Supported account types**: Choose based on your needs:
     - Personal Microsoft accounts only → Use `consumers` tenant
     - Work/school accounts only → Use `organizations` tenant
     - Both → Use `common` tenant
   - **Redirect URI**: `https://your-app.com/api/auth/microsoft/callback`
4. After registration:
   - Copy **Application (client) ID** → `MICROSOFT_CLIENT_ID`
   - Go to **Certificates & secrets** → Create new secret → Copy value → `MICROSOFT_CLIENT_SECRET`
5. Configure **API permissions**:
   - Add Microsoft Graph permissions: `openid`, `profile`, `email`, `User.Read`
   - Grant admin consent if required

## Tenant Types

Configure the tenant type in `config/microsoft.php` or via `MICROSOFT_TENANT` env variable:

- **`common`**: Multi-tenant and personal Microsoft accounts (recommended for most apps)
- **`organizations`**: Multi-tenant Azure AD accounts only (work/school accounts)
- **`consumers`**: Personal Microsoft accounts only (outlook.com, live.com, etc.)
- **`{tenant-id}`**: Specific Azure AD tenant (GUID or domain like `contoso.onmicrosoft.com`)

## Usage

### Frontend Integration

1. **Login Button**: Redirect user to Microsoft login

```javascript
// Get authorization URL
const response = await fetch('/api/auth/microsoft/redirect');
const data = await response.json();

// Redirect to Microsoft
window.location.href = data.authorization_url;

// Or direct redirect (non-JSON)
window.location.href = '/api/auth/microsoft/redirect';
```

2. **Callback Handler**: Handle OAuth callback

```javascript
// The package automatically handles the callback and redirects to:
// {MICROSOFT_FRONTEND_REDIRECT_URL}?token={sanctum_token}&user_id={user_id}

// In your frontend callback route:
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const userId = urlParams.get('user_id');

// Store token and authenticate user
localStorage.setItem('auth_token', token);
// Redirect to dashboard or use token for API calls
```

### API Endpoints

#### OAuth Flow

**Redirect to Microsoft**
```
GET /api/auth/microsoft/redirect
```

Optional query parameters:
- `scopes`: Comma-separated list of custom scopes
- `state`: Custom state parameter for CSRF protection

**OAuth Callback**
```
GET /api/auth/microsoft/callback?code={code}&state={state}
```

Returns JSON (if request expects JSON):
```json
{
  "message": "Authentication successful",
  "token": "1|sanctum_token_here",
  "user": { ... },
  "microsoft_user": { ... }
}
```

Or redirects to frontend with token as query parameter.

#### Authenticated Routes

**Get Current User**
```
GET /api/auth/microsoft/me
Authorization: Bearer {token}
```

**Refresh Token**
```
POST /api/auth/microsoft/refresh
Authorization: Bearer {token}
```

**Unlink Microsoft Account**
```
POST /api/auth/microsoft/unlink
Authorization: Bearer {token}
```

### Using Microsoft Graph API

```php
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

$microsoftAuth = app(MicrosoftAuthService::class);
$microsoftUser = auth()->user()->microsoftUser;

// Make Graph API request
$response = $microsoftAuth->graphApiRequest(
    $microsoftUser->access_token,
    'me/messages', // Endpoint
    'GET'
);

// Get user photo
$photo = $microsoftAuth->getUserPhoto($microsoftUser->access_token);
```

### Middleware

Ensure Microsoft token is valid and auto-refresh if expired:

```php
Route::middleware(['auth:sanctum', 'microsoft.token'])->group(function () {
    Route::get('/office365/emails', function (Request $request) {
        $microsoftUser = $request->attributes->get('microsoft_user');
        // Use $microsoftUser->access_token for Graph API calls
    });
});
```

### Model Relationships

The package automatically adds a `microsoftUser` relationship to your User model:

```php
$user = auth()->user();
$microsoftUser = $user->microsoftUser;

if ($microsoftUser) {
    echo $microsoftUser->email;
    echo $microsoftUser->user_principal_name;
    echo $microsoftUser->job_title;
}
```

## Configuration

See `config/microsoft.php` for all available options:

- **OAuth credentials**: client_id, client_secret, tenant
- **Redirect URIs**: redirect_uri, frontend_redirect_url
- **Scopes**: Default scopes for authentication
- **Token settings**: Store tokens, auto-create users, token abilities
- **Graph API**: API version and endpoints

## Common Scopes

- `openid`: Required for OpenID Connect
- `profile`: User's basic profile information
- `email`: User's email address
- `User.Read`: Read user's full profile via Graph API
- `offline_access`: Refresh tokens for long-lived access
- `Mail.Read`: Read user's mail
- `Calendars.Read`: Read user's calendars
- `Files.Read`: Read user's OneDrive files

[Full list of Microsoft Graph permissions](https://learn.microsoft.com/en-us/graph/permissions-reference)

## Database Structure

### microsoft_users Table

Stores Microsoft account information and OAuth tokens:

- `user_id`: Link to application user
- `microsoft_id`: Microsoft unique identifier
- `email`: Primary email
- `user_principal_name`: UPN (username@domain.com)
- `name`, `given_name`, `surname`: User names
- `job_title`, `office_location`: Work information
- `mobile_phone`, `business_phones`: Contact information
- `access_token`, `refresh_token`: OAuth tokens
- `token_expires_at`: Token expiration
- `tenant_id`: Azure AD tenant identifier

## Security

- **CSRF Protection**: State parameter validation
- **Token Storage**: Tokens are hidden in API responses
- **Secure Defaults**: Uses HTTPS and secure OAuth flow
- **Auto-refresh**: Expired tokens are automatically refreshed
- **Sanctum Integration**: Leverages Laravel Sanctum security

## Error Handling

All errors are logged and returned with appropriate HTTP status codes:

- `401`: Authentication failed or token expired
- `403`: Microsoft account not linked
- `404`: Resource not found
- `500`: Server error

## Testing

```bash
composer test
```

## Troubleshooting

### "AADSTS50011: The redirect URI specified in the request does not match"
- Ensure `MICROSOFT_REDIRECT_URI` exactly matches the redirect URI registered in Azure AD
- Include protocol (https://) and port if applicable

### "AADSTS65001: The user or administrator has not consented"
- Add required permissions in Azure AD
- Grant admin consent for organization-wide permissions

### "Token expired and could not be refreshed"
- User needs to re-authenticate
- Check if `offline_access` scope is included for refresh tokens

### "Could not create or find user"
- Ensure `MICROSOFT_AUTO_CREATE_USER=true`
- Check that Microsoft provides an email address
- Verify User model configuration

## License

MIT

## Credits

Built with:
- [TheNetworg OAuth2 Azure](https://github.com/TheNetworg/oauth2-azure)
- [Microsoft Identity Platform](https://learn.microsoft.com/en-us/entra/identity-platform/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)

## Links

- [Microsoft OAuth 2.0 Documentation](https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow)
- [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/overview)
- [Azure AD App Registration](https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps)
