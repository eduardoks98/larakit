# Frequently Asked Questions (FAQ)

Common questions and answers about the Microsoft Auth package.

## General Questions

### What is this package for?

This package enables Microsoft Azure AD OAuth 2.0 authentication in Laravel applications. It allows users to log in using their Microsoft accounts (Office 365, Azure AD, or personal Microsoft accounts) and integrates with Laravel Sanctum for API authentication.

### What Microsoft accounts are supported?

- **Personal Microsoft accounts** (outlook.com, hotmail.com, live.com, xbox.com)
- **Work or school accounts** (Office 365, Azure AD)
- **Azure AD B2B guest accounts**
- **Multi-tenant Azure AD accounts**

Configure the account type using the `MICROSOFT_TENANT` environment variable.

### Do I need an Azure account?

Yes, you need an Azure account to create an app registration. Azure offers a free tier that works perfectly for this purpose.

### Is this package free to use?

Yes, this package is open source (MIT License). However, depending on your Azure AD usage and Microsoft Graph API calls, you may incur Microsoft Azure costs.

## Installation & Setup

### How do I install this package?

```bash
composer require eduardoks98/microsoft-auth
php artisan vendor:publish --tag=microsoft-config
php artisan vendor:publish --tag=microsoft-migrations
php artisan migrate
```

See [README.md](README.md) for complete installation instructions.

### What environment variables do I need?

Minimum required:
```env
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_client_secret
MICROSOFT_TENANT=common
MICROSOFT_REDIRECT_URI=${APP_URL}/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=${FRONTEND_URL}/auth/callback
```

See [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) for Azure configuration.

