# Troubleshooting Guide

Common issues and solutions for the Facebook Auth package.

## Installation Issues

### Issue: Package not found

**Error:**
```
Package eduardoks98/facebook-auth not found
```

**Solution:**
1. Make sure the package is registered in your root `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/facebook-auth"
        }
    ],
    "require": {
        "eduardoks98/facebook-auth": "^1.0"
    }
}
```

2. Run `composer update eduardoks98/facebook-auth`

### Issue: Class not found

**Error:**
```
Class 'Eduardoks98\FacebookAuth\FacebookAuthServiceProvider' not found
```

**Solution:**
1. Clear Laravel caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan clear-compiled
```

2. Run `composer dump-autoload`

## Authentication Issues

### Issue: Invalid redirect URI

**Error from Facebook:**
```
Can't Load URL: The domain of this URL isn't included in the app's domains
```

**Solution:**
1. Add your domain to Facebook App settings:
   - Go to Facebook Developers > Your App > Settings > Basic
   - Add your domain to "App Domains"

2. Configure redirect URI in Facebook Login settings:
   - Go to Facebook Login > Settings
   - Add your callback URL to "Valid OAuth Redirect URIs"
   - Example: `https://yourdomain.com/api/facebook-auth/callback`

3. Make sure your `.env` file has the correct redirect URI:
```env
FACEBOOK_REDIRECT_URI=https://yourdomain.com/api/facebook-auth/callback
```

### Issue: Authorization URL not generated

**Error:**
```
Failed to generate Facebook authorization URL
```

**Solution:**
1. Check if Facebook App credentials are configured:
```bash
php artisan tinker
>>> config('facebook-auth.app_id')
>>> config('facebook-auth.app_secret')
```

2. Verify `.env` file:
```env
FACEBOOK_APP_ID=your-app-id
FACEBOOK_APP_SECRET=your-app-secret
```

3. Clear config cache:
```bash
php artisan config:clear
```

### Issue: No email returned from Facebook

**Error:**
```
User email is null
```

**Solution:**
1. Make sure you're requesting the `email` scope:
```php
'scopes' => [
    'email',
    'public_profile',
]
```

2. Check if the Facebook user has verified their email
3. Handle missing email gracefully in your code:
```php
$email = $facebookUser->getEmail() ?? "facebook_{$facebookUser->getId()}@facebook-auth.local";
```

### Issue: State parameter mismatch

**Error:**
```
Invalid state parameter
```

**Solution:**
1. Make sure sessions are working properly
2. Check if session driver is configured in `.env`:
```env
SESSION_DRIVER=file
```

3. Verify session middleware is applied to routes
4. If using API-only mode, disable state verification or implement custom state storage

## Database Issues

### Issue: Migration fails

**Error:**
```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'facebook_users' already exists
```

**Solution:**
1. Check if migration was already run:
```bash
php artisan migrate:status
```

2. If needed, rollback and re-run:
```bash
php artisan migrate:rollback
php artisan migrate
```

3. Or drop the table manually:
```bash
php artisan tinker
>>> Schema::dropIfExists('facebook_users');
>>> exit
php artisan migrate
```

### Issue: Foreign key constraint fails

**Error:**
```
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails
```

**Solution:**
1. Make sure the `users` table exists before running migrations
2. Check if the referenced user ID exists when creating FacebookUser
3. Verify foreign key constraint in migration:
```php
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
```

## API Issues

### Issue: 401 Unauthorized

**Error:**
```json
{
  "message": "Unauthenticated"
}
```

**Solution:**
1. Make sure you're sending the Bearer token:
```
Authorization: Bearer your-token-here
```

2. Verify token is valid:
```bash
php artisan tinker
>>> $token = 'your-token-here';
>>> \Laravel\Sanctum\PersonalAccessToken::findToken($token);
```

3. Check if Sanctum is configured:
```bash
# config/sanctum.php should exist
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Issue: CORS errors

**Error in browser console:**
```
Access to fetch at 'http://localhost:8000/api/facebook-auth/redirect' from origin 'http://localhost:3000' has been blocked by CORS policy
```

**Solution:**
1. Install and configure Laravel CORS:
```bash
composer require fruitcake/laravel-cors
```

2. Publish CORS config:
```bash
php artisan vendor:publish --tag="cors"
```

3. Update `config/cors.php`:
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Issue: Token not working after login

**Error:**
```json
{
  "message": "Unauthenticated"
}
```

**Solution:**
1. Verify Sanctum middleware is applied:
```php
// In your kernel.php or bootstrap/app.php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

