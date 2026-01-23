# Changelog

All notable changes to `eduardoks98/facebook-auth` will be documented in this file.

## [1.0.0] - 2024-01-01

### Added
- Initial release
- Facebook OAuth2 authentication flow using League OAuth2 Facebook
- Integration with Laravel Sanctum for token-based authentication
- Facebook Graph API v19.0 support
- User profile management (email, name, avatar, facebook_id)
- Automatic user creation or linking by email
- Configurable OAuth scopes (email, public_profile, etc.)
- Configurable user fields from Graph API
- Frontend redirect support with token parameter
- FacebookUser model for storing Facebook data
- Migration for facebook_users table
- FacebookAuthService for handling OAuth flow
- FacebookAuthController with endpoints:
  - GET /api/facebook-auth/redirect - Get authorization URL
  - GET /api/facebook-auth/callback - Handle OAuth callback
  - GET /api/facebook-auth/profile - Get Facebook profile (authenticated)
  - DELETE /api/facebook-auth/disconnect - Disconnect Facebook account (authenticated)
- Comprehensive logging support
- Configuration file with all Facebook settings
- Complete documentation and integration guides
- Unit and feature tests
- Support for Laravel 10.x, 11.x, and 12.x
- Support for PHP 8.1, 8.2, and 8.3

### Security
- OAuth state parameter for CSRF protection
- Secure access token storage
- Access tokens hidden in model serialization
- Token expiration support
- Secure password generation for auto-created users

### Documentation
- Complete README with installation and usage instructions
- Integration guide for frontend frameworks (React, Vue, Angular)
- Example code for common use cases
- API endpoint documentation
- Configuration reference
- Security best practices
