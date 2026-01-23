# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-01-23

### 🎉 Initial Release

#### Added - Foundation Packages
- **eduardoks98/base-api** - RFC 7807 Problem Details, API Resources, HTTP Client
  - `ApiController` base controller with standardized responses
  - `ApiResource` and `ApiCollection` classes
  - `ApiBaseService` for external HTTP calls
  - Traits: `HasApiResponses`, `HasTransactions`, `PreventLazyLoading`
  - Middleware: `ForceJsonResponse`, `SetApiHeaders`
  - Global helpers: `problemDetails()`, `apiResponse()`, `beginTransaction()`, etc.

- **eduardoks98/helpers** - Brazilian Market Utilities (100% framework-agnostic)
  - CPF/CNPJ validators and formatters
  - Phone number formatter (Brazilian format)
  - Money formatter (R$ format)
  - Date formatters (Brazilian ↔ American)
  - Global helpers: `checkCPF()`, `formatPhoneNumber()`, `moneyFormat()`, etc.

#### Added - Security Packages
- **eduardoks98/security** - OWASP API Security Compliance
  - Security headers middleware (CSP, HSTS, X-Frame-Options, etc.)
  - IP blocking with geolocation tracking
  - Database-driven banned IPs with whitelist support
  - Encryption service (JWE)
  - Global helpers: `banIp()`, `unbanIp()`, `isIpBanned()`

- **eduardoks98/rate-limiter** - Advanced 3-Tier Throttling
  - Tier 1: Per-Route global limits
  - Tier 2: Per-IP + Route limits
  - Tier 3: Global IP banning
  - Geolocation-based restrictions
  - SQL/XSS injection detection
  - Volume anomaly detection
  - IP whitelist with CIDR support
  - fail2ban integration
  - Global helpers: `whitelistIp()`, `getApiRequestLogs()`, `banIpOnSSH()`

- **eduardoks98/recaptcha** - Smart Context-Aware Validation
  - Google reCAPTCHA v3 and Enterprise support
  - Trust scoring algorithm (multi-factor analysis)
  - IP reputation tracking
  - User history analysis
  - Time pattern detection (business hours)
  - Geolocation risk assessment
  - Bot detection via User-Agent
  - Auto-approve/reject based on trust score
  - Global helpers: `checkRecaptcha()`, `getRecaptchaStats()`

#### Added - Authentication Package
- **eduardoks98/auth** - Modern Sanctum Authentication
  - Laravel Sanctum token-based authentication
  - Access + Refresh token system
  - Token expiration (15min access, 7 days refresh)
  - Token abilities (granular permissions)
  - Device management and tracking
  - Session tracking with browser/platform detection
  - AuthController with login/logout/refresh/me endpoints
  - CheckTokenAbilities middleware
  - Global helpers: `createAccessToken()`, `getUserDevices()`, `hasTokenAbility()`

#### Added - Monitoring Packages
- **eduardoks98/performance** - Performance Monitoring
  - Request duration tracking
  - Query count and time monitoring
  - Memory usage tracking
  - Slow request detection
  - N+1 query prevention (Laravel 11/12)
  - Laravel Pulse integration ready
  - Global helpers: `getSlowRequests()`, `getPerformanceStats()`

#### Added - Infrastructure Packages
- **eduardoks98/reverb** - Laravel Reverb WebSocket Wrapper
  - Pre-configured Reverb setup
  - Channel authorization examples
  - Broadcasting configuration
  - Laravel Echo integration guide

- **eduardoks98/api-docs** - Scramble OpenAPI Documentation
  - Zero-config auto-generated documentation
  - OpenAPI 3.1 standard
  - Stoplight UI integration
  - Automatic Form Request detection
  - API Resource response examples

- **eduardoks98/health** - Health Check Endpoints
  - Basic health check (`/health`)
  - Database connectivity check (`/health/db`)
  - Cache health check (`/health/cache`)
  - Queue health check (`/health/queue`)
  - Full health report (`/health/full`)
  - Kubernetes liveness/readiness probe compatible

#### Documentation
- Complete documentation in `/docs`
  - Overview with technology decisions
  - Quick start guide (5 minutes)
  - Detailed installation guide
  - Package-specific documentation (10 files)
- README for each package with examples
- IMPLEMENTATION-STATUS.md with full checklist
- .env.example with all configuration options

#### Testing
- Pest PHP configuration
- Unit tests for helpers package
- GitHub Actions CI/CD workflows
  - Automated testing (PHP 8.1/8.2/8.3, Laravel 10/11/12)
  - Code quality checks (Pint, Larastan)
  - Security audit

#### Infrastructure
- Monorepo structure with path repositories
- PSR-4 autoloading for all packages
- Service Provider auto-discovery
- 9 database migrations
- 50+ global helper functions
- MIT License
- Contributing guidelines

### 📊 Statistics
- **Packages**: 10 complete
- **Files**: 150+ PHP files
- **Lines of Code**: ~15,000+
- **Migrations**: 9
- **Helper Functions**: 50+
- **Middleware**: 10+
- **Services**: 15+
- **Models**: 10+
- **Controllers**: 5+

### 🎯 Technology Stack
- Laravel 10/11/12
- PHP 8.1/8.2/8.3
- Laravel Sanctum
- Laravel Reverb (WebSocket)
- Laravel Pulse (Monitoring)
- Scramble (API Docs)
- Pest PHP (Testing)
- Guzzle (HTTP Client)
- stevebauman/location (Geolocation)

### 🔒 Security Features
- RFC 7807 standardized error responses
- OWASP API Security Top 10 compliance
- Security headers (CSP, HSTS, etc.)
- 3-tier rate limiting
- IP blocking with geolocation
- Smart reCAPTCHA with trust scoring
- Injection detection (SQL, XSS, Path Traversal)
- Volume anomaly detection
- Token-based authentication with abilities
- Device management and session tracking

---

## Future Releases

### [1.1.0] - Planned
- Social authentication packages
  - eduardoks98/google-auth
  - eduardoks98/facebook-auth
  - eduardoks98/microsoft-auth
- Payment integration packages
  - eduardoks98/payment-stripe
  - eduardoks98/payment-mercadopago
- Communication packages
  - eduardoks98/sms-twilio
  - eduardoks98/whatsapp-official

### [2.0.0] - Future
- Laravel 13 support
- GraphQL package
- Service mesh integration
- Advanced analytics

---

[Unreleased]: https://github.com/eduardoks98/api-base-monorepo/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/eduardoks98/api-base-monorepo/releases/tag/v1.0.0
