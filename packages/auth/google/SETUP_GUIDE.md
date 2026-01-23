# Google Auth Package - Complete Setup Guide

This guide will walk you through setting up Google OAuth 2.0 authentication in your Laravel application.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Google Cloud Console Setup](#google-cloud-console-setup)
3. [Package Installation](#package-installation)
4. [Configuration](#configuration)
5. [Database Setup](#database-setup)
6. [User Model Integration](#user-model-integration)
7. [Testing the Integration](#testing-the-integration)
8. [Frontend Integration](#frontend-integration)
9. [Production Deployment](#production-deployment)
10. [Troubleshooting](#troubleshooting)

## Prerequisites

- Laravel 10.x, 11.x, or 12.x
- PHP 8.1, 8.2, or 8.3
- Laravel Sanctum installed and configured
- Google Cloud account (free tier available)

## Google Cloud Console Setup

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top
3. Click "New Project"
4. Enter a project name (e.g., "My Laravel App")
5. Click "Create"

### Step 2: Enable Google+ API

1. In your project, go to "APIs & Services" > "Library"
2. Search for "Google+ API"
3. Click on it and click "Enable"
4. Alternatively, search for "People API" and enable it (newer alternative)

### Step 3: Configure OAuth Consent Screen

1. Go to "APIs & Services" > "OAuth consent screen"
2. Select "External" user type (or "Internal" if using Google Workspace)
3. Click "Create"

#### App Information
- **App name**: Your application name (e.g., "My Laravel App")
- **User support email**: Your email address
- **App logo**: Optional, upload your app logo
- **Application home page**: Your app URL (e.g., https://yourdomain.com)
- **Application privacy policy link**: Your privacy policy URL
- **Application terms of service link**: Your terms of service URL

#### Developer contact information
- Enter your email address

4. Click "Save and Continue"

#### Scopes
1. Click "Add or Remove Scopes"
2. Select the following scopes:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
   - `openid`
3. Click "Update"
4. Click "Save and Continue"

#### Test Users (if in testing mode)
1. Add test user emails if your app is in testing mode
2. Click "Save and Continue"

5. Review your settings and click "Back to Dashboard"

### Step 4: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Select "Web application" as the application type

#### Configure OAuth Client

**Name**: Give it a descriptive name (e.g., "Laravel Web Client")

**Authorized JavaScript origins**:
- `http://localhost:8000` (development)
- `https://yourdomain.com` (production)

**Authorized redirect URIs**:
- `http://localhost:8000/api/auth/google/callback` (development)
- `https://yourdomain.com/api/auth/google/callback` (production)

4. Click "Create"
5. **Copy your Client ID and Client Secret** - you'll need these!

### Step 5: Publishing Your App (Optional)

If you want to remove the "This app isn't verified" warning:

1. Go to "OAuth consent screen"
2. Click "Publish App"
3. For production apps, submit for verification (may take several days)

## Package Installation

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

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Google OAuth 2.0 Credentials
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/google/callback"

# Auto-create users on first Google login
GOOGLE_AUTO_CREATE_USERS=true

# Auto-sync user data from Google
GOOGLE_AUTO_SYNC_USER_DATA=true

# Enable refresh token support
GOOGLE_ENABLE_REFRESH_TOKEN=true

# OAuth settings
GOOGLE_ACCESS_TYPE=offline
GOOGLE_PROMPT=select_account

# Sanctum token settings
GOOGLE_AUTH_TOKEN_NAME=google-auth-token

# Frontend URL
FRONTEND_URL=http://localhost:3000
GOOGLE_AUTH_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

### Config File

Review and customize `config/google-auth.php` if needed:

```php
return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),

    'scopes' => [
        'openid',
        'profile',
        'email',
    ],

    'auto_create_users' => env('GOOGLE_AUTO_CREATE_USERS', true),
    'auto_sync_user_data' => env('GOOGLE_AUTO_SYNC_USER_DATA', true),

    // ... other settings
];
```

## Database Setup

The migration creates a `google_users` table. Ensure your `users` table has these columns:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('email')->unique();
    $table->string('name');
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password')->nullable(); // Make nullable for OAuth users
});
```

If you need to modify your users table:

```bash
php artisan make:migration update_users_table_for_google_auth
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('password')->nullable()->change();
    });
}
```

## User Model Integration

Add the `HasGoogleAuth` trait to your User model:

```php
<?php

namespace App\Models;

use Eduardoks98\GoogleAuth\Traits\HasGoogleAuth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasGoogleAuth;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

## Testing the Integration

### Using Postman

1. Import the Postman collection from `examples/postman-collection.json`
2. Set the `base_url` variable to your API URL
3. Send the "Get Authorization URL" request
4. Copy the `authorization_url` from the response
5. Open the URL in a browser and authorize
6. Copy the callback URL with code and state parameters
7. Use those parameters in the "OAuth Callback" request
8. The token will be automatically saved to the `token` variable

### Using cURL

```bash
# Step 1: Get authorization URL
curl -X GET http://localhost:8000/api/auth/google/redirect \
  -H "Accept: application/json"

# Step 2: Open the authorization_url in a browser and authorize
# Google will redirect to: http://localhost:8000/api/auth/google/callback?code=xxx&state=xxx

# Step 3: Test the callback (normally done automatically)
curl -X GET "http://localhost:8000/api/auth/google/callback?code=YOUR_CODE&state=YOUR_STATE" \
  -H "Accept: application/json"

# Step 4: Use the returned token
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Browser

1. Navigate to: `http://localhost:8000/api/auth/google/redirect`
2. You'll be redirected to Google
3. Authorize the application
4. Google redirects back to your callback URL
5. Your frontend will receive the token as a query parameter

## Frontend Integration

### React Setup

```jsx
// src/components/GoogleLogin.jsx
import React from 'react';

const GoogleLogin = () => {
  const handleLogin = () => {
    window.location.href = 'http://localhost:8000/api/auth/google/redirect';
  };

  return (
    <button onClick={handleLogin}>
      Sign in with Google
    </button>
  );
};

export default GoogleLogin;
```

```jsx
// src/pages/AuthCallback.jsx
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const AuthCallback = () => {
  const navigate = useNavigate();

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (token) {
      localStorage.setItem('auth_token', token);
      navigate('/dashboard');
    }
  }, [navigate]);

  return <div>Authenticating...</div>;
};

export default AuthCallback;
```

### Routes

```jsx
// src/App.jsx
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Login from './pages/Login';
import AuthCallback from './pages/AuthCallback';
import Dashboard from './pages/Dashboard';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/auth/callback" element={<AuthCallback />} />
        <Route path="/dashboard" element={<Dashboard />} />
      </Routes>
    </BrowserRouter>
  );
}
```

## Production Deployment

### Environment Variables

Update your production `.env`:

```env
APP_URL=https://yourdomain.com
FRONTEND_URL=https://app.yourdomain.com

GOOGLE_CLIENT_ID=your-production-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-production-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/google/callback"
GOOGLE_AUTH_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

### CORS Configuration

Update `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://app.yourdomain.com',
        'https://yourdomain.com',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
```

### HTTPS Requirements

- Always use HTTPS in production
- Update Google Cloud Console redirect URIs to use HTTPS
- Ensure your SSL certificate is valid

### Security Checklist

- [ ] Use HTTPS for all URLs
- [ ] Set secure environment variables
- [ ] Enable CORS only for your domains
- [ ] Review OAuth scopes (only request what you need)
- [ ] Set up monitoring and logging
- [ ] Test the complete flow in production
- [ ] Keep your client secret secure (never commit to Git)

## Troubleshooting

### Common Issues

#### "redirect_uri_mismatch" Error

**Problem**: The redirect URI doesn't match what's configured in Google Cloud Console.

**Solution**:
- Check the exact redirect URI in your `.env` file
- Ensure it matches one of the authorized redirect URIs in Google Cloud Console
- Include the protocol (http/https) and don't add trailing slashes

#### "invalid_client" Error

**Problem**: Client ID or Client Secret is incorrect.

**Solution**:
- Double-check your credentials in `.env`
- Ensure you're using the correct credentials for your environment
- Verify the credentials in Google Cloud Console

#### "access_denied" Error

**Problem**: User denied permission or the app isn't verified.

**Solution**:
- Users can still use the app by clicking "Advanced" > "Go to [App Name]"
- Submit your app for verification in Google Cloud Console
- Ensure your OAuth consent screen is properly configured

#### Users Not Being Created

**Problem**: Users aren't automatically created after Google login.

**Solution**:
- Check `GOOGLE_AUTO_CREATE_USERS=true` in `.env`
- Ensure your User model's `$fillable` includes: `name`, `email`, `email_verified_at`
- Check Laravel logs for errors

#### Token Expired Issues

**Problem**: Access token expires quickly.

**Solution**:
- Enable refresh tokens: `GOOGLE_ENABLE_REFRESH_TOKEN=true`
- Set access type: `GOOGLE_ACCESS_TYPE=offline`
- Use the `/api/auth/google/refresh` endpoint to get new tokens

### Debug Mode

Enable debug logging:

```php
// In your controller or service
\Log::debug('Google OAuth Data', [
    'user_data' => $googleUserData,
    'token' => $token,
]);
```

### Testing Locally with HTTPS

Use Laravel Valet or:

```bash
php artisan serve --host=localhost --port=8000
```

Then use ngrok for HTTPS:

```bash
ngrok http 8000
```

Update your redirect URI to the ngrok URL.

## Additional Resources

- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Package README](README.md)
- [Frontend Integration Examples](examples/frontend-integration.md)

## Support

For issues and questions:
- Check the [GitHub Issues](https://github.com/eduardoks98/google-auth/issues)
- Review the documentation
- Contact the maintainers

## Next Steps

1. Customize the user creation logic if needed
2. Add additional user data fields
3. Implement frontend components
4. Add user profile pages
5. Set up production environment
6. Test thoroughly before going live
