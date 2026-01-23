# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-24

### Added
- Initial release of Google Auth package
- Google OAuth 2.0 authentication flow using The League's OAuth2 Google provider
- Automatic user creation and synchronization
- Sanctum token generation for authenticated users
- Refresh token support for long-lived access
- Google profile management endpoints
- Token revocation functionality
- Complete user data sync (name, email, avatar)
- CSRF protection using state parameter
- Configurable OAuth scopes
- Auto-create users option
- Auto-sync user data option
- Frontend redirect with token support
- HasGoogleAuth trait for User model
- Comprehensive documentation and examples
- Frontend integration examples (React, Vue, Angular, Vanilla JS)
- Postman collection for API testing
- Unit tests for core functionality

### Features
- `/api/auth/google/redirect` - Redirect to Google OAuth page
- `/api/auth/google/callback` - Handle OAuth callback
- `/api/auth/google/profile` - Get Google profile (authenticated)
- `/api/auth/google/refresh` - Refresh access token (authenticated)
- `/api/auth/google/revoke` - Revoke Google access (authenticated)

### Dependencies
- `league/oauth2-google`: ^4.0
- `eduardoks98/base-api`: ^1.0
- `eduardoks98/auth`: ^1.0
- Laravel 10.x, 11.x, or 12.x
- PHP 8.1, 8.2, or 8.3

### Documentation
- Complete README with installation and usage instructions
- Frontend integration examples for popular frameworks
- Postman collection for API testing
- Security considerations and best practices
- Configuration options documentation
