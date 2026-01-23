# Google Auth Package - Installation Checklist

Use this checklist to ensure proper installation and configuration of the Google Auth package.

## Pre-Installation

- [ ] PHP 8.1, 8.2, or 8.3 installed
- [ ] Laravel 10.x, 11.x, or 12.x installed
- [ ] Laravel Sanctum installed and configured
- [ ] Database configured and accessible
- [ ] Composer available

## Google Cloud Console Setup

- [ ] Created Google Cloud project
- [ ] Enabled Google+ API or People API
- [ ] Configured OAuth consent screen
  - [ ] Set app name
  - [ ] Set user support email
  - [ ] Set developer contact email
  - [ ] Added scopes (openid, profile, email)
- [ ] Created OAuth 2.0 credentials
  - [ ] Created web application client
  - [ ] Added authorized redirect URIs
    - [ ] Development: `http://localhost:8000/api/auth/google/callback`
    - [ ] Production: `https://yourdomain.com/api/auth/google/callback`
- [ ] Copied Client ID and Client Secret

## Package Installation

- [ ] Installed via Composer
  ```bash
  composer require eduardoks98/google-auth
  ```

- [ ] Published configuration
  ```bash
  php artisan vendor:publish --tag=google-auth-config
  ```

- [ ] Published migrations
  ```bash
  php artisan vendor:publish --tag=google-auth-migrations
  ```

- [ ] Ran migrations
  ```bash
  php artisan migrate
  ```

## Configuration

### Environment Variables

- [ ] Added to `.env` file:
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

- [ ] Verified credentials are correct
- [ ] No spaces or quotes around values
- [ ] URLs are correct for environment

### User Model

- [ ] Added `HasGoogleAuth` trait to User model
  ```php
  use Eduardoks98\GoogleAuth\Traits\HasGoogleAuth;

  class User extends Authenticatable
  {
      use HasApiTokens, HasGoogleAuth;
  }
  ```

- [ ] Made `password` field nullable if needed
  ```php
  protected $fillable = [
      'name',
      'email',
      'password', // nullable
      'email_verified_at',
  ];
  ```

### CORS Configuration

- [ ] Updated `config/cors.php`
  ```php
  'allowed_origins' => [
      'http://localhost:3000',
      'https://yourdomain.com',
  ],
  'supports_credentials' => true,
  ```

## Database Verification

- [ ] `google_users` table exists
- [ ] Table has all required columns
  - [ ] id (UUID)
  - [ ] user_id (foreign key)
  - [ ] google_id (unique)
  - [ ] email (indexed)
  - [ ] name, given_name, family_name
  - [ ] picture, locale
  - [ ] access_token, refresh_token
  - [ ] expires_in, token_type
  - [ ] last_login_at
  - [ ] timestamps

- [ ] Foreign key constraint on user_id exists
- [ ] Indexes created properly

## API Routes Verification

- [ ] Routes registered automatically
- [ ] Verified routes exist:
  ```bash
  php artisan route:list | grep google
  ```

Expected routes:
- [ ] `GET api/auth/google/redirect`
- [ ] `GET api/auth/google/callback`
- [ ] `GET api/auth/google/profile`
- [ ] `POST api/auth/google/refresh`
- [ ] `DELETE api/auth/google/revoke`

## Testing

### Manual Testing

- [ ] Test authorization URL generation
  ```bash
  curl -X GET http://localhost:8000/api/auth/google/redirect \
    -H "Accept: application/json"
  ```

- [ ] Received valid response with authorization URL
- [ ] Opened authorization URL in browser
- [ ] Successfully logged in to Google
- [ ] Granted permissions
- [ ] Redirected back to callback URL
- [ ] Received token in response or redirect

### Authenticated Endpoints

- [ ] Test profile endpoint
  ```bash
  curl -X GET http://localhost:8000/api/auth/google/profile \
    -H "Authorization: Bearer YOUR_TOKEN"
  ```

- [ ] Test refresh endpoint
  ```bash
  curl -X POST http://localhost:8000/api/auth/google/refresh \
    -H "Authorization: Bearer YOUR_TOKEN"
  ```

### Database Verification

- [ ] Check GoogleUser record created
  ```sql
  SELECT * FROM google_users;
  ```

- [ ] Check User record created or linked
  ```sql
  SELECT * FROM users WHERE id IN (SELECT user_id FROM google_users);
  ```

- [ ] Verify token expiration calculation works
- [ ] Verify last_login_at updated

## Frontend Integration

### React

- [ ] Created login button component
- [ ] Implemented redirect to `/api/auth/google/redirect`
- [ ] Created callback handler component
- [ ] Extracts token from URL parameters
- [ ] Stores token in localStorage or state
- [ ] Sets Authorization header for API requests

### Vue.js

- [ ] Created login button component
- [ ] Implemented redirect logic
- [ ] Created callback page
- [ ] Token storage implemented
- [ ] Axios interceptor configured