### How do I get the Client ID and Client Secret?

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** → **App registrations** → **New registration**
3. After registration, copy the **Application (client) ID**
4. Create a **Client Secret** under **Certificates & secrets**
5. Copy the secret value immediately (you can't view it again)

See [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) for detailed guide.

### What is the redirect URI?

The redirect URI is where Microsoft redirects users after authentication. It must be:
- Registered in your Azure AD app registration
- Set in your `.env` file as `MICROSOFT_REDIRECT_URI`
- Exactly matching (including protocol, domain, port, and path)

Example: `https://your-app.com/api/auth/microsoft/callback`

## Authentication Flow

### How does the OAuth flow work?

1. User clicks "Login with Microsoft"
2. Frontend redirects to `/api/auth/microsoft/redirect`
3. Package redirects to Microsoft login page
4. User authenticates with Microsoft
5. Microsoft redirects back to `/api/auth/microsoft/callback` with authorization code
6. Package exchanges code for access token
7. Package retrieves user info from Microsoft Graph
8. Package creates/updates user and Microsoft account records
9. Package creates Sanctum token
10. User is redirected to frontend with token

### What happens on first login?

1. Microsoft user record is created with profile data
2. If `MICROSOFT_AUTO_CREATE_USER=true`, a User record is created
3. The two records are linked via `user_id`
4. Email is marked as verified (Microsoft verified it)
5. A Sanctum token is created
6. User is redirected to frontend with the token

### Can users login with both email/password and Microsoft?

Yes! The package works alongside Laravel's built-in authentication. Users can have both:
- Email/password authentication
- Microsoft OAuth authentication

See [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) for multi-authentication setup.

### How are users linked to Microsoft accounts?

The `microsoft_users` table has a `user_id` foreign key that references the `users` table. When a Microsoft user logs in:
- Package checks if a user with that email exists
- If yes, links the Microsoft account to existing user
- If no and `MICROSOFT_AUTO_CREATE_USER=true`, creates new user
- If no and `MICROSOFT_AUTO_CREATE_USER=false`, only creates Microsoft user record

## Configuration

### What tenant types are available?

| Tenant | Description | Use Case |
|--------|-------------|----------|
| `common` | Multi-tenant + personal accounts | **Recommended** - Maximum compatibility |
| `organizations` | Work/school accounts only | B2B SaaS applications |
| `consumers` | Personal accounts only | Consumer applications |
| `{tenant-id}` | Specific tenant only | Internal company applications |

Set via `MICROSOFT_TENANT` environment variable.

### What scopes should I request?

**Minimum (default):**
- `openid` - Required for OpenID Connect
- `profile` - User's basic profile
- `email` - User's email address
- `User.Read` - Read user profile via Graph API

**Optional (based on features):**
- `offline_access` - Refresh tokens for long-lived access
- `Mail.Read` - Read user's mail
- `Mail.Send` - Send mail as user
- `Calendars.Read` - Read user's calendar
- `Files.Read` - Read user's OneDrive files

Configure in `config/microsoft.php` or request dynamically in your code.

### Should I store Microsoft tokens?

**Yes** if you need to:
- Make Microsoft Graph API calls on behalf of the user
- Access user's emails, calendar, files, etc.
- Keep user data in sync with Microsoft

**No** if you only need:
- Basic authentication (login)
- User profile information (name, email)

Set via `MICROSOFT_STORE_TOKENS` environment variable.

### Can I use a custom User model?

Yes! Set the model in `config/microsoft.php`:

```php
'user_model' => App\Models\CustomUser::class,
```

Your model must use `Laravel\Sanctum\HasApiTokens` trait.

## Tokens & Security

### What tokens are created?

1. **Microsoft Access Token**: Used to access Microsoft Graph API
2. **Microsoft Refresh Token**: Used to get new access tokens
3. **Laravel Sanctum Token**: Used to authenticate API requests to your app

### How long do tokens last?

- **Microsoft Access Token**: 1 hour (configurable by Azure AD)
- **Microsoft Refresh Token**: 90 days of inactivity (configurable)
- **Sanctum Token**: Never expires (unless you implement expiration)

### Are tokens automatically refreshed?

Yes, if you use the `microsoft.token` middleware:

```php
Route::middleware(['auth:sanctum', 'microsoft.token'])->group(function () {
    // Microsoft token is automatically refreshed if expired
});
```

### How are tokens stored?

- Microsoft tokens are stored in `microsoft_users` table (encrypted at rest if you enable database encryption)
- Sanctum tokens are stored in `personal_access_tokens` table
- Tokens are hidden in API responses by default

### Is this package secure?

Yes, the package implements security best practices:
- **CSRF Protection**: State parameter validation
- **Token Encryption**: Tokens are hidden in API responses
- **HTTPS Required**: Use HTTPS in production
- **Secure OAuth Flow**: Follows Microsoft OAuth 2.0 best practices
- **Sanctum Integration**: Leverages Laravel Sanctum security

However, you must:
- Use HTTPS in production
- Keep client secret secure
- Rotate secrets regularly
- Implement rate limiting
- Monitor for suspicious activity

## Microsoft Graph API

### How do I call Microsoft Graph API?

```php
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;

$microsoftAuth = app(MicrosoftAuthService::class);
$microsoftUser = auth()->user()->microsoftUser;

$response = $microsoftAuth->graphApiRequest(
    $microsoftUser->access_token,
    'me/messages', // Endpoint
    'GET'
);
```

See [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) for more examples.

### What can I do with Microsoft Graph API?

- Read/send emails
- Access calendar events
- Read/write OneDrive files
- Get user's profile photo
- Access Teams data
- Read/write contacts
- Much more...

See [Microsoft Graph documentation](https://learn.microsoft.com/en-us/graph/overview) for full capabilities.

### Do I need additional permissions?

Yes, request specific scopes when initiating OAuth flow:

```php
$url = $microsoftAuth->getAuthorizationUrl([
    'scopes' => [
        'openid',
        'profile',
        'email',
        'User.Read',
        'Mail.Read',        // For reading emails
        'Calendars.Read',   // For reading calendar
    ],
]);
```

And add these permissions in Azure AD app registration.

## Troubleshooting

### Error: "AADSTS50011: Redirect URI mismatch"

**Cause**: Redirect URI in Azure AD doesn't match your `.env` file.

**Solution**:
1. Check `MICROSOFT_REDIRECT_URI` in `.env`
2. Verify it matches exactly in Azure AD (protocol, domain, port, path)
3. Check for trailing slashes
4. Ensure you're using the correct environment (dev/staging/prod)

### Error: "AADSTS65001: User has not consented"

**Cause**: Required permissions not granted.

**Solution**:
1. Add required permissions in Azure AD
2. Grant admin consent if needed
3. Ensure scopes are included in auth request

### Error: "Invalid state parameter"

**Cause**: Session expired or CSRF validation failed.

**Solution**:
1. Clear browser cache and session
2. Ensure session is working properly
3. Check if `SESSION_DRIVER` is configured correctly
4. Retry the OAuth flow

### Error: "Could not create or find user"

**Cause**: Microsoft doesn't provide email address or auto-create is disabled.

**Solution**:
1. Ensure `email` scope is requested
2. Check if `MICROSOFT_AUTO_CREATE_USER=true`
3. Verify User model configuration
4. Check logs for specific error

### Error: "Microsoft token expired and could not be refreshed"

**Cause**: Refresh token is invalid or expired.

**Solution**:
1. User must re-authenticate with Microsoft
2. Ensure `offline_access` scope is requested for refresh tokens
3. Check if refresh token exists in database

### Tokens not being stored

**Cause**: `MICROSOFT_STORE_TOKENS` is false or database issue.

**Solution**:
1. Set `MICROSOFT_STORE_TOKENS=true` in `.env`
2. Check database permissions
3. Verify migration ran successfully
4. Check logs for database errors

### User profile data not syncing

**Cause**: Missing scopes or Graph API permissions.

**Solution**:
1. Request `User.Read` scope
2. Add `User.Read` permission in Azure AD
3. Grant admin consent if required
4. Check Graph API response in logs

## Features & Customization

### Can I customize the user creation logic?

Yes, listen to model events:

```php
use Eduardoks98\MicrosoftAuth\Models\MicrosoftUser;

MicrosoftUser::creating(function ($microsoftUser) {
    // Custom logic before creating
});

MicrosoftUser::created(function ($microsoftUser) {
    // Custom logic after creating
    Mail::to($microsoftUser->email)->send(new WelcomeEmail());
});
```

Or disable auto-create and handle manually:

```php
// config/microsoft.php
'auto_create_user' => false,
```

See [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) for examples.

### Can I add custom fields to microsoft_users table?

Yes, create a new migration:

```php
Schema::table('microsoft_users', function (Blueprint $table) {
    $table->string('department')->nullable();
    $table->string('custom_field')->nullable();
});
```

Then update the model's `$fillable` array.

### Can I use this with multi-tenancy?

Yes! Store the Azure tenant ID in your organization/tenant model and verify users belong to the correct tenant:

```php
$organization->azure_tenant_id === $microsoftUser->tenant_id
```

See [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) for multi-tenant examples.

### Can I implement SSO (Single Sign-On)?

Yes! Force users from specific domains to use Microsoft authentication:

```php
if (str_ends_with($email, '@yourcompany.com')) {
    // Redirect to Microsoft OAuth
    return redirect('/api/auth/microsoft/redirect');
}
```

### Can I sync user data from Microsoft?

Yes, create a scheduled command:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('microsoft:sync-users')->daily();
}
```

See [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) for sync example.

## Best Practices

### Should I use personal or work accounts?

Depends on your use case:

- **Personal accounts** (`consumers`): Consumer apps, games, personal productivity
- **Work accounts** (`organizations`): B2B SaaS, enterprise apps, internal tools
- **Both** (`common`): Maximum flexibility, suitable for most apps

### How often should I refresh tokens?

- **Automatically**: Use `microsoft.token` middleware (recommended)
- **Manually**: Check `isTokenExpired()` and refresh before Graph API calls
- **Scheduled**: Refresh all tokens daily via command

### Should I verify email addresses?

The package automatically marks emails as verified (`email_verified_at`) because Microsoft has already verified them. However, you can add additional verification if needed.

### How do I handle user data privacy?

1. Store minimum necessary data
2. Implement data deletion on user request
3. Clear tokens when unlinking account
4. Follow GDPR/privacy regulations
5. Document what data you collect and why

### How do I test without real Microsoft accounts?

Use Microsoft's test accounts:
1. Create test users in Azure AD (if you have a tenant)
2. Use personal Microsoft accounts for testing
3. Mock the OAuth flow in unit tests

See [API_TESTING.md](API_TESTING.md) for testing examples.

## Performance

### Does this package impact performance?

Minimal impact:
- OAuth flow: Only during login (one-time per session)
- Token refresh: Automatic and cached
- Database queries: Optimized with proper indexes
- API calls: Only when you explicitly call Graph API

### Should I cache Microsoft data?

Yes, for frequently accessed data:

```php
$emails = Cache::remember("user.{$userId}.emails", 300, function () use ($microsoftAuth, $token) {
    return $microsoftAuth->graphApiRequest($token, 'me/messages');
});
```

### How many Graph API calls can I make?

Microsoft Graph has rate limits:
- **Default**: 10,000 requests per 10 minutes per app
- **Per user**: Varies by endpoint

Implement caching and rate limiting to stay within limits.

## Support & Community

### Where can I get help?

1. Check this FAQ
2. Read [README.md](README.md)
3. Review [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
4. Check [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
5. Search existing issues on GitHub
6. Create a new issue with detailed information

### How do I report a bug?

Create an issue on GitHub with:
1. Laravel version
2. Package version
3. PHP version
4. Steps to reproduce
5. Expected vs actual behavior
6. Error messages/logs
7. Relevant configuration

### How do I request a feature?

Create an issue on GitHub with:
1. Feature description
2. Use case
3. Example implementation (if possible)
4. Why this would be useful

### Can I contribute?

Yes! Contributions are welcome:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## Migration & Upgrades

### How do I migrate from another OAuth package?

1. Install this package
2. Run migrations to create `microsoft_users` table
3. Migrate existing data:
   ```php
   // Map your old OAuth data to MicrosoftUser model
   MicrosoftUser::create([
       'user_id' => $oldRecord->user_id,
       'microsoft_id' => $oldRecord->provider_id,
       'email' => $oldRecord->email,
       // ... other fields
   ]);
   ```
4. Update your frontend to use new endpoints
5. Test thoroughly before going live

### How do I upgrade to a new version?

```bash
composer update eduardoks98/microsoft-auth
php artisan vendor:publish --tag=microsoft-config --force
php artisan migrate
```

Check CHANGELOG.md for breaking changes.

### Will my existing users need to re-authenticate?

Generally no, unless:
- You change the Azure AD app registration
- You add new required scopes
- Tokens have expired and can't be refreshed
- You migrate to a different tenant type

## Production Deployment

### What should I check before going to production?

- [ ] Use HTTPS for all URLs
- [ ] Rotate Azure AD client secret
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper error handling
- [ ] Implement rate limiting
- [ ] Set up monitoring and logging
- [ ] Test all OAuth flows thoroughly
- [ ] Verify redirect URIs are correct
- [ ] Test with real Microsoft accounts
- [ ] Review security best practices
- [ ] Set up proper session driver (Redis, Memcached)
- [ ] Configure queue driver for async operations

### How do I handle multiple environments?

**Option 1**: One Azure app per environment
```env
# .env.dev
MICROSOFT_CLIENT_ID=dev-client-id

# .env.staging
MICROSOFT_CLIENT_ID=staging-client-id

# .env.production
MICROSOFT_CLIENT_ID=prod-client-id
```

**Option 2**: One Azure app with multiple redirect URIs
- Add all redirect URIs in Azure AD
- Use same client ID/secret across environments
- Redirect URIs resolve based on `APP_URL`

### How do I monitor OAuth errors?

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Azure AD sign-in logs: Azure Portal → Azure AD → Sign-in logs
3. Set up error tracking (Sentry, Bugsnag, etc.)
4. Monitor failed authentication attempts
5. Set up alerts for unusual activity

## Additional Resources

- [Microsoft Identity Platform Docs](https://learn.microsoft.com/en-us/entra/identity-platform/)
- [OAuth 2.0 Specification](https://oauth.net/2/)
- [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/overview)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [TheNetworg OAuth2 Azure](https://github.com/TheNetworg/oauth2-azure)
