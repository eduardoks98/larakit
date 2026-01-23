# Documentation Index

Complete documentation for the Facebook Auth package.

## Quick Links

- **New to the package?** Start with [QUICKSTART.md](QUICKSTART.md)
- **Need examples?** Check [INTEGRATION.md](INTEGRATION.md) and [EXAMPLES.md](EXAMPLES.md)
- **Having issues?** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Security concerns?** Read [SECURITY.md](SECURITY.md)
- **Command reference?** View [COMMANDS.md](COMMANDS.md)

## Documentation Files

### Getting Started

1. **[README.md](README.md)** - Main documentation
   - Overview and features
   - Installation instructions
   - Basic usage
   - Configuration options
   - API reference

2. **[QUICKSTART.md](QUICKSTART.md)** - 5-minute setup guide
   - Quick installation
   - Minimal configuration
   - Basic frontend integration
   - Testing the flow
   - Common issues

3. **[PACKAGE_SUMMARY.md](PACKAGE_SUMMARY.md)** - Implementation summary
   - Package structure
   - Feature checklist
   - Authentication flow diagram
   - Database schema
   - Version information

### Integration Guides

4. **[INTEGRATION.md](INTEGRATION.md)** - Frontend integration
   - React/Next.js examples
   - Vue.js examples
   - Angular examples
   - Fetch API usage
   - Axios configuration
   - Error handling
   - Complete React Context example

5. **[USER_MODEL.md](USER_MODEL.md)** - User model configuration
   - Basic setup
   - HasFacebookAccount trait
   - Helper methods
   - Eager loading
   - API resources
   - Blade templates
   - Observers
   - Scopes and accessors

### Advanced Usage

6. **[EXAMPLES.md](EXAMPLES.md)** - Advanced usage examples
   - Custom scopes
   - Linking accounts
   - Profile management
   - Middleware examples
   - Event listeners
   - Queue jobs
   - Testing examples
   - Best practices

7. **[COMMANDS.md](COMMANDS.md)** - Command reference
   - Installation commands
   - Development commands
   - Database commands
   - Testing commands
   - Tinker examples
   - API testing with cURL
   - Debugging commands
   - Production deployment

### Problem Solving

8. **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Troubleshooting guide
   - Installation issues
   - Authentication issues
   - Database issues
   - API issues
   - CORS errors
   - Facebook App issues
   - Configuration issues
   - Logging and debugging

9. **[SECURITY.md](SECURITY.md)** - Security documentation
   - Security features
   - Best practices
   - Security checklist
   - Vulnerability reporting
   - Compliance (GDPR, CCPA)
   - Data handling
   - Third-party dependencies

### Reference

10. **[CHANGELOG.md](CHANGELOG.md)** - Version history
    - Release notes
    - New features
    - Bug fixes
    - Breaking changes

11. **[LICENSE](LICENSE)** - MIT License
    - Terms and conditions
    - Copyright information

12. **[.env.example](.env.example)** - Environment variables
    - Required variables
    - Optional variables
    - Example values

## Documentation by Topic

