# AbacatePay Payment Package

> AbacatePay PIX integration for indie hackers and small businesses in Brazil.

## Overview

The `eduardoks98/payment-abacatepay` package provides simple PIX integration via AbacatePay, designed specifically for indie hackers with transparent pricing (R$ 0,80 fixed fee per transaction).

## Installation

```bash
composer require eduardoks98/payment-abacatepay
```

## Configuration

### Environment Variables

```env
ABACATEPAY_API_KEY=your_api_key
ABACATEPAY_WEBHOOK_SECRET=your_webhook_secret
ABACATEPAY_API_URL=https://api.abacatepay.com/v1
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\PaymentAbacatePay\AbacatePayServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\PaymentAbacatePay\AbacatePayServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### One-Time PIX Payment

```php
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;

$abacate = app(AbacatePayService::class);

// Create PIX billing
$billing = $abacate->createBilling([
    'frequency' => 'ONE_TIME',
    'methods' => ['PIX'],
    'products' => [
        [
            'externalId' => 'product_123',
            'name' => 'Premium Plan',
            'quantity' => 1,
            'price' => 4990 // R$ 49,90 in cents
        ]
    ],
    'customer' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'cellphone' => '11987654321',
        'taxId' => '12345678909' // CPF
    ],
    'metadata' => [
        'order_id' => $order->id
    ]
]);

// Returns checkout URL
return response()->json([
    'checkout_url' => $billing->url,
    'billing_id' => $billing->id
]);
```

### Recurring Payment (Subscription)

```php
// Monthly subscription
$billing = $abacate->createBilling([
    'frequency' => 'MONTHLY',
    'methods' => ['PIX'],
    'products' => [
        [
            'externalId' => 'subscription_pro',
            'name' => 'Pro Plan - Monthly',
            'quantity' => 1,
            'price' => 2990 // R$ 29,90/month
        ]
    ],
    'customer' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'taxId' => '12345678909'
    ]
]);

// Yearly subscription
$billing = $abacate->createBilling([
    'frequency' => 'YEARLY',
    'methods' => ['PIX'],
    'products' => [
        [
            'externalId' => 'subscription_pro_yearly',
            'name' => 'Pro Plan - Yearly',
            'quantity' => 1,
            'price' => 29900 // R$ 299,00/year
        ]
    ],
    'customer' => [/* ... */]
]);
```

### Get Billing Status

```php
$billing = $abacate->getBilling($billingId);

// Check status
if ($billing->status === 'PAID') {
    // Process order
}
```

### List Customer Billings

```php
$billings = $abacate->listBillings([
    'customer_email' => 'john@example.com',
    'status' => 'PAID'
]);
```

## Webhook Handling

### Supported Events

```php
'BILLING_CREATED'   // Billing created
'BILLING_PAID'      // Payment confirmed
'BILLING_EXPIRED'   // Payment expired
'BILLING_CANCELLED' // Payment cancelled
```

### Webhook Configuration

```php
// Auto-registered route
Route::post('/webhooks/abacatepay', [AbacatePayWebhookController::class, 'handle']);

// Configure in AbacatePay Dashboard:
// URL: https://yourapp.com/api/webhooks/abacatepay
```

### Custom Webhook Handler

```php
use Eduardoks98\PaymentAbacatePay\Events\BillingPaid;

class HandleAbacatePayment
{
    public function handle(BillingPaid $event)
    {
        $billing = $event->billing;
        $order = Order::find($billing->metadata['order_id']);
        $order->markAsPaid();
    }
}
```

## Billing Frequencies

| Frequency | Description |
|-----------|-------------|
| `ONE_TIME` | Single payment |
| `MONTHLY` | Monthly recurring |
| `YEARLY` | Yearly recurring |

## Features

- PIX payments
- One-time and recurring billing
- Transparent pricing (R$ 0,80/tx)
- Simple checkout flow
- Webhook notifications
- Customer management
- No monthly fees

## Pricing

AbacatePay has simple, transparent pricing:
- **R$ 0,80** fixed fee per transaction
- **No monthly fees**
- **No setup fees**
- Perfect for indie hackers!

## Database Models

```php
$payment = AbacatePayPayment::find($id);
$payment->status; // PENDING, PAID, EXPIRED, CANCELLED
$payment->frequency; // ONE_TIME, MONTHLY, YEARLY
$payment->amount;
$payment->checkout_url;

// Scopes
AbacatePayPayment::paid()->get();
AbacatePayPayment::pending()->get();
AbacatePayPayment::recurring()->get();
```

## Dependencies

- `abacatepay/php-sdk` ^1.0
- `eduardoks98/base-api` ^1.0

## Security

- Webhook signature validation
- Secure API key storage
- HTTPS only

## Why AbacatePay?

1. **Simple**: Easy integration, no complex setup
2. **Cheap**: R$ 0,80 per transaction, no monthly fees
3. **Fast**: PIX = instant payment confirmation
4. **Brazilian**: Made for the Brazilian market
5. **Indie-friendly**: Perfect for solo developers and small teams

## Related

- [Stripe](./payment-stripe.md)
- [MercadoPago](./payment-mercadopago.md)
