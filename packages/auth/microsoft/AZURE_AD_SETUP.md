# Azure AD App Registration Guide

Step-by-step guide to configure Microsoft Azure AD for OAuth 2.0 authentication.

## Prerequisites

- Azure account (free tier works fine)
- Access to Azure Portal: https://portal.azure.com
- Application URL (development or production)

## Step 1: Create App Registration

1. Navigate to **Azure Portal** (https://portal.azure.com)
2. Search for **"Azure Active Directory"** or **"Microsoft Entra ID"**
3. In the left menu, click **"App registrations"**
4. Click **"+ New registration"**

## Step 2: Configure App Registration

### Basic Information

**Name**: Your application name (e.g., "My Laravel App")

**Supported account types**: Choose based on your needs:

- **Personal Microsoft accounts only**
  - Use this for consumer apps (Outlook, Xbox, Skype users)
  - Set `MICROSOFT_TENANT=consumers`

- **Accounts in any organizational directory (Any Azure AD directory - Multitenant)**
  - Use this for B2B SaaS apps
  - Set `MICROSOFT_TENANT=organizations`

- **Accounts in any organizational directory and personal Microsoft accounts**
  - **Recommended for most apps** - maximum compatibility
  - Set `MICROSOFT_TENANT=common`

- **Accounts in this organizational directory only (Single tenant)**
  - Use for internal company apps
  - Set `MICROSOFT_TENANT={your-tenant-id}`

**Redirect URI**:
- Platform: **Web**
- URI: `https://your-app.com/api/auth/microsoft/callback`
  - For development: `http://localhost:8000/api/auth/microsoft/callback`
  - Must match `MICROSOFT_REDIRECT_URI` in your .env file exactly

Click **"Register"**

## Step 3: Get Client ID and Tenant ID

After registration, you'll see the app overview page:

1. Copy **Application (client) ID**
   - This is your `MICROSOFT_CLIENT_ID`

2. Copy **Directory (tenant) ID**
   - Use this if you want single-tenant authentication
   - Set as `MICROSOFT_TENANT={this-id}` (optional)

## Step 4: Create Client Secret

1. In left menu, click **"Certificates & secrets"**
2. Click **"+ New client secret"**
3. Add description: "Laravel App Secret"
4. Choose expiration: **24 months** (recommended) or custom
5. Click **"Add"**
6. **IMPORTANT**: Copy the **Value** immediately
   - This is your `MICROSOFT_CLIENT_SECRET`
   - You cannot view this value again after leaving the page
   - If lost, you must create a new secret

## Step 5: Configure API Permissions

1. In left menu, click **"API permissions"**
2. You should see **"Microsoft Graph"** with **"User.Read"** already added
3. Click **"+ Add a permission"**
4. Select **"Microsoft Graph"**
5. Select **"Delegated permissions"**
6. Add the following permissions:

### Required Permissions (Minimum)
- ✓ **openid** - Sign in and read user profile
- ✓ **profile** - View users' basic profile
- ✓ **email** - View users' email address
- ✓ **User.Read** - Sign in and read user profile

### Optional Permissions (Based on Features)
- **offline_access** - Maintain access to data (for refresh tokens)
- **Mail.Read** - Read user mail
- **Mail.Send** - Send mail as a user
- **Calendars.Read** - Read user calendars
- **Calendars.ReadWrite** - Have full access to user calendars
- **Files.Read** - Read user files (OneDrive)
- **Files.ReadWrite** - Have full access to user files

7. Click **"Add permissions"**

### Admin Consent

Some permissions require admin consent:

- If you're an admin: Click **"Grant admin consent for [Your Organization]"**
- If not: Your organization's admin must approve these permissions

## Step 6: Configure Authentication Settings

1. In left menu, click **"Authentication"**
2. Under **"Platform configurations"** → **"Web"**
   - Verify redirect URI is correct
   - Add additional redirect URIs if needed (e.g., staging, production)

3. Under **"Implicit grant and hybrid flows"** (optional):
   - ❌ Do NOT check these boxes (we use authorization code flow)

4. Under **"Advanced settings"**:
   - ✓ **Allow public client flows**: **No**
   - ✓ **Enable the following mobile and desktop flows**: **No**

5. Click **"Save"**

## Step 7: Configure Token Settings (Optional)

1. In left menu, click **"Token configuration"**
2. Click **"+ Add optional claim"**
3. Token type: **ID**
4. Add claims you want in the ID token:
   - `email`
   - `family_name`
   - `given_name`
   - `upn`

5. Click **"Add"**

## Step 8: Update Your .env File

```env
# Copy these values from Azure Portal
MICROSOFT_CLIENT_ID=12345678-1234-1234-1234-123456789abc
MICROSOFT_CLIENT_SECRET=your_secret_value_here~1234567890

# Tenant configuration
MICROSOFT_TENANT=common

# Your application URLs
MICROSOFT_REDIRECT_URI=https://your-api.com/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=https://your-frontend.com/auth/callback

# Optional
MICROSOFT_GRAPH_VERSION=v1.0
MICROSOFT_AUTO_CREATE_USER=true
MICROSOFT_STORE_TOKENS=true
```

## Tenant Type Reference

| Tenant Value | Description | Use Case |
|-------------|-------------|----------|
| `common` | Multi-tenant + personal accounts | **Recommended** - Maximum compatibility |
| `organizations` | Multi-tenant (work/school only) | B2B SaaS apps |
| `consumers` | Personal accounts only | Consumer apps |
| `{tenant-id}` | Specific tenant only | Internal company apps |

## Common Redirect URIs

### Development
```
http://localhost:8000/api/auth/microsoft/callback
http://localhost:3000/auth/callback (frontend)
http://127.0.0.1:8000/api/auth/microsoft/callback
```

### Production
```
https://api.yourapp.com/api/auth/microsoft/callback
https://yourapp.com/auth/callback (frontend)
```

**Important**: Add ALL redirect URIs you'll use (dev, staging, production)

## Verification Checklist

- [ ] App registration created
- [ ] Client ID copied to .env
- [ ] Client secret created and copied to .env
- [ ] Redirect URI added and matches .env
- [ ] Required permissions added (openid, profile, email, User.Read)
- [ ] Optional permissions added based on features needed
- [ ] Admin consent granted (if required)
- [ ] Tenant type configured correctly
- [ ] .env file updated with all values
- [ ] Configuration published: `php artisan vendor:publish --tag=microsoft-config`
- [ ] Migrations run: `php artisan migrate`

## Testing Your Configuration

1. Start your Laravel app: `php artisan serve`
2. Visit: `http://localhost:8000/api/auth/microsoft/redirect`
3. You should be redirected to Microsoft login page
4. After login, check if you're redirected back with a token

## Troubleshooting

### Error: "AADSTS50011: Redirect URI mismatch"
- **Solution**: Ensure redirect URI in Azure AD exactly matches your .env file
- Check for trailing slashes, http vs https, port numbers

### Error: "AADSTS65001: User has not consented"
- **Solution**: Add required permissions in Azure AD
- Grant admin consent if needed
- Include required scopes in your auth request

### Error: "AADSTS700016: Application not found"
- **Solution**: Check your client ID is correct
- Ensure you're using the correct tenant

### Error: "AADSTS7000215: Invalid client secret"
- **Solution**: Client secret expired or incorrect
- Create new secret in Azure AD
- Update .env file

### Error: "AADSTS50020: User not from expected tenant"
- **Solution**: User's account type doesn't match tenant configuration
- Use `common` for maximum compatibility
- Or restrict to specific account types

## Security Best Practices

1. **Client Secret**:
   - Store securely (never commit to git)
   - Rotate regularly (every 6-12 months)
   - Use different secrets for dev/staging/production

2. **Redirect URIs**:
   - Only add URIs you control
   - Use HTTPS in production
   - Don't use wildcards

3. **Permissions**:
   - Request minimum necessary permissions
   - Add more only when needed
   - Document why each permission is required

4. **Tenant Configuration**:
   - Use specific tenant for internal apps
   - Use `organizations` or `consumers` to restrict account types
   - Use `common` only if you need both personal and work accounts

## Additional Resources

- [Microsoft Identity Platform Docs](https://learn.microsoft.com/en-us/entra/identity-platform/)
- [OAuth 2.0 Authorization Code Flow](https://learn.microsoft.com/en-us/entra/identity-platform/v2-oauth2-auth-code-flow)
- [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/overview)
- [Graph API Permissions](https://learn.microsoft.com/en-us/graph/permissions-reference)
- [Azure AD App Registration](https://portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps)

## Support

If you encounter issues:

1. Check Azure AD logs: Azure Portal → Azure AD → Sign-in logs
2. Check Laravel logs: `storage/logs/laravel.log`
3. Enable debug mode temporarily: `APP_DEBUG=true`
4. Review error messages carefully (AADSTS error codes)

## Multi-Environment Setup

### Development
```env
MICROSOFT_CLIENT_ID=dev-client-id
MICROSOFT_REDIRECT_URI=http://localhost:8000/api/auth/microsoft/callback
```

### Staging
```env
MICROSOFT_CLIENT_ID=staging-client-id
MICROSOFT_REDIRECT_URI=https://staging-api.yourapp.com/api/auth/microsoft/callback
```

### Production
```env
MICROSOFT_CLIENT_ID=prod-client-id
MICROSOFT_REDIRECT_URI=https://api.yourapp.com/api/auth/microsoft/callback
```

**Tip**: You can use the same app registration for all environments by adding multiple redirect URIs.
