# Changelog

All notable changes to `eduardoks98/payment-mercadopago` will be documented in this file.

## [Unreleased]

## [1.0.0] - 2024-01-24

### Added
- Initial release
- PIX payment integration using Orders API (POST /v1/orders)
- Credit/debit card payment integration using Payment API
- Boleto Bancário payment integration
- QR Code support with `qr_code_base64` for PIX payments
- Comprehensive webhook handling for payment notifications
- Support for MercadoPago SDK v3.x (mercadopago/dx-php)
- Payment status tracking with PHP 8.1+ enums
- Webhook signature validation middleware
- Database migrations for payments and webhooks
- RESTful API endpoints for payment operations
- Payment refund functionality
- Configurable expiration times for PIX and Boleto
- Idempotency key support to prevent duplicate payments
- Comprehensive documentation and usage examples
- Unit and feature tests
- Support for Laravel 10.x, 11.x, and 12.x
- PHP 8.1, 8.2, and 8.3 support

### Features

#### Payment Methods
- **PIX**: Instant payments with QR code generation
- **Credit Card**: Full card payment processing with installments
- **Debit Card**: Debit card payment support
- **Boleto**: Bank slip generation with barcode

#### Enums (Type-Safe)
- `PaymentStatus`: All MercadoPago payment statuses
- `PaymentMethod`: Supported payment methods
- `WebhookTopic`: Webhook notification topics

#### Models
- `MercadoPagoPayment`: Complete payment tracking
- `MercadoPagoWebhook`: Webhook audit trail

#### Services
- `MercadoPagoService`: Main payment processing service
- `WebhookService`: Webhook handling and processing

#### API Endpoints
- `POST /api/mercadopago/payments/pix`: Create PIX payment
- `POST /api/mercadopago/payments/card`: Create card payment
- `POST /api/mercadopago/payments/boleto`: Create Boleto payment
- `GET /api/mercadopago/payments/{id}`: Get payment details
- `POST /api/mercadopago/payments/{id}/refund`: Refund payment
- `POST /api/mercadopago/webhook`: Receive MercadoPago notifications

#### Security
- Webhook signature validation
- Environment-based credentials
- Idempotency key support
- HTTPS enforcement for API calls

#### Developer Experience
- Comprehensive README with examples
- Usage examples document
- PHPUnit test suite
- PSR-4 autoloading
- Laravel service provider auto-discovery
- Database migrations
- Configuration publishing

### Official Documentation Compliance
- Based on official MercadoPago PIX documentation
- Uses official MercadoPago PHP SDK v3.x
- Follows MercadoPago API best practices
- Implements official webhook specifications

### Sources
- [MercadoPago PIX Documentation](https://www.mercadopago.com.br/developers/en/docs/checkout-api-orders/payment-integration/pix)
- [MercadoPago PHP SDK](https://github.com/mercadopago/sdk-php)
- [MercadoPago Webhooks](https://www.mercadopago.com.ar/developers/en/docs/your-integrations/notifications/webhooks)
- [MercadoPago IPN Notifications](https://www.mercadopago.com.ar/developers/en/docs/your-integrations/notifications/ipn)

[Unreleased]: https://github.com/eduardoks98/payment-mercadopago/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/eduardoks98/payment-mercadopago/releases/tag/v1.0.0
