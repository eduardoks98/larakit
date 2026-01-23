# Google Auth Package

Laravel package for Google OAuth 2.0 authentication using The League's OAuth2 Google provider with Sanctum integration.

## Features

- Google OAuth 2.0 authentication flow
- Automatic user creation and synchronization
- Sanctum token generation
- Refresh token support
- Google profile management
- Token revocation
- Complete user data sync (name, email, avatar)

## Installation

1. Install the package via Composer:

```bash
composer require eduardoks98/google-auth
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag=google-auth-config
```

3. Publish and run migrations:

```bash
php artisan vendor:publish --tag=google-auth-migrations
php artisan migrate
```

4. Add environment variables to your `.env` file:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/google/callback"
GOOGLE_AUTO_CREATE_USERS=true
GOOGLE_AUTO_SYNC_USER_DATA=true
GOOGLE_ENABLE_REFRESH_TOKEN=true
FRONTEND_URL=http://localhost:3000
GOOGLE_AUTH_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

## Getting Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure the OAuth consent screen
6. Add authorized redirect URIs:
   - `http://localhost:8000/api/auth/google/callback` (development)
   - `https://yourdomain.com/api/auth/google/callback` (production)
7. Copy the Client ID and Client Secret to your `.env` file

## Usage

### Add Trait to User Model

Add the `HasGoogleAuth` trait to your User model:

```php
use Eduardoks98\GoogleAuth\Traits\HasGoogleAuth;

class User extends Authenticatable
{
    use HasGoogleAuth;

    // ... rest of your model
}
```

This adds the following methods:
- `googleUser()` - Relationship to GoogleUser model
- `hasGoogleAccount()` - Check if user has linked Google account
- `getGooglePicture()` - Get Google profile picture
- `isGoogleTokenExpired()` - Check if token is expired

### Authentication Flow

#### 1. Redirect to Google

Frontend initiates authentication by redirecting to:

```
GET /api/auth/google/redirect
```

Or for API clients, get the authorization URL:

```bash
curl -X GET http://localhost:8000/api/auth/google/redirect \
  -H "Accept: application/json"
```

Response:
```json
{
  "success": true,
  "message": "Authorization URL generated successfully",
  "data": {
    "authorization_url": "https://accounts.google.com/o/oauth2/auth?...",
    "state": "random-state-string"
  }
}
```

#### 2. Handle Callback

After user authorizes, Google redirects to:

```
GET /api/auth/google/callback?code=xxx&state=xxx
```

The callback endpoint will:
1. Validate the authorization code
2. Exchange code for access token
3. Get user profile from Google
4. Create or update user and GoogleUser records
5. Generate Sanctum token
6. Redirect to frontend with token

Response (JSON):
```json
{
  "success": true,
  "message": "Authentication successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "google_user": {
      "id": "uuid",
      "google_id": "123456789",
      "email": "john@example.com",
      "name": "John Doe",
      "picture": "https://..."
    },
    "token": "1|xxxxx",
    "token_type": "Bearer"
  }
}
```

#### 3. Use Token for API Requests

Use the returned token in subsequent requests:

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|xxxxx"
```

### API Endpoints

#### Get Google Profile

Get the authenticated user's Google profile:

```bash
curl -X GET http://localhost:8000/api/auth/google/profile \
  -H "Authorization: Bearer 1|xxxxx"
```

Response:
```json
{
  "success": true,
  "message": "Google profile retrieved successfully",
  "data": {
    "google_user": {
      "id": "uuid",
      "google_id": "123456789",
      "email": "john@example.com",
      "name": "John Doe",
      "picture": "https://...",
      "access_token": "ya29.xxx",
      "refresh_token": "1//xxx"
    },
    "is_token_expired": false
  }
}
```

#### Refresh Access Token

Refresh the Google access token:

```bash
curl -X POST http://localhost:8000/api/auth/google/refresh \
  -H "Authorization: Bearer 1|xxxxx"
```

Response:
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "ya29.new-token",
    "expires_in": 3600
  }
}
```

#### Revoke Access

Revoke Google access and unlink the account:

```bash
curl -X DELETE http://localhost:8000/api/auth/google/revoke \
  -H "Authorization: Bearer 1|xxxxx"
```

Response:
```json
{
  "success": true,
  "message": "Google access revoked successfully"
}
```

## Frontend Integration

### React Example

```jsx
// Login button
const handleGoogleLogin = () => {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect';
};

// Callback page (e.g., /auth/callback)
useEffect(() => {
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');
  const error = params.get('error');

  if (token) {
    // Store token
    localStorage.setItem('auth_token', token);

    // Fetch user data
    fetch('http://localhost:8000/api/user', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    .then(res => res.json())
    .then(data => {
      // Handle user data
      console.log(data);
    });
  } else if (error) {
    // Handle error
    console.error(error);
  }
}, []);
```

### Vue.js Example

```vue
<template>
  <button @click="loginWithGoogle">Login with Google</button>
</template>

<script setup>
const loginWithGoogle = () => {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect';
};

// In callback component
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');

  if (token) {
    localStorage.setItem('auth_token', token);
    router.push('/dashboard');
  }
});
</script>
```

## Configuration

The `config/google-auth.php` file allows you to customize:

- **OAuth credentials** - Client ID, secret, redirect URI
- **Scopes** - OAuth scopes to request
- **User models** - Customize user and GoogleUser models
- **Auto-create users** - Automatically create users on first login
- **Auto-sync data** - Sync user data from Google
- **Token settings** - Sanctum token name and abilities
- **Refresh tokens** - Enable/disable refresh token support
- **Frontend redirect** - URL to redirect after authentication

## Service Usage

You can use the `GoogleAuthService` directly in your code:

```php
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;

class YourController extends Controller
{
    public function __construct(
        protected GoogleAuthService $googleAuthService
    ) {}

    public function example()
    {
        // Get authorization URL
        $url = $this->googleAuthService->getAuthorizationUrl();

        // Handle callback
        $result = $this->googleAuthService->handleCallback($code);

        // Refresh token
        $newToken = $this->googleAuthService->refreshAccessToken($refreshToken);

        // Get valid access token (auto-refreshes if expired)
        $token = $this->googleAuthService->getValidAccessToken($googleUser);

        // Revoke access
        $this->googleAuthService->revokeAccess($googleUser);
    }
}
```

## Database Schema

The package creates a `google_users` table with the following structure:

```sql
- id (uuid, primary key)
- user_id (foreign key to users table)
- google_id (unique)
- email
- name
- given_name
- family_name
- picture
- locale
- access_token (encrypted)
- refresh_token (encrypted)
- expires_in
- token_type
- last_login_at
- timestamps
```

## Security Considerations

1. **CSRF Protection**: The package uses state parameter for CSRF protection
2. **Token Storage**: Access and refresh tokens are hidden from JSON serialization
3. **HTTPS**: Always use HTTPS in production
4. **Environment Variables**: Keep credentials in `.env` file, never commit them
5. **Token Expiration**: Tokens are automatically refreshed when expired
6. **User Verification**: Google verifies email addresses, so `email_verified_at` is set automatically

## Testing

Run the test suite:

```bash
composer test
```

Or with Pest:

```bash
./vendor/bin/pest
```

## Documentation Links

- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Google OAuth PHP Documentation](https://developers.google.com/identity/protocols/oauth2/web-server)
- [The League OAuth2 Google](https://github.com/thephpleague/oauth2-google)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

## License

MIT License

## Author

Eduardo Steffens - [GitHub](https://github.com/eduardoks98)
