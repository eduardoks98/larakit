# Package Overview - eduardoks98/microsoft-auth

Complete Microsoft Azure AD OAuth 2.0 authentication package for Laravel.

## 📦 Package Information

- **Name**: eduardoks98/microsoft-auth
- **Version**: 1.0.0
- **License**: MIT
- **Author**: Eduardo Steffens
- **Dependencies**:
  - thenetworg/oauth2-azure ^2.0
  - laravel/sanctum ^3.0|^4.0
  - eduardoks98/base-api ^1.0
  - eduardoks98/auth ^1.0

## 🎯 Features

### Authentication
- ✅ Microsoft Azure AD OAuth 2.0 integration
- ✅ Support for multiple tenant types (common, organizations, consumers, specific)
- ✅ Automatic user creation and linking
- ✅ Email verification via Microsoft
- ✅ Sanctum token-based API authentication

### Microsoft Integration
- ✅ Microsoft Graph API integration
- ✅ User profile sync (name, email, job title, etc.)
- ✅ Token management (access + refresh tokens)
- ✅ Automatic token refresh
- ✅ Office 365 ready
- ✅ Azure AD B2B support

### Security
- ✅ CSRF protection via state parameter
- ✅ Token encryption and secure storage
- ✅ Middleware for token validation
- ✅ OAuth 2.0 best practices
- ✅ Laravel Sanctum integration

### Developer Experience
- ✅ Comprehensive documentation
- ✅ Usage examples and integration guides
- ✅ PHPUnit tests
- ✅ Azure AD setup guide
- ✅ API testing guide
- ✅ FAQ and troubleshooting

## 📁 Package Structure

```
microsoft-auth/
├── config/
│   └── microsoft.php                    # Configuration file
├── database/
│   └── migrations/
│       └── 2024_01_24_000001_create_microsoft_users_table.php
├── routes/
│   └── api.php                          # API routes
├── src/
│   ├── Enums/
│   │   └── MicrosoftTenant.php         # Tenant type enum
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── MicrosoftAuthController.php  # OAuth controller
│   │   └── Middleware/
│   │       └── EnsureMicrosoftTokenIsValid.php  # Token validation middleware
│   ├── Models/
│   │   └── MicrosoftUser.php           # Microsoft user model
│   ├── Services/
│   │   └── MicrosoftAuthService.php    # Core OAuth service
│   └── MicrosoftAuthServiceProvider.php # Service provider
├── tests/
│   ├── Feature/
│   │   └── MicrosoftAuthTest.php       # Feature tests
│   └── Unit/
│       └── MicrosoftAuthServiceTest.php # Unit tests
├── .env.example                         # Environment variables example
├── .gitignore                           # Git ignore rules
├── API_TESTING.md                       # API testing guide
├── AZURE_AD_SETUP.md                    # Azure AD configuration guide
├── CHANGELOG.md                         # Version history
├── composer.json                        # Composer dependencies
├── FAQ.md                               # Frequently asked questions
├── INTEGRATION_GUIDE.md                 # Integration with existing apps
├── LICENSE                              # MIT License
├── phpunit.xml                          # PHPUnit configuration
├── README.md                            # Main documentation
└── USAGE_EXAMPLES.md                    # Code examples
```

## 🔧 Core Components

### 1. MicrosoftAuthService
**File**: `src/Services/MicrosoftAuthService.php`

Main service handling all OAuth operations:
- Authorization URL generation
- Token exchange (code → access token)
- User info retrieval from Microsoft Graph
- Token refresh
- Graph API requests
- User creation and linking

### 2. MicrosoftAuthController
**File**: `src/Http/Controllers/MicrosoftAuthController.php`

Handles HTTP requests for OAuth flow:
- `redirect()` - Initiates OAuth flow
- `callback()` - Handles OAuth callback
- `me()` - Get current Microsoft user
- `refresh()` - Refresh access token
- `unlink()` - Unlink Microsoft account

### 3. MicrosoftUser Model
**File**: `src/Models/MicrosoftUser.php`

Eloquent model storing Microsoft account data:
- User profile (name, email, UPN, etc.)
- Work information (job title, office, phone)
- OAuth tokens (access, refresh)
- Token expiration tracking
- Relationships with User model

### 4. EnsureMicrosoftTokenIsValid Middleware
**File**: `src/Http/Middleware/EnsureMicrosoftTokenIsValid.php`

