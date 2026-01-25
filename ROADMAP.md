# 🗺️ Roadmap - Larakit

Este documento descreve o planejamento de versões futuras do projeto.

---

## ✅ v1.0.0 - Initial Release (COMPLETO - 23/01/2026)

### Foundation Packages
- ✅ **eduardoks98/base-api** - RFC 7807, API Resources, HTTP Client
- ✅ **eduardoks98/helpers** - Brazilian utilities (CPF/CNPJ, phone, money, dates)
- ✅ **eduardoks98/security** - OWASP compliance (headers, IP blocking, CSP)
- ✅ **eduardoks98/rate-limiter** - 3-tier throttling + geolocation + fail2ban
- ✅ **eduardoks98/recaptcha** - Smart validation with trust scoring
- ✅ **eduardoks98/auth** - Sanctum + Token Abilities + Device Management
- ✅ **eduardoks98/performance** - Monitoring + N+1 detection
- ✅ **eduardoks98/reverb** - Laravel Reverb WebSocket wrapper
- ✅ **eduardoks98/api-docs** - Scramble OpenAPI auto-documentation
- ✅ **eduardoks98/health** - Health checks for K8s/Load Balancers

### Infrastructure
- ✅ Complete documentation (16 files)
- ✅ Pest PHP tests
- ✅ GitHub Actions CI/CD
- ✅ 9 database migrations
- ✅ 50+ global helper functions

---

## 📋 v1.0.1 - Bug Fixes & Improvements (Q1 2026)

### Focus: Stabilization
- 🔄 Bug fixes reported by community
- 🔄 Documentation improvements
- 🔄 Additional Pest tests (target 90%+ coverage)
- 🔄 Performance optimizations
- 🔄 Code quality improvements

### Expected Changes
- Minor fixes in existing packages
- No breaking changes
- Enhanced error messages
- Better validation

---

## ✅ v1.1.0 - Social Auth & Payments (COMPLETO - 24/01/2026)

### Social Authentication Packages
- ✅ **eduardoks98/google-auth** - Google OAuth 2.0 integration
  - Login with Google
  - league/oauth2-google ^4.0
  - User profile sync (name, email, picture)
  - Token refresh handling
  - Seamless Sanctum integration
  - 25 arquivos, 11 documentos

- ✅ **eduardoks98/facebook-auth** - Facebook OAuth integration
  - Login with Facebook
  - league/oauth2-facebook ^2.0
  - Profile and email access
  - Graph API v19.0 integration
  - 26 arquivos, 12 documentos

- ✅ **eduardoks98/microsoft-auth** - Microsoft/Azure AD integration
  - Login with Microsoft
  - thenetworg/oauth2-azure ^2.0
  - Office 365 integration
  - Azure AD multi-tenant support
  - Microsoft Graph API
  - 27 arquivos, 11 documentos

### Payment Integration Packages
- ✅ **eduardoks98/payment-stripe** - Stripe payment gateway
  - stripe/stripe-php ^13.0
  - Payment Intents API
  - One-time payments + Subscriptions
  - 21 webhook events
  - Refunds & disputes
  - SCA (3D Secure 2.0) support
  - 27 arquivos

- ✅ **eduardoks98/payment-mercadopago** - MercadoPago (Brazil/LATAM)
  - mercadopago/dx-php ^3.0
  - PIX com QR Code base64 (Orders API)
  - Boleto bancário
  - Credit/debit cards
  - Webhook signature validation
  - Split payments
  - 27 arquivos

- ✅ **eduardoks98/payment-abacatepay** - AbacatePay (Brazil Indie Hackers) 🥑
  - abacatepay/php-sdk ^1.0
  - PIX (R$ 0,80 taxa fixa)
  - Recorrência (ONE_TIME, MONTHLY, YEARLY)
  - SDK wrapper nativo
  - Foco em indie hackers
  - 27 arquivos

### Infrastructure v1.1.0
- ✅ 6 novos packages (3 social + 3 payment)
- ✅ 159 arquivos criados
- ✅ ~30.000 linhas de código
- ✅ 6 SDKs oficiais integrados
- ✅ Documentação extensiva (50+ docs)
- ✅ OAuth 2.0 flows completos
- ✅ Production-ready
- ✅ Baseado 100% em documentações oficiais

---

## ✅ v1.2.0 - Communication & Notifications (COMPLETO - 24/01/2026)

### SMS Packages
- ✅ **eduardoks98/sms-twilio** - Twilio SMS integration
  - twilio/sdk ^8.0
  - Send SMS globally (E.164 format)
  - Phone number validation
  - Delivery status tracking via webhooks
  - Templates support with variables
  - Bulk SMS (batch sending)
  - Comprehensive logging
  - 10 arquivos

