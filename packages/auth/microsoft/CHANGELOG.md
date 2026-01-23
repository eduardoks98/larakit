# Changelog

All notable changes to `eduardoks98/microsoft-auth` will be documented in this file.

## [1.0.0] - 2024-01-24

### Added
- Initial release
- Microsoft Azure AD OAuth 2.0 authentication
- Support for multiple tenant types (common, organizations, consumers, specific tenant)
- Microsoft Graph API integration
- Sanctum token-based authentication
- Automatic user creation and linking
- Token refresh support
- Middleware for token validation and auto-refresh
- MicrosoftUser model with comprehensive profile data
- MicrosoftAuthService for OAuth operations
- MicrosoftAuthController with redirect, callback, me, refresh, and unlink endpoints
- Database migration for microsoft_users table
- Configuration file with all Microsoft OAuth options
- Comprehensive documentation and usage examples
- PHPUnit tests for core functionality
- Support for Office 365 integration
- User photo retrieval from Microsoft Graph
- Email, calendar, and file access capabilities

### Features
- **OAuth Flow**: Complete authorization code flow implementation
- **Multi-Tenant Support**: Works with personal, work/school, and specific tenant accounts
- **Token Management**: Automatic token refresh and storage
- **User Sync**: Sync user profile data from Microsoft Graph
- **Graph API**: Helper methods for common Graph API operations
- **Security**: CSRF protection via state parameter validation
- **Flexibility**: Configurable scopes, tenant types, and user creation
- **Laravel Integration**: Seamless integration with Laravel Sanctum
- **Developer Experience**: Comprehensive documentation and examples

### Dependencies
- PHP ^8.1|^8.2|^8.3
- Laravel ^10.0|^11.0|^12.0
- thenetworg/oauth2-azure ^2.0
- laravel/sanctum ^3.0|^4.0
- eduardoks98/base-api ^1.0
- eduardoks98/auth ^1.0

### Documentation
- README.md with installation and configuration guide
- USAGE_EXAMPLES.md with practical code examples
- Inline documentation in all classes and methods
- Azure AD app registration guide
- Troubleshooting section

### Configuration
- Flexible tenant configuration
- Customizable scopes
- Frontend redirect URL configuration
- Token storage options
- Auto-create user option
- Custom user model support

### API Endpoints
- `GET /api/auth/microsoft/redirect` - Initiate OAuth flow
- `GET /api/auth/microsoft/callback` - Handle OAuth callback
- `GET /api/auth/microsoft/me` - Get current Microsoft user
- `POST /api/auth/microsoft/refresh` - Refresh access token
- `POST /api/auth/microsoft/unlink` - Unlink Microsoft account

### Middleware
- `microsoft.token` - Ensure valid Microsoft token and auto-refresh

### Models
- `MicrosoftUser` - Store Microsoft account data and tokens

### Services
- `MicrosoftAuthService` - Handle all OAuth and Graph API operations

### Enums
- `MicrosoftTenant` - Tenant type enumeration

### Testing
- Feature tests for OAuth flow
- Unit tests for service methods
- PHPUnit configuration
- Test environment setup

[1.0.0]: https://github.com/eduardoks98/microsoft-auth/releases/tag/v1.0.0