Middleware ensuring valid Microsoft tokens:
- Checks if user has Microsoft account linked
- Validates token expiration
- Automatically refreshes expired tokens
- Attaches Microsoft user to request

### 5. MicrosoftTenant Enum
**File**: `src/Enums/MicrosoftTenant.php`

Enum for tenant types:
- `COMMON` - Multi-tenant + personal accounts
- `ORGANIZATIONS` - Work/school only
- `CONSUMERS` - Personal accounts only

### 6. Configuration
**File**: `config/microsoft.php`

Comprehensive configuration options:
- OAuth credentials
- Tenant configuration
- Scopes
- Redirect URIs
- Token settings
- User model
- Graph API settings

## 🛣️ API Routes

### Public Routes
```
GET  /api/auth/microsoft/redirect   - Get authorization URL
GET  /api/auth/microsoft/callback   - OAuth callback handler
```

### Protected Routes (require Sanctum authentication)
```
GET  /api/auth/microsoft/me         - Get current Microsoft user
POST /api/auth/microsoft/refresh    - Refresh access token
POST /api/auth/microsoft/unlink     - Unlink Microsoft account
```

## 🗄️ Database Schema

### microsoft_users Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users table |
| microsoft_id | string | Microsoft unique identifier |
| email | string | Primary email |
| name | string | Display name |
| given_name | string | First name |
| surname | string | Last name |
| user_principal_name | string | UPN (username@domain) |
| job_title | string | Job title |
| office_location | string | Office location |
| mobile_phone | string | Mobile phone |
| business_phones | json | Business phones array |
| preferred_language | string | Preferred language |
| avatar_url | string | Avatar URL |
| tenant_id | string | Azure AD tenant ID |
| access_token | text | OAuth access token |
| refresh_token | text | OAuth refresh token |
| token_expires_at | timestamp | Token expiration |
| last_login_at | timestamp | Last login timestamp |
| created_at | timestamp | Record creation |
| updated_at | timestamp | Record update |

**Indexes**:
- `microsoft_id` (unique)
- `email`
- `user_principal_name`
- `user_id, microsoft_id`
- `tenant_id`

## 🔐 Security Features

1. **CSRF Protection**
   - State parameter validation
   - Session-based state storage

2. **Token Security**
   - Tokens hidden in API responses
   - Secure storage in database
   - Auto-refresh before expiration

3. **OAuth Best Practices**
   - Authorization code flow (not implicit)
   - HTTPS required in production
   - Redirect URI validation

4. **Laravel Integration**
   - Sanctum token authentication
   - Middleware protection
   - Policy-based authorization

## 📚 Documentation Files

1. **README.md** - Main documentation, installation, basic usage
2. **USAGE_EXAMPLES.md** - Practical code examples for common scenarios
3. **INTEGRATION_GUIDE.md** - Integrating with existing Laravel apps
4. **AZURE_AD_SETUP.md** - Step-by-step Azure AD configuration
5. **API_TESTING.md** - Testing endpoints with cURL, Postman, etc.
6. **FAQ.md** - Frequently asked questions and answers
7. **CHANGELOG.md** - Version history and changes
8. **PACKAGE_OVERVIEW.md** - This file, complete package overview

## 🧪 Testing

### Feature Tests
- OAuth redirect endpoint
- Configuration loading
- Model functionality

### Unit Tests
- Service authorization URL generation
- State parameter handling
- Token operations

### Test Configuration
- PHPUnit XML configured
- SQLite in-memory database
- Environment variables for testing

## 🚀 Quick Start

### Installation
```bash
composer require eduardoks98/microsoft-auth
php artisan vendor:publish --tag=microsoft-config
php artisan vendor:publish --tag=microsoft-migrations
php artisan migrate
```

### Configuration
```env
MICROSOFT_CLIENT_ID=your_client_id
MICROSOFT_CLIENT_SECRET=your_client_secret
MICROSOFT_TENANT=common
MICROSOFT_REDIRECT_URI=${APP_URL}/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=${FRONTEND_URL}/auth/callback
```

### Frontend Integration
```javascript
// Login button
window.location.href = '/api/auth/microsoft/redirect';

// Callback handler
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
localStorage.setItem('auth_token', token);
```

## 🔄 OAuth Flow Diagram