2. Check if token abilities are correct:
```php
// When creating token
$token = $user->createToken('facebook-auth-token', ['*']);
```

3. Make sure User model uses HasApiTokens:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

## Facebook App Issues

### Issue: App is in Development Mode

**Error:**
```
This app is in development mode
```

**Solution:**
1. For testing, add test users in Facebook App Dashboard
2. For production, switch app to Live Mode:
   - Complete App Review
   - Add Privacy Policy URL
   - Add Terms of Service URL
   - Submit for review if using advanced permissions

### Issue: App Review Required

**Error:**
```
This permission requires app review
```

**Solution:**
1. Basic permissions (email, public_profile) don't require review
2. For advanced permissions, submit for App Review:
   - Go to App Review > Permissions and Features
   - Request the required permissions
   - Provide detailed use case and screencast

### Issue: Rate Limiting

**Error:**
```
API rate limit exceeded
```

**Solution:**
1. Implement rate limiting in your app:
```php
Route::middleware(['throttle:10,1'])->group(function () {
    // Facebook auth routes
});
```

2. Cache authorization URLs to reduce API calls
3. Consider implementing exponential backoff for retries

## Configuration Issues

### Issue: Config not updating

**Problem:**
Changes to `config/facebook-auth.php` are not reflected

**Solution:**
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Environment variables not working

**Problem:**
`.env` changes are not being picked up

**Solution:**
1. Clear config cache:
```bash
php artisan config:clear
```

2. Restart your development server
3. If using Docker, rebuild containers:
```bash
docker-compose down
docker-compose up -d
```

## Logging and Debugging

### Enable Debug Logging

```php
// In config/facebook-auth.php
'logging' => [
    'enabled' => true,
    'channel' => 'single', // Use 'single' for easier debugging
],
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

### Enable Query Logging

```php
// In AppServiceProvider
use Illuminate\Support\Facades\DB;

public function boot()
{
    if (app()->environment('local')) {
        DB::listen(function ($query) {
            logger()->info($query->sql, $query->bindings);
        });
    }
}
```

### Test Authentication Flow

```bash
php artisan tinker
```

```php
// Test service initialization
$service = app(\Eduardoks98\FacebookAuth\Services\FacebookAuthService::class);

// Test authorization URL generation
$url = $service->getAuthorizationUrl();
echo $url;

// Test Facebook user retrieval
$facebookUser = \Eduardoks98\FacebookAuth\Models\FacebookUser::findByFacebookId('123456789');
dd($facebookUser);
```

## Common Pitfalls

### 1. Using HTTP instead of HTTPS in production

**Problem:** OAuth redirects fail or tokens are exposed

**Solution:** Always use HTTPS in production:
```env
APP_URL=https://yourdomain.com
FACEBOOK_REDIRECT_URI=https://yourdomain.com/api/facebook-auth/callback
```

### 2. Not handling missing email

**Problem:** User creation fails when Facebook doesn't provide email

**Solution:** Implemented in package - generates fallback email

### 3. Hardcoding configuration values

**Problem:** App breaks when deployed to different environments

**Solution:** Always use environment variables:
```php
// ❌ Bad
'app_id' => '123456789',

// ✅ Good
'app_id' => env('FACEBOOK_APP_ID'),
```

### 4. Not verifying state parameter

**Problem:** Vulnerable to CSRF attacks

**Solution:** Always verify state in callback (handled by package)

### 5. Storing access tokens in localStorage

**Problem:** XSS vulnerability

**Solution:** Use httpOnly cookies or secure token storage

## Getting Help

1. Check the logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true`
3. Read the [documentation](README.md)
4. Check [examples](EXAMPLES.md)
5. Search [GitHub issues](https://github.com/eduardoks98/facebook-auth/issues)
6. Create a new issue with:
   - Laravel version
   - PHP version
   - Package version
   - Error message
   - Steps to reproduce

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list | grep facebook

# Test database connection
php artisan migrate:status

# View config
php artisan config:show facebook-auth

# Run tests
composer test

# Check Sanctum installation
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```