### Installation & Setup
- [Quick Installation](QUICKSTART.md#1-installation)
- [Facebook App Setup](QUICKSTART.md#2-facebook-app-setup)
- [Environment Configuration](QUICKSTART.md#3-environment-configuration)
- [User Model Setup](QUICKSTART.md#4-update-user-model)

### Frontend Integration
- [React Integration](INTEGRATION.md#reactnextjs-example)
- [Vue Integration](INTEGRATION.md#vuejs-example)
- [Angular Integration](INTEGRATION.md#angular-example)
- [Complete Auth Context](INTEGRATION.md#complete-react-context-example)

### Backend Development
- [Service Usage](EXAMPLES.md#2-api-only-authentication)
- [Custom Controllers](EXAMPLES.md#3-custom-scopes)
- [Middleware](EXAMPLES.md#6-middleware-for-facebook-users)
- [Observers](USER_MODEL.md#observers)

### Testing
- [Running Tests](COMMANDS.md#testing)
- [Test Examples](EXAMPLES.md#9-testing)
- [Tinker Commands](COMMANDS.md#tinker-commands)

### Security
- [Best Practices](SECURITY.md#best-practices)
- [Token Security](SECURITY.md#token-security)
- [HTTPS Configuration](SECURITY.md#https-only)
- [Data Protection](SECURITY.md#data-protection)

### Troubleshooting
- [Installation Issues](TROUBLESHOOTING.md#installation-issues)
- [Authentication Issues](TROUBLESHOOTING.md#authentication-issues)
- [CORS Errors](TROUBLESHOOTING.md#issue-cors-errors)
- [Common Pitfalls](TROUBLESHOOTING.md#common-pitfalls)

## API Reference

### Endpoints

| Method | Endpoint | Auth | Documentation |
|--------|----------|------|---------------|
| GET | `/api/facebook-auth/redirect` | No | [README.md](README.md#1-redirect-to-facebook) |
| GET | `/api/facebook-auth/callback` | No | [README.md](README.md#2-handle-callback) |
| GET | `/api/facebook-auth/profile` | Yes | [README.md](README.md#get-facebook-profile) |
| DELETE | `/api/facebook-auth/disconnect` | Yes | [README.md](README.md#disconnect-facebook-account) |

### Classes

| Class | Type | Documentation |
|-------|------|---------------|
| FacebookAuthService | Service | [README.md](README.md#service-usage) |
| FacebookAuthController | Controller | [README.md](README.md#usage) |
| FacebookUser | Model | [USER_MODEL.md](USER_MODEL.md) |
| FacebookAuthServiceProvider | Provider | [PACKAGE_SUMMARY.md](PACKAGE_SUMMARY.md#key-classes) |

### Configuration

| File | Purpose | Documentation |
|------|---------|---------------|
| `config/facebook-auth.php` | Package config | [README.md](README.md#configuration) |
| `.env` | Environment vars | [.env.example](.env.example) |

## Learning Path

### Beginner
1. Read [QUICKSTART.md](QUICKSTART.md)
2. Follow the setup steps
3. Test with the example code
4. Read [README.md](README.md) for details

### Intermediate
1. Review [INTEGRATION.md](INTEGRATION.md)
2. Implement frontend integration
3. Read [USER_MODEL.md](USER_MODEL.md)
4. Configure your User model

### Advanced
1. Study [EXAMPLES.md](EXAMPLES.md)
2. Implement custom features
3. Review [SECURITY.md](SECURITY.md)
4. Optimize for production

## Getting Help

1. **Check documentation**
   - Search this index for your topic
   - Read relevant documentation files

2. **Check troubleshooting**
   - Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
   - Check logs: `tail -f storage/logs/laravel.log`

3. **Debug**
   - Use [COMMANDS.md](COMMANDS.md) for debugging commands
   - Enable debug mode: `APP_DEBUG=true`

4. **Ask for help**
   - Create GitHub issue
   - Include error messages
   - Share relevant code
   - Mention versions (PHP, Laravel, package)

## Quick Reference

### Essential Commands
```bash
# Install
composer require eduardoks98/facebook-auth

# Setup
php artisan vendor:publish --tag=facebook-auth-config
php artisan migrate

# Test
vendor/bin/pest

# Debug
php artisan tinker
tail -f storage/logs/laravel.log
```

### Essential Configuration
```env
FACEBOOK_APP_ID=your-app-id
FACEBOOK_APP_SECRET=your-app-secret
FACEBOOK_REDIRECT_URI="${APP_URL}/api/facebook-auth/callback"
FACEBOOK_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

### Essential Code
```php
// User model
public function facebookUser()
{
    return $this->hasOne(FacebookUser::class);
}

// Frontend (React)
const response = await fetch('/api/facebook-auth/redirect');
const { data } = await response.json();
window.location.href = data.authorization_url;
```

## File Structure

```
facebook-auth/
├── Documentation Files (10 markdown files)
│   ├── INDEX.md                  ← You are here
│   ├── README.md                 ← Start here
│   ├── QUICKSTART.md            ← 5-minute setup
│   ├── INTEGRATION.md           ← Frontend guide
│   ├── EXAMPLES.md              ← Advanced examples
│   ├── USER_MODEL.md            ← User model setup
│   ├── TROUBLESHOOTING.md       ← Problem solving
│   ├── SECURITY.md              ← Security guide
│   ├── COMMANDS.md              ← Command reference
│   ├── PACKAGE_SUMMARY.md       ← Implementation summary
│   └── CHANGELOG.md             ← Version history
│
├── Configuration Files
│   ├── composer.json            ← Package config
│   ├── phpunit.xml              ← Test config
│   ├── .env.example             ← Environment template
│   └── .gitignore               ← Git ignore rules
│
├── Source Code
│   ├── config/                  ← Package configuration
│   ├── database/                ← Migrations
│   ├── routes/                  ← API routes
│   ├── src/                     ← Main source code
│   └── tests/                   ← Test files
│
└── LICENSE                      ← MIT License
```

## Statistics

- **Total Files**: 26
- **Code Files**: 13 (PHP, JSON, XML)
- **Documentation Files**: 11 (Markdown)
- **Lines of Code**: ~2,000+
- **Lines of Documentation**: ~2,500+
- **Test Coverage**: Feature + Unit tests

## Version Information

- **Package Version**: 1.0.0
- **Laravel Support**: 10.x, 11.x, 12.x
- **PHP Support**: 8.1, 8.2, 8.3
- **Graph API Version**: v19.0
- **License**: MIT

---

**Need more help?** Start with [QUICKSTART.md](QUICKSTART.md) for a 5-minute setup guide!