### Other Frameworks

- [ ] Login flow implemented
- [ ] Callback handling implemented
- [ ] Token storage implemented
- [ ] API client configured

## Security Checklist

- [ ] HTTPS enabled in production
- [ ] Environment variables not committed to Git
- [ ] `.env` file in `.gitignore`
- [ ] Client secret kept secure
- [ ] CORS configured correctly
- [ ] Only necessary scopes requested
- [ ] State parameter validated
- [ ] Token expiration handled
- [ ] Error messages sanitized

## Production Deployment

- [ ] Updated Google Cloud Console redirect URIs for production
- [ ] Updated `.env` with production values
  - [ ] `APP_URL`
  - [ ] `FRONTEND_URL`
  - [ ] `GOOGLE_REDIRECT_URI`
  - [ ] Production credentials if different
- [ ] HTTPS certificate installed and valid
- [ ] Database migrated on production
- [ ] Configuration cached
  ```bash
  php artisan config:cache
  ```
- [ ] Routes cached
  ```bash
  php artisan route:cache
  ```

## Performance Optimization

- [ ] Enabled OPcache
- [ ] Database indexes verified
- [ ] Query optimization checked
- [ ] Caching configured
- [ ] Rate limiting configured

## Monitoring & Logging

- [ ] Laravel logs configured
- [ ] Error tracking enabled (Sentry, Bugsnag, etc.)
- [ ] Monitoring OAuth failures
- [ ] Monitoring token refresh patterns
- [ ] Tracking login success rate

## Documentation

- [ ] Read [README.md](README.md)
- [ ] Read [SETUP_GUIDE.md](SETUP_GUIDE.md)
- [ ] Reviewed [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- [ ] Checked [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- [ ] Reviewed [Frontend Integration Examples](examples/frontend-integration.md)

## Common Issues Resolution

### Issue: "redirect_uri_mismatch"

- [ ] Verified exact redirect URI matches Google Console
- [ ] Checked for trailing slashes
- [ ] Verified HTTP vs HTTPS
- [ ] Checked port number if using non-standard ports

### Issue: "invalid_client"

- [ ] Verified client ID is correct
- [ ] Verified client secret is correct
- [ ] Checked for extra spaces in `.env`
- [ ] Cleared configuration cache
  ```bash
  php artisan config:clear
  ```

### Issue: Users not being created

- [ ] Verified `GOOGLE_AUTO_CREATE_USERS=true`
- [ ] Checked User model `$fillable` includes necessary fields
- [ ] Verified database permissions
- [ ] Checked Laravel logs for errors

### Issue: Tokens not refreshing

- [ ] Verified `GOOGLE_ENABLE_REFRESH_TOKEN=true`
- [ ] Verified `GOOGLE_ACCESS_TYPE=offline`
- [ ] Checked that refresh_token is stored
- [ ] Cleared old tokens and re-authenticated

## Post-Installation Tasks

- [ ] Created documentation for team
- [ ] Set up monitoring and alerts
- [ ] Configured backup strategy
- [ ] Planned for token rotation
- [ ] Created runbook for common issues
- [ ] Set up staging environment testing

## Support & Resources

- [ ] GitHub repository bookmarked
- [ ] Google OAuth documentation reviewed
- [ ] Laravel Sanctum documentation reviewed
- [ ] Team trained on OAuth flow
- [ ] Emergency contacts documented

## Final Verification

- [ ] Complete OAuth flow works end-to-end
- [ ] Users can log in successfully
- [ ] Tokens are created and validated
- [ ] User data is synced correctly
- [ ] Refresh token flow works
- [ ] Revoke access works
- [ ] Error handling works as expected
- [ ] Frontend integration complete
- [ ] Production environment tested
- [ ] Backup and recovery tested

---

## Sign-off

**Installed by:** ___________________________
**Date:** ___________________________
**Environment:** ☐ Development ☐ Staging ☐ Production
**Version:** 1.0.0

**Notes:**
_____________________________________________
_____________________________________________
_____________________________________________

**Issues encountered:**
_____________________________________________
_____________________________________________
_____________________________________________

**Resolution:**
_____________________________________________
_____________________________________________
_____________________________________________

---

## Quick Commands Reference

```bash
# Installation
composer require eduardoks98/google-auth
php artisan vendor:publish --tag=google-auth-config
php artisan vendor:publish --tag=google-auth-migrations
php artisan migrate

# Verification
php artisan route:list | grep google
php artisan tinker
>>> \Eduardoks98\GoogleAuth\Models\GoogleUser::count()

# Troubleshooting
php artisan config:clear
php artisan cache:clear
php artisan route:clear
tail -f storage/logs/laravel.log

# Testing
curl -X GET http://localhost:8000/api/auth/google/redirect -H "Accept: application/json"
```

---

**Installation Complete! 🎉**

If you've checked all items, your Google Auth package is properly installed and configured.

For support, refer to the documentation or open an issue on GitHub.