```
User                Frontend              Backend                Microsoft
 |                     |                     |                       |
 | Click Login         |                     |                       |
 |------------------->|                     |                       |
 |                     | GET /redirect       |                       |
 |                     |------------------->|                       |
 |                     |                     | Generate auth URL     |
 |                     |                     |--------------------->|
 |                     |                     | Auth URL              |
 |                     |                     |<---------------------|
 |                     | Auth URL            |                       |
 |                     |<-------------------|                       |
 | Redirect to Microsoft                     |                       |
 |-------------------------------------------->                       |
 |                     |                     |                       |
 | Login & Consent     |                     |                       |
 |<--------------------|---------------------|---------------------->|
 |                     |                     |                       |
 | Redirect to callback with code            |                       |
 |<-------------------------------------------|                       |
 |                     |                     |                       |
 |                     | GET /callback?code  |                       |
 |                     |------------------->|                       |
 |                     |                     | Exchange code         |
 |                     |                     |--------------------->|
 |                     |                     | Access token          |
 |                     |                     |<---------------------|
 |                     |                     | Get user info         |
 |                     |                     |--------------------->|
 |                     |                     | User data             |
 |                     |                     |<---------------------|
 |                     |                     | Create/update user    |
 |                     |                     | Create Sanctum token  |
 |                     | Redirect + token    |                       |
 |                     |<-------------------|                       |
 | Dashboard with token|                     |                       |
 |<-------------------|                     |                       |
```

## 📊 Tenant Types Matrix

| Tenant | Personal | Work/School | Multi-Tenant | Use Case |
|--------|----------|-------------|--------------|----------|
| `common` | ✅ | ✅ | ✅ | Most apps (recommended) |
| `organizations` | ❌ | ✅ | ✅ | B2B SaaS |
| `consumers` | ✅ | ❌ | ❌ | Consumer apps |
| `{tenant-id}` | ❌ | ✅ | ❌ | Single organization |

## 🔍 Common Use Cases

1. **Basic Authentication**
   - User login with Microsoft account
   - Auto-create user record
   - Issue Sanctum token

2. **Office 365 Integration**
   - Access user's emails
   - Read calendar events
   - Access OneDrive files

3. **Enterprise SSO**
   - Force Microsoft login for company domain
   - Sync user data from Azure AD
   - Multi-tenant support

4. **Hybrid Authentication**
   - Support both email/password and Microsoft
   - Link Microsoft account to existing users
   - Multiple authentication methods

## 🎯 Target Audience

- Laravel developers needing Microsoft/Azure AD authentication
- SaaS applications requiring Office 365 integration
- Enterprises implementing SSO with Azure AD
- Applications needing Microsoft Graph API access
- Multi-tenant applications with Azure AD

## 🤝 Dependencies

### Required
- PHP ^8.1|^8.2|^8.3
- Laravel ^10.0|^11.0|^12.0
- thenetworg/oauth2-azure ^2.0
- laravel/sanctum ^3.0|^4.0
- eduardoks98/base-api ^1.0
- eduardoks98/auth ^1.0

### Dev Dependencies
- orchestra/testbench ^8.0|^9.0
- pestphp/pest ^2.0
- pestphp/pest-plugin-laravel ^2.0

## 📝 Configuration Options

### Essential
- `client_id` - Azure AD application ID
- `client_secret` - Azure AD client secret
- `tenant` - Tenant type or ID
- `redirect_uri` - OAuth callback URL

### Optional
- `scopes` - Requested permissions
- `auto_create_user` - Auto-create users
- `store_tokens` - Store Microsoft tokens
- `user_model` - Custom user model
- `token_abilities` - Sanctum token abilities
- `frontend_redirect_url` - Frontend callback URL

## 🔗 External Resources

- [Microsoft Identity Platform](https://learn.microsoft.com/en-us/entra/identity-platform/)
- [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/overview)
- [OAuth 2.0 Specification](https://oauth.net/2/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [TheNetworg OAuth2 Azure](https://github.com/TheNetworg/oauth2-azure)

## 📈 Version History

### v1.0.0 (2024-01-24)
- Initial release
- Complete OAuth 2.0 implementation
- Microsoft Graph API integration
- Multi-tenant support
- Comprehensive documentation
- PHPUnit tests

## 📞 Support

- **Documentation**: See README.md and other .md files
- **Issues**: GitHub Issues
- **Questions**: FAQ.md
- **Examples**: USAGE_EXAMPLES.md

## 📄 License

MIT License - see LICENSE file for details

---

**Built with ❤️ by Eduardo Steffens**

For complete documentation, see individual .md files in the package root.
