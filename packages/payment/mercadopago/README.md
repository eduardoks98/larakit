# MercadoPago Payment Integration for Laravel

[![Latest Version](https://img.shields.io/packagist/v/eduardoks98/payment-mercadopago.svg)](https://packagist.org/packages/eduardoks98/payment-mercadopago)
[![License](https://img.shields.io/packagist/l/eduardoks98/payment-mercadopago.svg)](https://packagist.org/packages/eduardoks98/payment-mercadopago)

Modern Laravel package for MercadoPago payment integration using the official PHP SDK (v3.x). Supports PIX, credit/debit cards, and Boleto Bancário with comprehensive webhook handling.

## Features

- **PIX Payments** - Real-time payments with QR code (base64)
- **Credit/Debit Cards** - Complete card payment processing
- **Boleto Bancário** - Bank slip payments with barcode
- **Webhooks** - Real-time payment status notifications
- **Official SDK** - Uses MercadoPago PHP SDK v3.x
- **Type-Safe** - Full PHP 8.1+ enum support
- **Database Storage** - Payment and webhook tracking
- **Signature Validation** - Secure webhook verification

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- MercadoPago account and credentials

## Installation

Install via Composer:

```bash
composer require eduardoks98/payment-mercadopago
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --provider="Eduardoks98\PaymentMercadoPago\PaymentMercadoPagoServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

Add your MercadoPago credentials to `.env`:

```env
MERCADOPAGO_ACCESS_TOKEN=your_access_token_here
MERCADOPAGO_PUBLIC_KEY=your_public_key_here
MERCADOPAGO_ENVIRONMENT=sandbox # or production
MERCADOPAGO_WEBHOOK_SECRET=your_webhook_secret_here

# Optional configurations
MERCADOPAGO_PIX_EXPIRATION=PT24H
MERCADOPAGO_BOLETO_EXPIRATION_DAYS=3
MERCADOPAGO_PROCESSING_MODE=automatic
MERCADOPAGO_STATEMENT_DESCRIPTOR=YOUR_STORE
MERCADOPAGO_LOGGING_ENABLED=false
```

Get your credentials from: https://www.mercadopago.com.br/developers/panel/credentials

## Usage

### PIX Payment

Create a PIX payment using the Orders API:

```php
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;

$mercadoPago = app(MercadoPagoService::class);

$payment = $mercadoPago->createPixPayment([
    'amount' => 100.00,
    'payer_email' => 'customer@example.com',
    'payer_name' => 'John Doe',
    'payer_document' => '12345678909',
    'payer_document_type' => 'CPF',
    'description' => 'Product purchase',
    'external_reference' => 'ORDER-123',
    'metadata' => [
        'order_id' => 123,
        'customer_id' => 456,
    ],
    'expiration_time' => 'PT30M', // Optional: 30 minutes (ISO 8601 duration)
]);

// Access PIX data
$qrCode = $payment->qr_code; // Text code for copy-paste
$qrCodeBase64 = $payment->qr_code_base64; // Base64 image
$qrCodeDataUri = $payment->getPixQrCodeDataUri(); // data:image/jpeg;base64,...
$ticketUrl = $payment->ticket_url; // URL with QR code page
```

Display QR code in frontend:

```html
<!-- Direct base64 display -->
<img src="{{ $payment->getPixQrCodeDataUri() }}" alt="PIX QR Code">

<!-- Or copy-paste code -->
<input type="text" value="{{ $payment->qr_code }}" readonly>
```

### Credit/Debit Card Payment

```php
$payment = $mercadoPago->createCardPayment([
    'amount' => 100.00,
    'token' => 'card_token_from_mercadopago_js', // Generated via MercadoPago.js
    'payment_method_id' => 'visa',
    'installments' => 1,
    'payer_email' => 'customer@example.com',
    'payer_name' => 'John Doe',
    'payer_document' => '12345678909',
    'payer_document_type' => 'CPF',
    'description' => 'Product purchase',
    'external_reference' => 'ORDER-123',
]);

// Check status
if ($payment->isApproved()) {
    // Payment successful
}
```

### Boleto Bancário Payment

```php
$payment = $mercadoPago->createBoletoPayment([
    'amount' => 100.00,
    'payer_email' => 'customer@example.com',
    'payer_name' => 'John Doe',
    'payer_document' => '12345678909',
    'payer_document_type' => 'CPF',
    'description' => 'Product purchase',
    'external_reference' => 'ORDER-123',
    'expiration_days' => 3, // Optional: defaults to 3 days
]);

// Access Boleto data
$ticketUrl = $payment->ticket_url; // URL to Boleto PDF
$barcode = $payment->barcode; // Barcode number
$expirationDate = $payment->expiration_date; // Due date
```

### Query Payment

```php
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;

// By external reference
$payment = MercadoPagoPayment::externalReference('ORDER-123')->first();

// By MercadoPago ID
$payment = MercadoPagoPayment::mercadoPagoId('12345678')->first();

// By status
$approvedPayments = MercadoPagoPayment::status(PaymentStatus::APPROVED)->get();

// By payment method
$pixPayments = MercadoPagoPayment::paymentMethod(PaymentMethod::PIX)->get();
```

### Refund Payment

```php
$mercadoPago = app(MercadoPagoService::class);

// Full refund
$refund = $mercadoPago->refundPayment($payment->mercadopago_id);

// Partial refund
$refund = $mercadoPago->refundPayment($payment->mercadopago_id, 50.00);
```

## API Endpoints

The package automatically registers these API routes:

### Create Payments

```http
POST /api/mercadopago/payments/pix
POST /api/mercadopago/payments/card
POST /api/mercadopago/payments/boleto
```

**PIX Request:**
```json
{
    "amount": 100.00,
    "payer_email": "customer@example.com",
    "payer_name": "John Doe",
    "payer_document": "12345678909",
    "description": "Product purchase",
    "external_reference": "ORDER-123"
}
```

**PIX Response:**
```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "external_reference": "ORDER-123",
        "mercadopago_id": "12345678",
        "status": "action_required",
        "amount": "100.00",
        "currency": "BRL",
        "qr_code": "00020126580014br.gov.bcb.pix...",
        "qr_code_base64": "iVBORw0KGgoAAAANSUhEUgAA...",
        "qr_code_data_uri": "data:image/jpeg;base64,iVBORw0KGgo...",
        "ticket_url": "https://www.mercadopago.com.br/payments/...",
        "created_at": "2024-01-01T10:00:00.000000Z"
    }
}
```

**Card Request:**
```json
{
    "amount": 100.00,
    "token": "card_token_from_frontend",
    "payment_method_id": "visa",
    "installments": 1,
    "payer_email": "customer@example.com",
    "description": "Product purchase"
}
```

**Boleto Request:**
```json
{
    "amount": 100.00,
    "payer_email": "customer@example.com",
    "payer_name": "John Doe",
    "payer_document": "12345678909",
    "description": "Product purchase",
    "expiration_days": 3
}
```

### Get Payment

```http
GET /api/mercadopago/payments/{identifier}
```

Identifier can be: UUID, external_reference, or mercadopago_id

### Refund Payment

```http
POST /api/mercadopago/payments/{identifier}/refund
```

Request body (optional):
```json
{
    "amount": 50.00
}
```

### Webhook

```http
POST /api/mercadopago/webhook
```

This endpoint receives notifications from MercadoPago about payment status changes.

## Webhooks

### Configure Webhooks in MercadoPago

1. Go to: https://www.mercadopago.com.br/developers/panel/webhooks
2. Add your webhook URL: `https://your-domain.com/api/mercadopago/webhook`
3. Select topics: `payment`, `merchant_order`, `chargebacks`

### Webhook Security

The package validates webhook signatures using the `MERCADOPAGO_WEBHOOK_SECRET` environment variable.

### Webhook Processing

Webhooks are automatically processed and stored in the `mercadopago_webhooks` table. Payment statuses are updated accordingly.

```php
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoWebhook;

// Get unprocessed webhooks
$webhooks = MercadoPagoWebhook::unprocessed()->get();

// Get webhooks by topic
$paymentWebhooks = MercadoPagoWebhook::topic(WebhookTopic::PAYMENT)->get();
```

## Payment Status

The package uses PHP 8.1+ enums for type-safe status handling:

```php
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;

$payment->status === PaymentStatus::APPROVED;
$payment->status === PaymentStatus::PENDING;
$payment->status === PaymentStatus::REJECTED;

// Helper methods
$payment->isApproved(); // true if approved
$payment->isPending(); // true if pending/in_process/action_required
$payment->isRejected(); // true if rejected
$payment->requiresAction(); // true if action_required (PIX/Boleto)
```

Available statuses:
- `PENDING` - Awaiting payment
- `APPROVED` - Payment approved
- `AUTHORIZED` - Payment authorized (awaiting capture)
- `IN_PROCESS` - Payment processing
- `IN_MEDIATION` - Payment in dispute
- `REJECTED` - Payment rejected
- `CANCELLED` - Payment cancelled
- `REFUNDED` - Payment refunded
- `CHARGED_BACK` - Payment charged back
- `ACTION_REQUIRED` - Requires action (PIX/Boleto)

## Payment Methods

```php
use Eduardoks98\PaymentMercadoPago\Enums\PaymentMethod;

PaymentMethod::PIX;
PaymentMethod::CREDIT_CARD;
PaymentMethod::DEBIT_CARD;
PaymentMethod::BOLETO;
PaymentMethod::BANK_TRANSFER;
PaymentMethod::ACCOUNT_MONEY;
```

## Database Schema

### mercadopago_payments

Stores all payment transactions with complete MercadoPago data including PIX QR codes and Boleto details.

### mercadopago_webhooks

Stores all webhook notifications for audit trail and reprocessing.

## Testing

The package includes comprehensive test coverage:

```bash
composer test
```

## Official Documentation

This package is built following the official MercadoPago documentation:

- **PIX Integration**: https://www.mercadopago.com.br/developers/en/docs/checkout-api-orders/payment-integration/pix
- **PHP SDK**: https://github.com/mercadopago/sdk-php
- **Webhooks**: https://www.mercadopago.com.ar/developers/en/docs/your-integrations/notifications/webhooks
- **API Reference**: https://www.mercadopago.com.br/developers/en/reference

## Security

- All API requests use HTTPS
- Webhook signature validation
- Idempotency key support
- Secure credential storage via environment variables

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Credits

- **Author**: Eduardo Steffens
- **Email**: eduardo@example.com
- **GitHub**: https://github.com/eduardoks98

## Support

For issues and questions:
- GitHub Issues: https://github.com/eduardoks98/payment-mercadopago/issues
- MercadoPago Support: https://www.mercadopago.com.br/developers/en/support

---

Built with the official MercadoPago PHP SDK v3.x following best practices and official documentation.
