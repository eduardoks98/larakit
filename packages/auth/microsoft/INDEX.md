# Documentation Index

Complete index of all documentation files in the Microsoft Auth package.

## 📚 Documentation Structure

### Getting Started (Start Here!)

1. **[QUICKSTART.md](QUICKSTART.md)** ⚡
   - 5-minute setup guide
   - Minimal configuration
   - Basic frontend integration
   - Common issues and fixes
   - **Start here if you're new!**

2. **[README.md](README.md)** 📖
   - Complete package documentation
   - Installation instructions
   - Configuration guide
   - Basic usage
   - API endpoints
   - Security considerations

### Setup & Configuration

3. **[AZURE_AD_SETUP.md](AZURE_AD_SETUP.md)** ☁️
   - Step-by-step Azure AD app registration
   - Tenant types explained
   - Permissions configuration
   - Redirect URI setup
   - Security best practices
   - Troubleshooting Azure AD issues

4. **[PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md)** 📦
   - Complete package architecture
   - Component descriptions
   - Database schema
   - API routes
   - OAuth flow diagram
   - Dependencies and requirements

### Usage & Integration

5. **[USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)** 💡
   - Practical code examples
   - Common scenarios
   - Microsoft Graph API usage
   - Frontend integration (React, Vue)
   - Backend API examples
   - Error handling

6. **[INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)** 🔗
   - Integrating with existing apps
   - Custom user models
   - Multi-authentication setup
   - SSO implementation
   - Event-based integration
   - Migration from other OAuth packages

### Testing & Development

7. **[API_TESTING.md](API_TESTING.md)** 🧪
   - Testing with cURL
   - Postman collection
   - HTTPie examples
   - Testing scripts (Bash, Python, Node.js)
   - Integration testing
   - Debugging guide

8. **[FAQ.md](FAQ.md)** ❓
   - Frequently asked questions
   - Common issues and solutions
   - Best practices
   - Security considerations
   - Performance tips
   - Troubleshooting guide

### Reference

9. **[CHANGELOG.md](CHANGELOG.md)** 📝
   - Version history
   - Features added
   - Bug fixes
   - Breaking changes

10. **[LICENSE](LICENSE)** ⚖️
    - MIT License
    - Usage terms
    - Copyright information

## 📂 Code Documentation

### Source Files

Located in `src/` directory:

#### Services
- **MicrosoftAuthService.php**
  - Core OAuth operations
  - Token management
  - Graph API integration
  - User creation logic

#### Controllers
- **MicrosoftAuthController.php**
  - OAuth redirect endpoint
  - Callback handler
  - User info endpoint
  - Token refresh
  - Account unlinking

#### Models
- **MicrosoftUser.php**
  - Eloquent model
  - Token expiration checks
  - User relationships
  - Token management methods

#### Middleware
- **EnsureMicrosoftTokenIsValid.php**
  - Token validation
  - Auto-refresh logic
  - Request attribute injection

#### Enums
- **MicrosoftTenant.php**
  - Tenant type definitions
  - URL patterns
  - Helper methods

#### Service Provider
- **MicrosoftAuthServiceProvider.php**
  - Laravel service registration
  - Configuration publishing
  - Route registration
  - Middleware registration

### Configuration
- **config/microsoft.php**
  - All configuration options
  - Environment variables
  - Default values
  - Detailed comments

### Routes
- **routes/api.php**
  - OAuth endpoints
  - Protected routes
  - Route naming

### Migrations
- **database/migrations/2024_01_24_000001_create_microsoft_users_table.php**
  - Database schema
  - Indexes
  - Foreign keys

### Tests
- **tests/Feature/MicrosoftAuthTest.php**
  - Feature tests
  - OAuth flow tests
  - Endpoint tests

- **tests/Unit/MicrosoftAuthServiceTest.php**
  - Unit tests
  - Service method tests
  - Configuration tests

## 🎯 Quick Navigation

### By Task

#### I want to...

**Get started quickly**
→ [QUICKSTART.md](QUICKSTART.md)

**Set up Azure AD**
→ [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md)

**See code examples**
→ [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)

**Integrate with existing app**
→ [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)

**Test the API**
→ [API_TESTING.md](API_TESTING.md)

**Find answers**
→ [FAQ.md](FAQ.md)

**Understand architecture**
→ [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md)

**Read full docs**
→ [README.md](README.md)

### By Experience Level

