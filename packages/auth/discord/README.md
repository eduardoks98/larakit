# Discord OAuth 2.0 Authentication for Laravel

A Laravel package for Discord OAuth 2.0 authentication using League OAuth2 Discord Provider with Sanctum integration.

## Installation

```bash
composer require eduardoks98/auth-discord
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=discord-auth-config
```

Publish the migrations:

```bash
php artisan vendor:publish --tag=discord-auth-migrations
php artisan migrate
```

### Environment Variables

Add the following to your `.env` file:

```env
DISCORD_CLIENT_ID=your-client-id
DISCORD_CLIENT_SECRET=your-client-secret
DISCORD_REDIRECT_URI=https://your-app.com/api/auth/discord/callback

# Optional
DISCORD_AUTH_USER_MODEL=App\Models\User
DISCORD_AUTO_CREATE_USERS=true
DISCORD_AUTO_SYNC_USER_DATA=true
DISCORD_AUTH_TOKEN_NAME=discord-auth-token
DISCORD_AUTH_FRONTEND_REDIRECT_URL=https://your-frontend.com/auth/callback
DISCORD_ENABLE_REFRESH_TOKEN=true
DISCORD_PROMPT=none
```

### Getting Discord Credentials

1. Go to [Discord Developer Portal](https://discord.com/developers/applications)
2. Create a new application
3. Go to OAuth2 settings
4. Add your redirect URI
5. Copy the Client ID and Client Secret

## Usage

### Add Trait to User Model

```php
use Eduardoks98\DiscordAuth\Traits\HasDiscordAuth;

class User extends Authenticatable
{
    use HasDiscordAuth;

    // ...
}
```

### Available Routes

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/api/auth/discord/redirect` | Redirect to Discord OAuth |
| GET | `/api/auth/discord/callback` | Handle OAuth callback |
| GET | `/api/auth/discord/profile` | Get Discord profile (auth required) |
| POST | `/api/auth/discord/refresh` | Refresh access token (auth required) |
| DELETE | `/api/auth/discord/disconnect` | Disconnect Discord account (auth required) |

### Frontend Integration

#### Redirect to Discord Login

```javascript
// Option 1: Redirect user
window.location.href = '/api/auth/discord/redirect';

// Option 2: Get URL via API (for custom handling)
const response = await fetch('/api/auth/discord/redirect', {
    headers: { 'Accept': 'application/json' }
});
const { data: { authorization_url } } = await response.json();
window.location.href = authorization_url;
```

#### Handle Callback

After authentication, the user is redirected to `DISCORD_AUTH_FRONTEND_REDIRECT_URL` with:
- `?token=xxx` on success
- `?error=xxx` on failure

```javascript
// In your frontend callback handler
const params = new URLSearchParams(window.location.search);
const token = params.get('token');
const error = params.get('error');

if (token) {
    // Store token and redirect to app
    localStorage.setItem('auth_token', token);
} else if (error) {
    // Handle error
    console.error('Auth failed:', error);
}
```

### User Model Methods

```php
$user->hasDiscordAccount();        // Check if Discord is linked
$user->getDiscordAvatar();         // Get avatar URL
$user->getDiscordDisplayName();    // Get display name
$user->getDiscordUsername();       // Get username
$user->isDiscordTokenExpired();    // Check token expiration
$user->discordUser;                // Get DiscordUser model
```

### Discord User Model Methods

```php
$discordUser->getDisplayName();    // Returns global_name or username
$discordUser->getFullUsername();   // Returns "username#1234" or username
$discordUser->isTokenExpired();    // Check if access token is expired
```

## OAuth Scopes

Default scopes:
- `identify` - Basic user info (username, avatar, etc.)
- `email` - User's email address

Available scopes:
- `guilds` - Access to user's guilds list
- `guilds.join` - Ability to join guilds
- `connections` - User's linked accounts
- `bot` - For bot applications

To customize scopes, update the `scopes` array in `config/discord-auth.php`.

## License

MIT
