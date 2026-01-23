# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Security Features

This package implements several security measures to protect your application:

### 1. CSRF Protection

- OAuth state parameter validation
- Session-based state storage
- State verification on callback

### 2. Token Security

- Access tokens stored encrypted in database
- Tokens hidden from model serialization
- Configurable token expiration
- Support for token rotation

### 3. Data Protection

- Secure password generation for auto-created users
- Email verification on account creation
- Sensitive data encryption at rest

### 4. API Security

- Rate limiting support
- Input validation on all endpoints
- Proper error handling without information leakage

## Best Practices

### Environment Variables

Never commit sensitive credentials to version control:

```env
# ❌ Never commit these values
FACEBOOK_APP_ID=123456789
FACEBOOK_APP_SECRET=abc123xyz789

# ✅ Use .env and keep it in .gitignore
```

### HTTPS Only

Always use HTTPS in production:

```php
// In AppServiceProvider
public function boot()
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### Token Storage

#### Backend (Server-side)
```php
// ✅ Store in database with encryption
protected $casts = [
    'access_token' => 'encrypted',
];
```

#### Frontend (Client-side)

```javascript
// ❌ Bad - vulnerable to XSS
localStorage.setItem('token', token);

// ✅ Better - httpOnly cookie (set from backend)
// Set-Cookie: token=xxx; HttpOnly; Secure; SameSite=Strict

// ✅ Acceptable - if XSS is mitigated
sessionStorage.setItem('token', token); // Cleared on browser close
```

### Input Validation

Always validate input before processing:

```php
$request->validate([
    'code' => 'required|string|max:500',
    'state' => 'nullable|string|max:100',
]);
```

### Rate Limiting

Implement rate limiting on authentication endpoints:

```php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/facebook-auth/redirect', [FacebookAuthController::class, 'redirect']);
    Route::get('/facebook-auth/callback', [FacebookAuthController::class, 'callback']);
});
```

### Error Handling

Don't expose sensitive information in error messages:

```php
// ❌ Bad - exposes internal details
catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()]);
}

// ✅ Good - generic message for users, detailed logging
catch (\Exception $e) {
    Log::error('Facebook auth failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);

    return response()->json([
        'error' => 'Authentication failed. Please try again.',
    ], 500);
}
```

### Scope Permissions

Only request necessary permissions:

```php
// ❌ Bad - requesting unnecessary permissions
'scopes' => [
    'email',
    'public_profile',
    'user_friends',
    'user_birthday',
    'user_location',
    'user_photos',
],

// ✅ Good - minimal permissions
'scopes' => [
    'email',
    'public_profile',
],
```

## Security Checklist

### Production Deployment

- [ ] All credentials stored in environment variables
- [ ] HTTPS enabled and enforced
- [ ] `APP_DEBUG=false` in production
- [ ] Rate limiting enabled
- [ ] CORS properly configured
- [ ] Database connections encrypted
- [ ] Logs don't contain sensitive data
- [ ] Error messages sanitized
- [ ] Facebook App in Live Mode (not Development)
- [ ] Valid Privacy Policy URL configured
- [ ] Valid Terms of Service URL configured

### Code Review

- [ ] No hardcoded credentials
- [ ] All user input validated
- [ ] State parameter verified on callback
- [ ] Access tokens encrypted at rest
- [ ] Tokens have appropriate expiration
- [ ] Error handling doesn't leak information
- [ ] SQL injection prevented (using Eloquent ORM)
- [ ] XSS prevented (using Laravel's built-in escaping)
- [ ] CSRF protected (using state parameter)

### Facebook App Configuration

- [ ] Valid OAuth Redirect URIs configured
- [ ] App Domains set correctly
- [ ] Client OAuth Login enabled
- [ ] Web OAuth Login enabled
- [ ] Enforce HTTPS enabled
- [ ] App Review completed for advanced permissions
- [ ] Data Deletion Request URL configured
- [ ] Privacy Policy URL accessible
- [ ] Terms of Service URL accessible

## Vulnerability Reporting

If you discover a security vulnerability, please send an email to security@example.com. All security vulnerabilities will be promptly addressed.

**Please do not:**
- Open a public GitHub issue
- Disclose the vulnerability publicly until it has been addressed
- Exploit the vulnerability

**Please do:**
- Provide detailed information about the vulnerability
- Include steps to reproduce the issue
- Suggest a fix if possible
- Allow reasonable time for the issue to be resolved

## Response Timeline

- **Initial Response:** Within 24-48 hours
- **Fix Development:** Within 7 days for critical issues
- **Disclosure:** After patch is released and users have had time to update

## Security Updates

Subscribe to security updates:
- Watch this repository on GitHub
- Follow [@eduardoks98](https://github.com/eduardoks98)
- Check CHANGELOG.md regularly

## Compliance

This package helps you comply with:

### GDPR (General Data Protection Regulation)

- User consent required for data collection (via OAuth flow)
- Right to be forgotten (disconnect feature)
- Data minimization (only request necessary scopes)
- Data portability (export user data via API)

### CCPA (California Consumer Privacy Act)

- User data access (profile endpoint)
- User data deletion (disconnect feature)
- Opt-out of data sale (not applicable - no data sold)

## Data Handling

### What Data We Store

```
facebook_users table:
- facebook_id (required for authentication)
- email (with user consent)
- name (with user consent)
- first_name (with user consent)
- last_name (with user consent)
- avatar_url (with user consent)
- access_token (encrypted, for API calls)
- metadata (additional data from Facebook)
```

### Data Retention

- User data retained while account is active
- Data deleted when user disconnects Facebook account
- Access tokens refreshed as needed
- Old tokens removed after expiration

### Data Deletion

Users can delete their data:

```bash
DELETE /api/facebook-auth/disconnect
```

Implement data deletion callback for Facebook:

```php
Route::post('/facebook/data-deletion', function (Request $request) {
    $signedRequest = $request->input('signed_request');

    // Verify signature and parse request
    // Delete user data
    // Return confirmation URL

    return response()->json([
        'url' => 'https://yourdomain.com/deletion-status/{id}',
        'confirmation_code' => '{unique-code}',
    ]);
});
```

## Third-Party Dependencies

This package depends on:

- `league/oauth2-facebook` - OAuth2 client
- `laravel/sanctum` - API authentication
- `illuminate/*` - Laravel framework

Keep dependencies updated:

```bash
composer update --with-dependencies
```

Check for security advisories:

```bash
composer audit
```

## Additional Resources

- [Facebook Platform Security](https://developers.facebook.com/docs/facebook-login/security)
- [OAuth 2.0 Security Best Practices](https://datatracker.ietf.org/doc/html/draft-ietf-oauth-security-topics)
- [Laravel Security](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

## License

This security policy is part of the eduardoks98/facebook-auth package and is licensed under the MIT License.
