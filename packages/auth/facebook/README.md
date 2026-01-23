# Facebook Authentication Package

Facebook Login Integration for Laravel using League OAuth2 Facebook with Graph API v19.0 and Laravel Sanctum.

## Features

- Facebook OAuth2 authentication flow
- Integration with Laravel Sanctum for token-based authentication
- Facebook Graph API v19.0 support
- User profile management (email, name, avatar, facebook_id)
- Automatic user creation or linking
- Configurable scopes and user fields
- Frontend redirect support
- Comprehensive logging

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- eduardoks98/auth package (Laravel Sanctum)
- Facebook App credentials

## Installation

1. Install the package via Composer:

```bash
composer require eduardoks98/facebook-auth
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag=facebook-auth-config
```

3. Publish and run migrations:

```bash
php artisan vendor:publish --tag=facebook-auth-migrations
php artisan migrate
```

4. Configure your environment variables in `.env`:

```env
# Facebook App Credentials
FACEBOOK_APP_ID=your-app-id
FACEBOOK_APP_SECRET=your-app-secret

# Graph API Version
FACEBOOK_GRAPH_API_VERSION=v19.0

# OAuth Redirect URI (must match Facebook App settings)
FACEBOOK_REDIRECT_URI="${APP_URL}/api/facebook-auth/callback"

# Frontend Redirect URL (after successful authentication)
FACEBOOK_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"

# Optional: User Model
FACEBOOK_USER_MODEL=App\Models\User

# Optional: Logging
FACEBOOK_AUTH_LOGGING_ENABLED=true
FACEBOOK_AUTH_LOG_CHANNEL=stack
```

## Facebook App Setup

