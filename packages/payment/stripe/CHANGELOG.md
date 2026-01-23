# Changelog

All notable changes to `eduardoks98/payment-stripe` will be documented in this file.

## [1.0.0] - 2024-01-24

### Added - Initial Release

#### Core Features
- Full Stripe Payment Intents API integration using official stripe/stripe-php v13.x SDK
- Complete Customer API implementation
- Complete Subscription API implementation
- Webhook handling with signature verification for 21+ event types
- Database synchronization with Stripe objects

#### Services
- `StripeService` - Payment Intents API wrapper
  - Create, confirm, cancel, capture, update payment intents
  - Automatic database synchronization
  - Real-time payment status tracking

- `CustomerService` - Customer API wrapper
  - Create, update, delete customers
  - Payment method management (attach, detach, list)
  - Default payment method configuration
  - Find or create customer helper

- `SubscriptionService` - Subscription API wrapper
  - Create, update, cancel subscriptions
  - Pause/resume functionality
  - Price change (upgrade/downgrade)
  - Trial period support
  - Prorated billing support

#### Models
- `StripePayment` - PaymentIntent data model with relationships and status helpers
- `StripeCustomer` - Customer data model with user and payment relationships
- `StripeSubscription` - Subscription data model with status tracking

#### Enums
- `PaymentStatus` - Type-safe payment statuses from official Stripe documentation
- `SubscriptionStatus` - Type-safe subscription statuses from official Stripe documentation

#### Controllers
- `PaymentController` - RESTful API endpoints for payments, customers, and subscriptions
- `WebhookController` - Automatic webhook event handling with extensibility

#### Middleware
- `VerifyStripeWebhook` - Official webhook signature verification

#### Configuration
- Comprehensive Stripe configuration file with all API settings
- Environment variables for API keys and webhook secrets
- Configurable payment, subscription, and customer defaults
- Logging configuration

#### Database
- `stripe_customers` table migration
- `stripe_payments` table migration
- `stripe_subscriptions` table migration
- All tables with proper indexes and relationships

#### Routes
- 9 RESTful API endpoints for payment operations
- Webhook endpoint with automatic signature verification

#### Documentation
- Comprehensive README.md with complete API documentation
- EXAMPLES.md with real-world usage examples
- STRUCTURE.md with detailed architecture documentation
- IMPLEMENTATION.md with compliance verification
- .env.example with all configuration options

#### Testing
- Basic test structure with PHPUnit/Pest support
- Test configuration for Orchestra Testbench

#### Security
- Webhook signature verification using official Stripe SDK
- Environment-based secret key management
- HTTPS requirement for production webhooks
- PCI compliance through Stripe.js integration
- 3D Secure 2 support via Payment Intents API

### Technical Specifications

- **PHP Version**: ^8.1|^8.2|^8.3
- **Laravel Version**: ^10.0|^11.0|^12.0
- **Stripe SDK**: stripe/stripe-php ^13.0
- **Architecture**: Service-oriented with repository pattern
- **API Style**: RESTful with official Stripe conventions
- **Database**: MySQL/PostgreSQL compatible
- **License**: MIT

### Webhook Events Supported

#### Payment Intent Events
- payment_intent.succeeded
- payment_intent.payment_failed
- payment_intent.canceled
- payment_intent.created
- payment_intent.processing

#### Subscription Events
- customer.subscription.created
- customer.subscription.updated
- customer.subscription.deleted
- customer.subscription.trial_will_end

#### Invoice Events
- invoice.created
- invoice.finalized
- invoice.paid
- invoice.payment_failed

#### Customer Events
- customer.created
- customer.updated
- customer.deleted

#### Payment Method Events
- payment_method.attached
- payment_method.detached

#### Charge Events
- charge.succeeded
- charge.failed
- charge.refunded

### API Endpoints

```
POST   /api/stripe/payment-intents
GET    /api/stripe/payment-intents/{id}
POST   /api/stripe/payment-intents/{id}/confirm
POST   /api/stripe/payment-intents/{id}/cancel
POST   /api/stripe/payment-intents/{id}/capture
POST   /api/stripe/customers
POST   /api/stripe/subscriptions
POST   /api/stripe/subscriptions/{id}/cancel
POST   /api/stripe/webhook
```

### Breaking Changes
- None (initial release)

### Deprecated
- None (initial release)

### Fixed
- None (initial release)

### Security
- Implemented webhook signature verification
- Added environment variable validation
- Secured API keys in .env configuration

---

## Future Plans

### Planned for v1.1.0
- [ ] Refund API integration
- [ ] Dispute management
- [ ] Connect/Platform support
- [ ] Payment Link generation
- [ ] Checkout Session integration
- [ ] Setup Intent implementation
- [ ] Invoice management
- [ ] Tax calculation integration

### Planned for v1.2.0
- [ ] Laravel Events for all webhook events
- [ ] Queue support for webhook processing
- [ ] Rate limiting configuration
- [ ] Retry logic for failed API calls
- [ ] Caching layer for frequently accessed data
- [ ] CLI commands for data synchronization

### Planned for v2.0.0
- [ ] Multi-currency support enhancements
- [ ] Advanced subscription features (metered billing, etc.)
- [ ] Payment Method configuration UI
- [ ] Stripe Billing Portal integration
- [ ] Advanced reporting and analytics
- [ ] Multi-tenant support

---

## Links

- **Package Repository**: https://github.com/eduardoks98/payment-stripe
- **Official Stripe Documentation**: https://stripe.com/docs
- **stripe-php SDK**: https://github.com/stripe/stripe-php
- **Issue Tracker**: https://github.com/eduardoks98/payment-stripe/issues

---

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
