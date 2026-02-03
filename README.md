# Larakit

> Production-ready Laravel packages for building modern REST APIs with Brazilian market support.

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012-red)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## Overview

**Larakit** is a monorepo containing 25 reusable Composer packages designed to accelerate Laravel API development. Built with modern best practices including RFC 7807 responses, OWASP security compliance, and first-class support for the Brazilian market.

### Key Features

- **RFC 7807** - Standard problem details for HTTP APIs
- **OWASP Compliant** - Security headers, rate limiting, IP blocking
- **Brazilian Market** - CPF/CNPJ, PIX, ViaCEP, bank codes
- **Modern Auth** - Sanctum with token abilities and device management
- **Real-time** - Laravel Reverb WebSocket integration
- **Auto Documentation** - Scramble OpenAPI 3.1 generation
- **Health Checks** - Kubernetes and load balancer ready

---

## Packages

### Core Packages (v1.0.0)

| Package | Description |
|---------|-------------|
| [base-api](./packages/core/base-api) | RFC 7807 responses, API Resources, HTTP Client |
| [helpers](./packages/core/helpers) | Brazilian utilities (CPF/CNPJ, phone, money, dates) |
| [security](./packages/core/security) | Security headers, CSP, IP blocking, OWASP compliance |
| [rate-limiter](./packages/core/rate-limiter) | 3-tier throttling with geolocation and fail2ban |
| [recaptcha](./packages/core/recaptcha) | Smart reCAPTCHA with trust scoring algorithm |
| [auth](./packages/core/auth) | Sanctum authentication with token abilities & devices |
| [performance](./packages/core/performance) | Performance monitoring with Laravel Pulse integration |
| [reverb](./packages/core/reverb) | Laravel Reverb WebSocket wrapper and configuration |
| [api-docs](./packages/core/api-docs) | Scramble OpenAPI 3.1 auto-generated documentation |
| [health](./packages/core/health) | Health check endpoints (K8s/Load Balancer ready) |

### Social Authentication (v1.1.0)

| Package | Description |
|---------|-------------|
| [google-auth](./packages/auth/google) | Google OAuth 2.0 integration with profile sync |
| [facebook-auth](./packages/auth/facebook) | Facebook OAuth with Graph API v19.0 |
| [microsoft-auth](./packages/auth/microsoft) | Microsoft/Azure AD OAuth with Office 365 support |

### Payment Gateways (v1.1.0)

| Package | Description |
|---------|-------------|
| [payment-stripe](./packages/payment/stripe) | Stripe with subscriptions & webhooks |
| [payment-mercadopago](./packages/payment/mercadopago) | MercadoPago with PIX QR Code, Boleto, and cards |
| [payment-abacatepay](./packages/payment/abacatepay) | AbacatePay PIX integration |

### Communication (v1.2.0)

| Package | Description |
|---------|-------------|
| [sms-twilio](./packages/sms/twilio) | Twilio SMS integration for global messaging |
| [sms-comtele](./packages/sms/comtele) | Comtele SMS provider for Brazil |
| [whatsapp-official](./packages/whatsapp/official) | WhatsApp Business Cloud API (Meta official) |
| [whatsapp-converx](./packages/whatsapp/converx) | Converx WhatsApp provider for Brazil |

### Storage (v1.3.0)

| Package | Description |
|---------|-------------|
| [storage-s3](./packages/storage/s3) | AWS S3 wrapper with signed URLs and CloudFront CDN |
| [media-library](./packages/storage/media-library) | Media management with image processing |

### Brazilian Market (v1.4.0)

| Package | Description |
|---------|-------------|
| [geolocation](./packages/brazilian/geolocation) | ViaCEP, geocoding, distance calculation |
| [email-validator](./packages/brazilian/email-validator) | Advanced email validation with quality scoring |
| [banking](./packages/brazilian/banking) | PIX validation, bank codes, boleto validation |

---

## Quick Start

### 1. Add Repository

```bash
composer config repositories.larakit vcs https://github.com/eduardoks98/larakit.git
```

### 2. Install Packages

```bash
# Full stack (recommended)
composer require eduardoks98/base-api \
    eduardoks98/helpers \
    eduardoks98/security \
    eduardoks98/auth \
    eduardoks98/rate-limiter \
    eduardoks98/recaptcha

# Minimal (API + Auth only)
composer require eduardoks98/base-api eduardoks98/auth eduardoks98/helpers

# Brazilian market
composer require eduardoks98/geolocation eduardoks98/banking eduardoks98/email-validator

# Payments
composer require eduardoks98/payment-stripe eduardoks98/payment-mercadopago
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
php artisan migrate
```

### 4. Configure Environment