#### Beginner
1. [QUICKSTART.md](QUICKSTART.md) - Get up and running
2. [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Configure Azure
3. [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Copy-paste examples
4. [FAQ.md](FAQ.md) - Common questions

#### Intermediate
1. [README.md](README.md) - Complete documentation
2. [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) - Advanced integration
3. [API_TESTING.md](API_TESTING.md) - Testing strategies
4. [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Architecture deep dive

#### Advanced
1. Source code in `src/` directory
2. [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Technical details
3. [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) - Customization
4. Tests in `tests/` directory

### By Use Case

#### Basic Authentication
- [QUICKSTART.md](QUICKSTART.md) - Setup
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Basic OAuth flow
- [FAQ.md](FAQ.md) - Troubleshooting

#### Office 365 Integration
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Graph API examples
- [README.md](README.md) - Scopes configuration
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Permissions setup

#### Enterprise SSO
- [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) - SSO implementation
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Tenant configuration
- [FAQ.md](FAQ.md) - Multi-tenant scenarios

#### Multi-Authentication
- [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) - Hybrid auth
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Account linking
- [FAQ.md](FAQ.md) - Best practices

## 📊 Package Statistics

- **Total Files**: 25
- **PHP Files**: 11
- **Documentation Files**: 9
- **Lines of Code**: ~1,300
- **Lines of Documentation**: ~3,800
- **Test Files**: 2

## 🔍 Search Guide

### Find information about...

**OAuth Flow**
- [README.md](README.md) - Overview
- [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Flow diagram
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Implementation

**Configuration**
- [README.md](README.md) - Configuration guide
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Azure configuration
- [FAQ.md](FAQ.md) - Configuration FAQs
- `config/microsoft.php` - All options

**Tenant Types**
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Detailed explanation
- [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Matrix comparison
- [FAQ.md](FAQ.md) - Which to use

**Security**
- [README.md](README.md) - Security section
- [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Security features
- [FAQ.md](FAQ.md) - Security best practices

**Tokens**
- [README.md](README.md) - Token management
- [FAQ.md](FAQ.md) - Token FAQs
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Token refresh
- `src/Services/MicrosoftAuthService.php` - Implementation

**Microsoft Graph API**
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - API examples
- [FAQ.md](FAQ.md) - Graph API FAQs
- [README.md](README.md) - Scopes

**Errors & Troubleshooting**
- [FAQ.md](FAQ.md) - Common errors
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Azure errors
- [API_TESTING.md](API_TESTING.md) - Debugging

**Testing**
- [API_TESTING.md](API_TESTING.md) - Complete testing guide
- [QUICKSTART.md](QUICKSTART.md) - Quick tests
- `tests/` - Unit and feature tests

## 📱 Support Resources

### Documentation
- All .md files in package root
- Inline code documentation
- Configuration comments

### Code Examples
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
- [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
- [QUICKSTART.md](QUICKSTART.md)
- [API_TESTING.md](API_TESTING.md)

### External Resources
- [Microsoft Identity Platform](https://learn.microsoft.com/en-us/entra/identity-platform/)
- [Microsoft Graph API](https://learn.microsoft.com/en-us/graph/overview)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [OAuth 2.0 Specification](https://oauth.net/2/)

## 🗺️ Learning Path

### Recommended Reading Order

1. **Day 1: Setup**
   - [QUICKSTART.md](QUICKSTART.md)
   - [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md)
   - Test basic OAuth flow

2. **Day 2: Integration**
   - [README.md](README.md)
   - [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
   - Implement in your app

3. **Day 3: Advanced Features**
   - [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
   - [API_TESTING.md](API_TESTING.md)
   - Customize for your needs

4. **Ongoing: Reference**
   - [FAQ.md](FAQ.md) - As needed
   - [PACKAGE_OVERVIEW.md](PACKAGE_OVERVIEW.md) - Deep dive
   - Source code - When customizing

## 📞 Getting Help

1. **Check FAQ**: [FAQ.md](FAQ.md)
2. **Search docs**: Use Ctrl+F in relevant .md files
3. **Review examples**: [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
4. **Check source**: Read code in `src/` directory
5. **Create issue**: GitHub Issues (with details!)

## 🎓 Additional Materials

### Code Structure
```
src/
├── Enums/MicrosoftTenant.php
├── Http/
│   ├── Controllers/MicrosoftAuthController.php
│   └── Middleware/EnsureMicrosoftTokenIsValid.php
├── Models/MicrosoftUser.php
├── Services/MicrosoftAuthService.php
└── MicrosoftAuthServiceProvider.php
```

### Documentation Structure
```
docs/
├── QUICKSTART.md         (5-min setup)
├── README.md             (Main docs)
├── AZURE_AD_SETUP.md     (Azure config)
├── USAGE_EXAMPLES.md     (Code examples)
├── INTEGRATION_GUIDE.md  (Integration)
├── API_TESTING.md        (Testing)
├── FAQ.md                (Questions)
├── PACKAGE_OVERVIEW.md   (Architecture)
├── CHANGELOG.md          (History)
└── INDEX.md              (This file)
```

## 🔗 Quick Links

- **Start Here**: [QUICKSTART.md](QUICKSTART.md)
- **Full Docs**: [README.md](README.md)
- **Azure Setup**: [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md)
- **Examples**: [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md)
- **Help**: [FAQ.md](FAQ.md)
- **License**: [LICENSE](LICENSE)

---

**Last Updated**: 2024-01-24

**Package Version**: 1.0.0

**Maintained By**: Eduardo Steffens