1. Go to [Facebook Developers](https://developers.facebook.com/apps)
2. Create a new app or select an existing one
3. Add "Facebook Login" product to your app
4. Configure OAuth Redirect URIs:
   - Add your callback URL: `https://your-domain.com/api/facebook-auth/callback`
5. Get your App ID and App Secret from Settings > Basic
6. Configure your app settings:
   - App Domains: your domain
   - Privacy Policy URL
   - Terms of Service URL

## Usage

### Authentication Flow

#### 1. Redirect to Facebook

**Endpoint:** `GET /api/facebook-auth/redirect`

**Response:**
```json
{
  "success": true,
  "data": {
    "authorization_url": "https://www.facebook.com/v19.0/dialog/oauth?...",
    "state": "random-state-string"
  },
  "message": "Facebook authorization URL generated successfully"
}
```

**Frontend Implementation:**
```javascript
// Get authorization URL
const response = await fetch('/api/facebook-auth/redirect');
const { data } = await response.json();

// Redirect user to Facebook
window.location.href = data.authorization_url;
```

#### 2. Handle Callback

**Endpoint:** `GET /api/facebook-auth/callback?code={code}&state={state}`

This endpoint is automatically called by Facebook after user authorization.

**Response (JSON mode):**
```json
{
  "success": true,
  "data": {
    "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "facebook_user": {
      "facebook_id": "1234567890",
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "https://platform-lookaside.fbsbx.com/..."
    }
  },
  "message": "Facebook authentication successful"
}
```

**Response (Redirect mode):**
If `FACEBOOK_FRONTEND_REDIRECT_URL` is configured, the user will be redirected to:
```
https://your-frontend.com/auth/callback?token=1|xxxxxxxxxxxxxxxxxxxxxxx
```

### Protected Routes

All protected routes require the `Authorization: Bearer {token}` header.

#### Get Facebook Profile

**Endpoint:** `GET /api/facebook-auth/profile`

**Headers:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

**Response:**
```json
{
  "success": true,
  "data": {
    "facebook_id": "1234567890",
    "name": "John Doe",
    "email": "john@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "avatar_url": "https://platform-lookaside.fbsbx.com/...",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "message": "Facebook profile retrieved successfully"
}
```

#### Disconnect Facebook Account

**Endpoint:** `DELETE /api/facebook-auth/disconnect`

**Headers:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

**Response:**
```json
{
  "success": true,
  "message": "Facebook account disconnected successfully"
}
```

## Configuration

The configuration file is located at `config/facebook-auth.php`:

```php
return [
    // Facebook App Credentials
    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),

    // Graph API Version
    'graph_api_version' => env('FACEBOOK_GRAPH_API_VERSION', 'v19.0'),

    // OAuth Redirect URI
    'redirect_uri' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/api/facebook-auth/callback'),

    // OAuth Scopes
    'scopes' => [
        'email',
        'public_profile',
    ],

    // User Fields to fetch from Graph API
    'user_fields' => [
        'id',
        'name',
        'email',
        'first_name',
        'last_name',
        'picture.type(large)',
    ],

    // Frontend Redirect URL
    'frontend_redirect_url' => env('FACEBOOK_FRONTEND_REDIRECT_URL', env('FRONTEND_URL') . '/auth/callback'),

    // User Model
    'user_model' => env('FACEBOOK_USER_MODEL', 'App\\Models\\User'),

    // Sanctum Token Configuration
    'token' => [
        'name' => 'facebook-auth-token',
        'abilities' => ['*'],
        'expires_in' => null, // null = never expires
    ],
];
```

## Database Schema

### facebook_users Table

```sql
id                  bigint unsigned primary key
user_id             bigint unsigned (foreign key to users)
facebook_id         varchar(255) unique
email               varchar(255) nullable
name                varchar(255) nullable
first_name          varchar(255) nullable
last_name           varchar(255) nullable
avatar_url          text nullable
access_token        text nullable
metadata            json nullable
created_at          timestamp
updated_at          timestamp
```

## User Model Integration

To add a relationship to your User model:

```php
use Eduardoks98\FacebookAuth\Models\FacebookUser;

class User extends Authenticatable
{
    // ...

    /**
     * Get the user's Facebook profile.
     */
    public function facebookUser()
    {
        return $this->hasOne(FacebookUser::class);
    }
}
```

## Service Usage

You can use the `FacebookAuthService` directly in your code:

```php
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;

class YourController extends Controller
{
    public function __construct(
        protected FacebookAuthService $facebookAuthService
    ) {}

    public function example()
    {
        // Get authorization URL
        $authUrl = $this->facebookAuthService->getAuthorizationUrl();

        // Handle callback
        $result = $this->facebookAuthService->handleCallback($code, $state);

        // Access user data
        $user = $result['user'];
        $token = $result['token'];
        $facebookUser = $result['facebook_user'];
    }
}
```

## Error Handling

All endpoints return standardized error responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "error": "Detailed error information"
  }
}
```

Common HTTP status codes:
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (authentication failed)
- `404` - Not Found (resource not found)
- `500` - Internal Server Error

## Security

- OAuth state parameter for CSRF protection
- Secure token storage
- Access tokens are hidden in model serialization
- Webhook signature verification (if applicable)
- Configurable token expiration

## Facebook Scopes

Available scopes (configure in `config/facebook-auth.php`):

- `email` - Access to user's email address
- `public_profile` - Access to public profile information
- `user_friends` - Access to user's friends list
- `user_birthday` - Access to user's birthday
- `user_location` - Access to user's location

See [Facebook Permissions Reference](https://developers.facebook.com/docs/permissions/reference) for more.

## Graph API Fields

Available user fields (configure in `config/facebook-auth.php`):

- `id` - Facebook user ID
- `name` - Full name
- `email` - Email address
- `first_name` - First name
- `last_name` - Last name
- `picture` - Profile picture
- `birthday` - Birthday
- `location` - Current location
- `gender` - Gender

See [Facebook User Reference](https://developers.facebook.com/docs/graph-api/reference/user) for more.

## Testing

```bash
composer test
```

## License

MIT

## Credits

- [League OAuth2 Facebook](https://github.com/thephpleague/oauth2-facebook)
- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login)
- [Facebook Graph API](https://developers.facebook.com/docs/graph-api)

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/eduardoks98/facebook-auth/issues).