```env
# Authentication
SANCTUM_ACCESS_TOKEN_EXPIRATION=15
SANCTUM_REFRESH_TOKEN_EXPIRATION=10080

# Rate Limiting
RATE_LIMITER_MAX_ATTEMPTS=60
THROTTLE_MAX_ATTEMPTS=30
MAX_ATTEMPTS_BEFORE_BAN_IP=100

# reCAPTCHA (optional)
RECAPTCHA_V3_SECRET=your_secret_key
RECAPTCHA_V3_SITE_KEY=your_site_key
RECAPTCHA_THRESHOLD=0.5

# WebSocket (optional)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
```

---

## Usage Examples

### RFC 7807 Error Response

```php
use function problemDetails;

return problemDetails(
    type: 'https://api.example.com/errors/validation',
    title: 'Validation Error',
    status: 422,
    detail: 'The email field is required.',
    instance: '/api/v1/users'
);
```

**Response:**
```json
{
    "type": "https://api.example.com/errors/validation",
    "title": "Validation Error",
    "status": 422,
    "detail": "The email field is required.",
    "instance": "/api/v1/users"
}
```

### Brazilian Document Validation

```php
use function checkCPF;
use function checkCNPJ;
use function formatarCpfCnpj;

// Validate CPF
$isValid = checkCPF('123.456.789-09'); // true or false

// Validate CNPJ
$isValid = checkCNPJ('12.345.678/0001-95'); // true or false

// Format with leading zeros
$formatted = formatarCpfCnpj('1234567809'); // '01234567809'
```

### ViaCEP Address Lookup

```php
use Eduardoks98\Geolocation\Services\ViaCepService;

$viaCep = app(ViaCepService::class);

// Find address by CEP
$address = $viaCep->findByCep('01310-100');
// Returns: street, neighborhood, city, state, ibge_code, ddd

// Search by address
$addresses = $viaCep->search('SP', 'São Paulo', 'Paulista');
```

### PIX Validation

```php
use Eduardoks98\Banking\Services\PixService;

$pix = app(PixService::class);

// Validate PIX key (auto-detect type)
$result = $pix->validate('123.456.789-09');
// Returns: valid, key, type (cpf/cnpj/email/phone/evp), formatted

// Generate PIX QR Code payload
$payload = $pix->generatePixCopyPaste([
    'key' => '12345678909',
    'merchant_name' => 'João Silva',
    'merchant_city' => 'São Paulo',
    'amount' => 100.50,
    'transaction_id' => 'ORDER123',
]);
```

### Email Validation with Quality Score

```php
use Eduardoks98\EmailValidator\Services\EmailValidatorService;

$validator = app(EmailValidatorService::class);

$result = $validator->validate('user@example.com');
// Returns:
// - valid: true/false
// - score: 0-100 (quality score)
// - is_disposable: true/false
// - is_role_based: true/false
// - has_mx: true/false
// - reason: validation details
```

### Distance Calculation

```php
use Eduardoks98\Geolocation\Services\DistanceService;

$distance = app(DistanceService::class);

// Calculate distance between two points
$km = $distance->calculate(
    -23.5505, -46.6333,  // São Paulo
    -22.9068, -43.1729   // Rio de Janeiro
);
// Returns: 357.86 (km)

// Find closest point
$closest = $distance->findClosest($origin, $stores);

// Find points within radius
$nearby = $distance->findWithinRadius($center, $stores, 5); // 5km
```

### Google OAuth Authentication

```php
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;

$google = app(GoogleAuthService::class);

// Get authorization URL
$authUrl = $google->getAuthUrl([
    'redirect_uri' => 'https://myapp.com/callback',
    'scopes' => ['email', 'profile'],
]);

// Handle callback
$user = $google->handleCallback($code, $redirectUri);
// Returns: id, email, name, picture, verified_email
```

---

## Architecture

```
larakit/
├── packages/
│   ├── core/           # Foundation packages
│   │   ├── base-api/   # RFC 7807, API Resources
│   │   ├── helpers/    # Brazilian utilities
│   │   ├── security/   # OWASP compliance
│   │   ├── auth/       # Sanctum authentication
│   │   └── ...
│   ├── auth/           # Social authentication
│   │   ├── google/
│   │   ├── facebook/
│   │   └── microsoft/
│   ├── payment/        # Payment gateways
│   │   ├── stripe/
│   │   ├── mercadopago/
│   │   └── abacatepay/
│   ├── sms/            # SMS providers
│   ├── whatsapp/       # WhatsApp providers
│   ├── storage/        # Storage & media
│   └── brazilian/      # Brazilian market
│       ├── geolocation/
│       ├── email-validator/
│       └── banking/
└── docs/               # Documentation
```

### Dependency Graph

