# MercadoPago Payment Package

> MercadoPago integration with PIX, Boleto, and cards for Brazil/LATAM.

## Overview

The `eduardoks98/payment-mercadopago` package provides complete MercadoPago integration for the Brazilian and Latin American markets, supporting PIX with QR Code, Boleto, credit/debit cards, and split payments.

## Installation

```bash
composer require eduardoks98/payment-mercadopago
```

## Configuration

### Environment Variables

```env
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxx
MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxx
MERCADOPAGO_WEBHOOK_SECRET=your_webhook_secret
MERCADOPAGO_STATEMENT_DESCRIPTOR=MyStore
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\PaymentMercadoPago\MercadoPagoServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\PaymentMercadoPago\MercadoPagoServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### PIX Payment (Orders API)

```php
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;

$mp = app(MercadoPagoService::class);

// Create PIX payment
$payment = $mp->createPixPayment([
    'transaction_amount' => 99.90,
    'description' => 'Pedido #12345',
    'payer' => [
        'email' => 'customer@email.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'identification' => [
            'type' => 'CPF',
            'number' => '12345678909'
        ]
    ],
    'metadata' => [
        'order_id' => $order->id
    ]
]);

// PIX response includes:
// - qr_code: PIX copia e cola
// - qr_code_base64: QR Code image (base64)
// - ticket_url: Payment page URL

return response()->json([
    'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
    'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
]);
```

### Boleto Payment

```php
$payment = $mp->createBoletoPayment([
    'transaction_amount' => 199.90,
    'description' => 'Pedido #12345',
    'payment_method_id' => 'bolbradesco', // or 'pec' for lottery
    'payer' => [
        'email' => 'customer@email.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'identification' => [
            'type' => 'CPF',
            'number' => '12345678909'
        ],
        'address' => [
            'zip_code' => '01310100',
            'street_name' => 'Av. Paulista',
            'street_number' => '1000',
            'neighborhood' => 'Bela Vista',
            'city' => 'Sao Paulo',
            'federal_unit' => 'SP'
        ]
    ]
]);

// Returns boleto URL and barcode
return response()->json([
    'boleto_url' => $payment->transaction_details->external_resource_url,
    'barcode' => $payment->barcode->content,
]);
```

### Credit Card Payment

```php
$payment = $mp->createCardPayment([
    'transaction_amount' => 299.90,
    'token' => $cardToken, // From MercadoPago.js
    'description' => 'Pedido #12345',
    'installments' => 3,
    'payment_method_id' => 'visa',
    'payer' => [
        'email' => 'customer@email.com',
        'identification' => [
            'type' => 'CPF',
            'number' => '12345678909'
        ]
    ]
]);
```

### Refunds

```php
// Full refund
$refund = $mp->refundPayment($paymentId);

// Partial refund
$refund = $mp->refundPayment($paymentId, 50.00);
```

### Split Payments (Marketplace)

```php
$payment = $mp->createSplitPayment([
    'transaction_amount' => 100.00,
    'application_fee' => 10.00, // Platform fee (10%)
    'marketplace_owner_access_token' => config('mercadopago.marketplace_token'),
    // ... rest of payment data
]);
```

## Webhook Handling

### Supported Events

```php
'payment.created'
'payment.updated'
'payment.approved'
'payment.rejected'
'payment.cancelled'
'payment.refunded'
'plan.created'
'subscription.created'
'subscription.updated'
'subscription.cancelled'
```

### Webhook Configuration

```php
// Auto-registered route
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle']);

// Configure in MercadoPago Dashboard:
// URL: https://yourapp.com/api/webhooks/mercadopago
```

## Payment Methods

| Method | ID | Description |
|--------|-----|-------------|
| PIX | `pix` | Instant payment |
| Boleto Bradesco | `bolbradesco` | Bank slip |
| Boleto Lottery | `pec` | Lottery payment |
| Visa | `visa` | Credit card |
| Mastercard | `master` | Credit card |
| Elo | `elo` | Credit card |
| Hipercard | `hipercard` | Credit card |
| American Express | `amex` | Credit card |

## Features

- PIX with QR Code (base64)
- Boleto bancario
- Credit/debit cards
- Installments (parcelas)
- Split payments (marketplace)
- Webhook signature validation
- Automatic status tracking
- Refunds (full and partial)

## Database Models

```php
$payment = MercadoPagoPayment::find($id);
$payment->status; // approved, pending, rejected
$payment->payment_type; // pix, boleto, credit_card
$payment->amount;
$payment->pix_qr_code;

// Scopes
MercadoPagoPayment::approved()->get();
MercadoPagoPayment::pending()->get();
MercadoPagoPayment::pix()->get();
```

## Dependencies

- `mercadopago/dx-php` ^3.0
- `eduardoks98/base-api` ^1.0

## Security

- Webhook signature validation (x-signature header)
- Idempotency keys
- Secure credential storage
- PCI DSS compliance for card data

## Related

- [Stripe](./payment-stripe.md)
- [AbacatePay](./payment-abacatepay.md)
