# Changelog

All notable changes to `eduardoks98/payment-abacatepay` will be documented in this file.

## [1.0.0] - 2024-01-24

### Added
- Initial release of AbacatePay payment integration for Laravel
- Official AbacatePay PHP SDK integration (`abacatepay/php-sdk: ^1.0`)
- AbacatePayService wrapper for SDK's BillingClient and CustomerClient
- Support for PIX and credit card payment methods
- Support for one-time payments, monthly and yearly subscriptions
- Data Transfer Objects (DTOs):
  - BillingData - Billing creation wrapper
  - ProductData - Product information wrapper
  - CustomerData - Customer data wrapper
- PHP 8.1+ Enums:
  - Frequency (ONE_TIME, MONTHLY, YEARLY)
  - PaymentMethod (PIX, CARD)
  - BillingStatus (PENDING, PAID, CANCELLED, EXPIRED, REFUNDED)
- Eloquent model (AbacatePayBilling) for database persistence
- Database migration for billings table
- RESTful API controllers:
  - AbacatePayController - Billing and customer management
  - WebhookController - Webhook event handling
- Webhook signature verification middleware (VerifyAbacatePayWebhook)
- Automatic route registration
- Configuration file with environment variable support
- Service Provider with automatic registration
- Comprehensive test suite (PHPUnit/Pest)
- Complete documentation:
  - README.md - Full package documentation
  - EXAMPLE.md - Practical usage examples
  - STRUCTURE.md - Package structure overview
- MIT License

### SDK Integration
- Client::setToken() authentication
- BillingClient for billing operations
- CustomerClient for customer management
- Full compatibility with AbacatePay\Billing, Product, Customer classes
- Support for AbacatePay\Frequencies and Methods constants

### Features
- Database persistence (optional, configurable)
- Webhook signature verification
- Automatic billing status updates
- Query scopes for common operations
- Helper methods for status checks
- Price formatting (cents to BRL)
- User relationship support
- Metadata support for custom data
- Return and completion URL configuration
- Direct SDK access when needed

### API Endpoints
- `POST /api/abacatepay/billings` - Create billing
- `GET /api/abacatepay/billings` - List billings
- `GET /api/abacatepay/billings/{id}` - Get billing details
- `POST /api/abacatepay/customers` - Create customer
- `GET /api/abacatepay/customers/{id}` - Get customer details
- `POST /webhooks/abacatepay` - Webhook handler

### Requirements
- PHP 7.2.5+ (PHP 8.1+ recommended for enum support)
- Laravel 10.x, 11.x, or 12.x
- Official AbacatePay PHP SDK
- Composer

### Configuration
Environment variables:
- ABACATEPAY_TOKEN
- ABACATEPAY_WEBHOOK_SECRET
- ABACATEPAY_DEFAULT_METHOD
- ABACATEPAY_DEFAULT_FREQUENCY
- ABACATEPAY_RETURN_URL
- ABACATEPAY_COMPLETION_URL
- ABACATEPAY_STORE_BILLINGS

## [Unreleased]

### Planned Features
- Event system for billing status changes
- Command for cleanup expired billings
- Dashboard widgets for billing statistics
- Export functionality for billing reports
- Additional payment methods as SDK evolves
- Recurring billing management helpers
- Customer portal integration
- Billing notifications
- Failed payment retry logic
- Dunning management

---

## Version History

### Version 1.0.0 (2024-01-24)
First stable release with full AbacatePay SDK integration, database persistence, webhook handling, and comprehensive API.

---

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Upgrade Guide

### From Pre-release to 1.0.0
This is the initial release. No upgrade needed.

## Breaking Changes

None yet. This is the initial release.

## Deprecations

None yet.

## Security

If you discover any security-related issues, please email eduardo@example.com instead of using the issue tracker.