- ✅ **eduardoks98/sms-comtele** - Comtele SMS (Brazil)
  - Brazilian SMS provider
  - Cost-effective for Brazil
  - DDD+Number format
  - Bulk SMS support (até 100 destinatários)
  - Templates with variables
  - Detailed reporting API
  - Rate limiting (30s cooldown)
  - 9 arquivos

### WhatsApp Packages
- ✅ **eduardoks98/whatsapp-official** - WhatsApp Business API
  - netflie/whatsapp-cloud-api ^3.0
  - Meta Cloud API integration
  - Message templates (pre-approved)
  - Media messages (image, video, audio, document)
  - Text messages with URL preview
  - Webhooks for status updates
  - Message tracking database
  - E.164 phone format
  - 10 arquivos

- ✅ **eduardoks98/whatsapp-converx** - Converx integration (Brazil)
  - Brazilian WhatsApp provider (Chatwoot-based)
  - Text messages
  - Template messages
  - Conversation management
  - Contact search
  - Brazilian phone format (55XXXXXXXXXXX)
  - Easy setup
  - 5 arquivos

### Infrastructure v1.2.0
- ✅ 4 novos packages (2 SMS + 2 WhatsApp)
- ✅ 34 arquivos criados
- ✅ 4 SDKs oficiais integrados (Twilio, netflie/whatsapp, Comtele HTTP, Converx HTTP)
- ✅ Documentação extensiva (4 READMEs completos)
- ✅ Webhook handling completo
- ✅ Production-ready
- ✅ Baseado 100% em documentações oficiais

---

## ✅ v1.3.0 - Storage & Media (COMPLETO - 24/01/2026)

### Storage Packages
- ✅ **eduardoks98/storage-s3** - AWS S3 wrapper
  - aws/aws-sdk-php-laravel ^3.0
  - Upload/download files (single & multipart)
  - Signed URLs (S3 & CloudFront)
  - Pre-signed upload URLs (client-side upload)
  - CDN integration (CloudFront)
  - File management (copy, move, delete, list)
  - File type validation
  - File size limits by category
  - 8 arquivos

- ✅ **eduardoks98/media-library** - Media management
  - intervention/image-laravel ^1.0
  - Image processing (resize, crop, rotate, flip)
  - Multiple fit modes (crop, contain, fill, stretch, pad)
  - Watermark support
  - Image filters (grayscale, brightness, contrast, blur, sharpen)
  - Automatic conversions (thumb, small, medium, large, xl)
  - WebP/AVIF optimization
  - Collection-based organization
  - Polymorphic relationships (HasMedia trait)
  - Background queue processing
  - 12 arquivos

### Infrastructure v1.3.0
- ✅ 2 novos packages (storage + media)
- ✅ 20 arquivos criados
- ✅ 2 SDKs oficiais integrados (AWS SDK, Intervention Image)
- ✅ 2 migrations
- ✅ 2 READMEs completos
- ✅ Production-ready
- ✅ Baseado 100% em documentações oficiais

---

## ✅ v1.4.0 - Brazilian Market Utilities (COMPLETO - 24/01/2026)

### Address & Geolocation
- ✅ **eduardoks98/geolocation** - Brazilian geolocation services
  - ViaCEP integration (postal code lookup)
  - Address search by state/city/street
  - CEP validation
  - Multiple geocoding providers (Nominatim, Google Maps, HERE)
  - Reverse geocoding (coordinates to address)
  - Distance calculation (Haversine formula)
  - Find closest point, points within radius
  - Sort by distance, calculate center point
  - Bounding box utilities
  - Brazilian states list
  - Response caching
  - 7 arquivos

### Email & Validation
- ✅ **eduardoks98/email-validator** - Advanced email validation
  - RFC 5322 syntax validation
  - DNS verification
  - MX records check
  - Disposable email detection (40+ domains)
  - Role-based email detection
  - Trusted domain recognition
  - Quality scoring (0-100)
  - SMTP verification (optional)
  - Batch validation
  - Response caching
  - 5 arquivos

### Financial
- ✅ **eduardoks98/banking** - Brazilian banking utilities
  - PIX key validation (CPF, CNPJ, email, phone, EVP)
  - PIX QR Code/Copy & Paste generation (EMV format)
  - PIX payload parsing
  - Bank codes database (FEBRABAN)
  - Bank lookup by code, ISPB, or name
  - Major and digital banks lists
  - Boleto barcode validation
  - Boleto digitable line parsing
  - Support for bank and utility boletos
  - Brasil API integration (optional)
  - Response caching
  - 8 arquivos

### Infrastructure v1.4.0
- ✅ 3 novos packages (geolocation + email-validator + banking)
- ✅ 20 arquivos criados
- ✅ APIs integradas (ViaCEP, Nominatim, Brasil API)
- ✅ 3 READMEs completos
- ✅ Production-ready
- ✅ Baseado 100% em documentações oficiais

---

## ✅ v1.5.0 - Ads & Monetization (COMPLETO - 25/01/2026)

