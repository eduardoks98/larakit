# Google Auth Package - Quick Reference

## Installation

```bash
composer require eduardoks98/google-auth
php artisan vendor:publish --tag=google-auth-config
php artisan vendor:publish --tag=google-auth-migrations
php artisan migrate
```

## Environment Variables

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/google/callback"
GOOGLE_AUTO_CREATE_USERS=true
GOOGLE_AUTO_SYNC_USER_DATA=true
FRONTEND_URL=http://localhost:3000
GOOGLE_AUTH_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

## User Model Setup

```php
use Eduardoks98\GoogleAuth\Traits\HasGoogleAuth;

class User extends Authenticatable
{
    use HasApiTokens, HasGoogleAuth;
}
```

## API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/auth/google/redirect` | No | Get OAuth authorization URL |
| GET | `/api/auth/google/callback` | No | Handle OAuth callback |
| GET | `/api/auth/google/profile` | Yes | Get Google profile |
| POST | `/api/auth/google/refresh` | Yes | Refresh access token |
| DELETE | `/api/auth/google/revoke` | Yes | Revoke Google access |

## Frontend Integration

### React

```jsx
// Login
const handleLogin = () => {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect';
};

// Callback
useEffect(() => {
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');
  if (token) {
    localStorage.setItem('auth_token', token);
  }
}, []);
```

### Vue.js

```vue
<!-- Login -->
<button @click="loginWithGoogle">Login with Google</button>

<script setup>
const loginWithGoogle = () => {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect';
};
</script>
```

## Service Usage

```php
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;

// Get authorization URL
$url = $googleAuthService->getAuthorizationUrl();

// Handle callback
$result = $googleAuthService->handleCallback($code);

// Refresh token
$newToken = $googleAuthService->refreshAccessToken($refreshToken);

// Get valid token (auto-refresh)
$token = $googleAuthService->getValidAccessToken($googleUser);

// Revoke access
$googleAuthService->revokeAccess($googleUser);
```

## Model Relationships

```php
// User has one GoogleUser
$user->googleUser

// GoogleUser belongs to User
$googleUser->user

// Check if user has Google account
$user->hasGoogleAccount()

// Get Google profile picture
$user->getGooglePicture()

// Check if token is expired
$user->isGoogleTokenExpired()
```

## Response Formats

### Successful Callback

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

### Error Response

```json
{
  "success": false,
  "message": "Authentication failed",
  "error": "Invalid authorization code"
}
```

## Google Cloud Console URLs

- **Console**: https://console.cloud.google.com/
- **Credentials**: https://console.cloud.google.com/apis/credentials
- **OAuth Consent**: https://console.cloud.google.com/apis/credentials/consent

## OAuth Scopes

Default scopes (configured in `config/google-auth.php`):
- `openid` - OpenID Connect authentication
- `profile` - User profile information
- `email` - User email address

Additional available scopes:
- `https://www.googleapis.com/auth/user.birthday.read` - Birthday
- `https://www.googleapis.com/auth/user.phonenumbers.read` - Phone numbers
- `https://www.googleapis.com/auth/user.addresses.read` - Addresses

## Testing with cURL

```bash
# Get authorization URL
curl -X GET http://localhost:8000/api/auth/google/redirect \
  -H "Accept: application/json"

# Test authenticated endpoint
curl -X GET http://localhost:8000/api/auth/google/profile \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Refresh token
curl -X POST http://localhost:8000/api/auth/google/refresh \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Revoke access
curl -X DELETE http://localhost:8000/api/auth/google/revoke \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| `redirect_uri_mismatch` | Check redirect URI in Google Console matches your `.env` |
| `invalid_client` | Verify client ID and secret are correct |
| Users not created | Set `GOOGLE_AUTO_CREATE_USERS=true` |
| Token expired | Enable refresh tokens with `GOOGLE_ENABLE_REFRESH_TOKEN=true` |

## Directory Structure

```
packages/google-auth/
├── config/
│   └── google-auth.php
├── database/
│   └── migrations/
│       └── 2024_01_01_000001_create_google_users_table.php
├── examples/
│   ├── frontend-integration.md
│   └── postman-collection.json
├── src/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── GoogleAuthController.php
│   │   └── Middleware/
│   ├── Models/
│   │   └── GoogleUser.php
│   ├── Services/
│   │   └── GoogleAuthService.php
│   ├── Traits/
│   │   └── HasGoogleAuth.php
│   ├── GoogleAuthServiceProvider.php
│   └── routes.php
├── tests/
│   └── GoogleAuthTest.php
├── composer.json
├── README.md
├── SETUP_GUIDE.md
└── CHANGELOG.md
```

## Security Best Practices

- ✅ Always use HTTPS in production
- ✅ Keep client secret in `.env` file
- ✅ Never commit credentials to Git
- ✅ Enable CSRF protection (state parameter)
- ✅ Use refresh tokens for long-lived access
- ✅ Validate redirect URIs
- ✅ Limit OAuth scopes to what you need
- ✅ Implement rate limiting
- ✅ Monitor for suspicious activity

## Documentation Links

- **Full Documentation**: [README.md](README.md)
- **Setup Guide**: [SETUP_GUIDE.md](SETUP_GUIDE.md)
- **Frontend Examples**: [examples/frontend-integration.md](examples/frontend-integration.md)
- **Google OAuth Docs**: https://developers.google.com/identity/protocols/oauth2
- **League OAuth2**: https://github.com/thephpleague/oauth2-google