```
Level 1 (Foundation):
  base-api ←── (no dependencies)
  helpers ──── (framework-agnostic)

Level 2 (Core Services):
  security ───→ base-api
  health ─────→ base-api
  api-docs ───→ base-api

Level 3 (Advanced Features):
  rate-limiter → base-api + security
  recaptcha ──→ base-api + security
  auth ───────→ base-api + security
```

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| Laravel | 10, 11, or 12 |
| Redis | Recommended for rate limiting and queues |

---

## Documentation

| Document | Description |
|----------|-------------|
| [Full Documentation](docs/) | Complete technical documentation |
| [Usage Guide](docs/usage-guide.md) | 7 practical examples |
| [Quick Reference](docs/quick-reference.md) | Visual quick guide |
| [Costs & API Keys](docs/reference/costs.md) | Service costs and setup |
| [Roadmap](ROADMAP.md) | Future versions and features |

Each package includes its own README with:
- Installation instructions
- Configuration options
- Usage examples
- API reference

---

## Development

### Running Tests

```bash
# All packages
composer test

# Unit tests only
composer test:unit

# Feature tests only
composer test:feature

# Specific package
cd packages/core/helpers && ../../../vendor/bin/phpunit
```

### Code Style

```bash
composer lint
```

---

## Costs

Most packages are **100% free**. Some integrate with paid services:

| Service | Cost | Free Tier |
|---------|------|-----------|
| Google OAuth | Free | Unlimited |
| Facebook OAuth | Free | Unlimited |
| Microsoft OAuth | Free | Unlimited |
| reCAPTCHA | Free | 1M/month |
| Stripe | 2.9% + $0.30 | Per transaction |
| MercadoPago | 0.99% - 4.98% | Per transaction |
| Twilio SMS | ~$0.05/SMS | Trial credit |
| AWS S3 | Pay-as-you-go | 5GB free (12 months) |
| Google Maps | $5/1k requests | 40k/month free |

See [Costs Documentation](docs/reference/costs.md) for details.

---

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

---

## License

MIT License - see [LICENSE](LICENSE) for details.

---

## NPM Packages (React/TypeScript)

Alem dos pacotes Laravel, o Larakit tambem oferece pacotes NPM para projetos React/TypeScript.

### Pacotes Disponiveis

| Pacote | Descricao | Registry |
|--------|-----------|----------|
| `@eduardoks98/google-analytics` | Google Analytics 4 (GA4) | GitHub Packages |
| `@eduardoks98/google-adsense` | Google AdSense | GitHub Packages |
| `@eduardoks98/facebook-ads` | Facebook Pixel | GitHub Packages |
| `@eduardoks98/create-game` | CLI para criar jogos multiplayer | GitHub Packages |

### Instalacao NPM

```bash
# Configurar GitHub Packages
echo "@eduardoks98:registry=https://npm.pkg.github.com" >> .npmrc

# Instalar pacotes
npm install @eduardoks98/google-analytics
npm install @eduardoks98/google-adsense
npm install @eduardoks98/facebook-ads

# Criar novo jogo
npx @eduardoks98/create-game meu-jogo
```

### Exemplo: Google Analytics

```tsx
import { GoogleAnalyticsProvider, useAnalytics } from '@eduardoks98/google-analytics';

<GoogleAnalyticsProvider measurementId="G-XXXXXXXXXX">
  <App />
</GoogleAnalyticsProvider>

// Em componentes
const analytics = useAnalytics();
analytics.event('button_click', { button_name: 'cta' });
```

Ver documentacao completa em [packages/google/analytics/npm](./packages/google/analytics/npm).

---

## Convencao de Commits

Este repositorio usa **semantic-release** para versionamento automatico dos pacotes NPM.

### Formato

```
<tipo>: <descricao>
```

### Tipos e Releases

| Tipo | Release | Exemplo |
|------|---------|---------|
| `fix:` | Patch (1.0.X) | `fix: corrigir tracking de eventos` |
| `feat:` | Minor (1.X.0) | `feat: adicionar suporte a custom events` |
| `BREAKING CHANGE:` | Major (X.0.0) | Mudanca incompativel no body |
| `docs:` | Nenhum | Documentacao |
| `chore:` | Nenhum | Manutencao |

### Exemplos

```bash
# Patch (1.0.0 -> 1.0.1)
git commit -m "fix: corrigir inicializacao do GA4"

# Minor (1.0.0 -> 1.1.0)
git commit -m "feat: adicionar hook useMatchTracking"

# Major (1.0.0 -> 2.0.0)
git commit -m "feat: redesign da API

BREAKING CHANGE: metodo trackEvent renomeado para event"
```

Ver [CONTRIBUTING.md](./CONTRIBUTING.md) para mais detalhes.

---

## Author

**Eduardo Steffens** ([@eduardoks98](https://github.com/eduardoks98))

---

<p align="center">
  Made with care for the Laravel and React communities
</p>
