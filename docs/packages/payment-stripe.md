# Stripe Payment Package

> Stripe payment gateway integration with subscriptions and webhooks for Laravel.

## Overview

The `eduardoks98/payment-stripe` package provides complete Stripe integration including one-time payments, subscriptions, refunds, disputes, and comprehensive webhook handling.

## Installation

```bash
composer require eduardoks98/payment-stripe
```

## Configuration

### Environment Variables

```env
STRIPE_KEY=pk_test_xxxx
STRIPE_SECRET=sk_test_xxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxx
STRIPE_CURRENCY=brl
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\PaymentStripe\StripeServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\PaymentStripe\StripeServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### One-Time Payment

```php
use Eduardoks98\PaymentStripe\Services\StripeService;

$stripe = app(StripeService::class);

// Create Payment Intent
$paymentIntent = $stripe->createPaymentIntent([
    'amount' => 9990, // R$ 99,90 in cents
    'currency' => 'brl',
    'customer' => $customerId,
    'metadata' => [
        'order_id' => $order->id
    ]
]);

// Return client_secret to frontend
return response()->json([
    'clientSecret' => $paymentIntent->client_secret
]);
```

### Create Customer

```php
$customer = $stripe->createCustomer([
    'email' => $user->email,
    'name' => $user->name,
    'metadata' => [
        'user_id' => $user->id
    ]
]);

// Save customer ID
$user->update(['stripe_customer_id' => $customer->id]);
```

### Subscriptions

```php
// Create subscription
$subscription = $stripe->createSubscription([
    'customer' => $customerId,
    'items' => [
        ['price' => 'price_xxx'] // Price ID from Stripe Dashboard
    ],
    'trial_period_days' => 14,
]);

// Cancel subscription
$stripe->cancelSubscription($subscriptionId);

// Update subscription
$stripe->updateSubscription($subscriptionId, [
    'items' => [
        ['price' => 'price_new']
    ]
]);
```

### Refunds

```php
// Full refund
$refund = $stripe->createRefund($paymentIntentId);

// Partial refund
$refund = $stripe->createRefund($paymentIntentId, [
    'amount' => 5000 // R$ 50,00
]);
```

## Webhook Handling

### Supported Events (21 events)

```php
// Payment events
'payment_intent.succeeded'
'payment_intent.payment_failed'
'payment_intent.canceled'

// Subscription events
'customer.subscription.created'
'customer.subscription.updated'
'customer.subscription.deleted'
'customer.subscription.trial_will_end'

// Invoice events
'invoice.paid'
'invoice.payment_failed'
'invoice.upcoming'

// Charge events
'charge.succeeded'
'charge.failed'
'charge.refunded'
'charge.dispute.created'
'charge.dispute.closed'

// Customer events
'customer.created'
'customer.updated'
'customer.deleted'

// Checkout events
'checkout.session.completed'
'checkout.session.expired'
```

### Webhook Controller

```php
// routes/api.php (auto-registered)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
```

### Custom Webhook Handling

```php
// Create listener
class HandleStripePayment
{
    public function handle(PaymentSucceeded $event)
    {
        $order = Order::find($event->payload['metadata']['order_id']);
        $order->markAsPaid();
    }
}

// Register in EventServiceProvider
protected $listen = [
    PaymentSucceeded::class => [
        HandleStripePayment::class,
    ],
];
```

## Features

- Payment Intents API (SCA compliant)
- Subscriptions with trials
- Refunds and disputes
- 21 webhook events
- Automatic signature validation
- Idempotency support
- 3D Secure 2.0 (SCA)
- Multi-currency support

## Database Models

```php
// StripePayment model
$payment = StripePayment::where('payment_intent_id', $id)->first();
$payment->status; // succeeded, failed, pending
$payment->amount;
$payment->currency;
$payment->customer_id;

// Query scopes
StripePayment::successful()->get();
StripePayment::failed()->get();
StripePayment::forCustomer($customerId)->get();
```

## Dependencies

- `stripe/stripe-php` ^13.0
- `eduardoks98/base-api` ^1.0

## Security

- Webhook signature validation
- Idempotency keys
- PCI DSS compliance
- Secure API key storage

## Related

- [MercadoPago](./payment-mercadopago.md)
- [AbacatePay](./payment-abacatepay.md)