### Monetization Core
- ✅ **eduardoks98/monetization** - Complete monetization system
  - Virtual currency management
  - Transaction ledger with balance tracking
  - Reward system with queue processing
  - Ad impression tracking (provider-agnostic)
  - Analytics service with caching
  - Models: AdImpression, Reward, VirtualCurrencyTransaction
  - Enums: AdProvider, RewardStatus, RewardType
  - Trait: HasVirtualCurrency for User model
  - 3 database migrations
  - 7 global helper functions

### Ad Provider Integrations
- ✅ **eduardoks98/ads-google** - Google AdMob SSV
  - Server-Side Verification (SSV) for rewarded ads
  - ECDSA signature verification
  - Public key caching (24h rotation from Google)
  - Automatic reward processing
  - Middleware: VerifyAdMobSignature
  - 12 arquivos

- ✅ **eduardoks98/ads-unity** - Unity Ads S2S
  - Server-to-Server callback validation
  - HMAC-MD5 signature verification
  - Monetization Stats API integration
  - Player reward tracking
  - 10 arquivos

- ✅ **eduardoks98/ads-applovin** - AppLovin MAX
  - S2S callback validation via event token
  - User-Level Revenue Reporting API
  - Mediation support (multi-network attribution)
  - Country-level tracking
  - 10 arquivos

- ✅ **eduardoks98/ads-facebook** - Facebook Audience Network
  - Revenue reporting via Graph API (v21.0)
  - Client-side reward endpoint (FAN doesn't have S2S)
  - Insights and metrics parsing
  - Note: AdColony was not included (deprecated)
  - 10 arquivos

### Infrastructure v1.5.0
- ✅ 5 novos packages (1 monetization + 4 ads)
- ✅ ~60 arquivos criados
- ✅ 3 migrations (ad_impressions, rewards, virtual_currency_transactions)
- ✅ 8 services
- ✅ 3 enums
- ✅ Documentação completa (5 READMEs)
- ✅ Production-ready
- ✅ Baseado 100% em documentações oficiais

---

## 🎯 v2.0.0 - GraphQL & Modern Architecture (Q2 2027)

### Breaking Changes
- ⚠️ Laravel 13 minimum requirement
- ⚠️ PHP 8.3 minimum requirement
- ⚠️ Updated API response format (optional)

### GraphQL Support
- 📦 **eduardoks98/graphql** - Lighthouse GraphQL integration
  - Schema generation
  - Resolvers
  - Subscriptions (via Reverb)
  - DataLoader (N+1 prevention)
  - Federation support

### Modern Architecture
- 🏗️ Service mesh integration (Istio, Linkerd)
- 🔄 Event sourcing support
- 📊 CQRS pattern helpers
- 🎯 Domain-driven design utilities

### Advanced Features
- 🤖 AI/ML integration helpers
- 📈 Advanced analytics
- 🔍 Full-text search (Meilisearch, Typesense)
- ⚡ Edge computing support

---

## 🔮 Future Ideas (Not Scheduled)

### Database & ORM
- MongoDB integration
- PostgreSQL specific features
- Database sharding helpers

### Monitoring & Observability
- OpenTelemetry integration
- Distributed tracing
- APM (New Relic, Datadog)
- Log aggregation (ELK, Loki)

### Testing
- E2E testing package (Dusk wrapper)
- Load testing utilities (k6, Locust)
- Chaos engineering helpers

### DevOps
- Docker configurations
- Kubernetes manifests
- Terraform modules
- GitHub Actions workflows library

### AI & Machine Learning
- OpenAI integration
- Anthropic Claude integration
- Embeddings & vector search
- Content moderation

---

## 📊 Version Timeline

```
2026 Q1: v1.0.0 ✅ + v1.1.0 ✅ + v1.2.0 ✅ + v1.3.0 ✅ + v1.4.0 ✅ + v1.5.0 ✅
2027 Q2: v2.0.0 (GraphQL + Breaking Changes)
```

---

## 💡 Contributing Ideas

Tem ideias para novos packages?

1. Abra uma [Discussion](https://github.com/eduardoks98/larakit/discussions) no GitHub
2. Descreva o problema que o package resolveria
3. Compartilhe casos de uso
4. A comunidade vota e discute

Os packages mais votados entram no roadmap oficial!

---

## 📝 Notas

- **Versionamento**: Seguimos [Semantic Versioning](https://semver.org/)
- **Breaking Changes**: Apenas em versões major (2.0, 3.0, etc)
- **Suporte**: Cada versão major recebe 2 anos de suporte
- **LTS**: Versões LTS (Long Term Support) serão marcadas quando lançadas

---

**Última atualização**: 25 de Janeiro de 2026
**Versão atual**: v1.5.0
**Próximo release**: v2.0.0 (GraphQL + Modern Architecture)
