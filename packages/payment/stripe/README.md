# Stripe Payment Integration for Laravel

Complete Stripe payment integration using the official **stripe/stripe-php v13.x SDK** with Payment Intents API, Subscriptions, and Webhooks.

## Features

- **Payment Intents API** - Modern SCA-ready payment processing
- **Customer Management** - Create and manage Stripe customers
- **Subscriptions** - Recurring billing and subscription management
- **Webhooks** - Automatic webhook handling with signature verification
- **Database Sync** - Local storage of Stripe data for quick access
- **Type-Safe** - Enums for payment and subscription statuses
- **Laravel Integration** - Service provider, migrations, and routes included

## Installation

```bash
composer require eduardoks98/payment-stripe
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=stripe-config
```

### Run Migrations

```bash
php artisan migrate
```

## Configuration

Add your Stripe credentials to `.env`:

```env
# Stripe API Keys (from https://dashboard.stripe.com/apikeys)
STRIPE_SECRET_KEY=sk_test_51...
STRIPE_PUBLISHABLE_KEY=pk_test_51...

# Stripe Webhook Secret (from https://dashboard.stripe.com/webhooks)
STRIPE_WEBHOOK_SECRET=whsec_...

# Currency (ISO 4217)
STRIPE_CURRENCY=usd

# Optional: API Version (YYYY-MM-DD format)
STRIPE_API_VERSION=2023-10-16
```

## Usage Examples

### 1. Create a Payment Intent

```php
use Eduardoks98\PaymentStripe\Services\StripeService;

$stripeService = app(StripeService::class);

// Create a payment for $20.00 USD
$payment = $stripeService->createPaymentIntent(
    amount: 2000, // Amount in cents
    currency: 'usd',
    options: [
        'customer' => 'cus_xxxxx',
        'payment_method' => 'pm_xxxxx',
        'description' => 'Order #12345',
        'metadata' => [
            'order_id' => '12345',
            'user_id' => '1',
        ],
    ]
);

// Get client secret for frontend
$clientSecret = $payment->client_secret;
```

### 2. Confirm a Payment Intent

```php
// Confirm payment (triggers 3D Secure if needed)
$payment = $stripeService->confirmPaymentIntent(
    paymentIntentId: 'pi_xxxxx',
    options: [
        'payment_method' => 'pm_xxxxx',
        'return_url' => 'https://example.com/payment/return',
    ]
);

// Check status
if ($payment->status === PaymentStatus::SUCCEEDED) {
    // Payment succeeded!
}
```

### 3. Cancel a Payment Intent

```php
$payment = $stripeService->cancelPaymentIntent('pi_xxxxx');
```

### 4. Capture a Payment (Manual Capture Mode)

```php
// Create with manual capture
$payment = $stripeService->createPaymentIntent(
    amount: 2000,
    currency: 'usd',
    options: [
        'capture_method' => 'manual',
        'payment_method' => 'pm_xxxxx',
        'confirm' => true,
    ]
);

// Later, capture the payment
$payment = $stripeService->capturePaymentIntent('pi_xxxxx');
```

### 5. Create a Customer

```php
use Eduardoks98\PaymentStripe\Services\CustomerService;

$customerService = app(CustomerService::class);

$customer = $customerService->createCustomer([
    'email' => 'customer@example.com',
    'name' => 'John Doe',
    'phone' => '+1234567890',
    'metadata' => [
        'user_id' => '1',
    ],
]);

$stripeCustomerId = $customer->stripe_customer_id;
```

### 6. Attach a Payment Method to Customer

```php
// Attach payment method
$paymentMethod = $customerService->attachPaymentMethod(
    paymentMethodId: 'pm_xxxxx',
    customerId: 'cus_xxxxx'
);

// Set as default
$customer = $customerService->setDefaultPaymentMethod(
    customerId: 'cus_xxxxx',
    paymentMethodId: 'pm_xxxxx'
);
```

### 7. Create a Subscription

```php
use Eduardoks98\PaymentStripe\Services\SubscriptionService;

$subscriptionService = app(SubscriptionService::class);

$subscription = $subscriptionService->createSubscription(
    customerId: 'cus_xxxxx',
    items: [
        [
            'price' => 'price_xxxxx', // Price ID from Stripe Dashboard
            'quantity' => 1,
        ],
    ],
    options: [
        'trial_period_days' => 14,
        'metadata' => [
            'user_id' => '1',
        ],
    ]
);
```

### 8. Change Subscription Price (Upgrade/Downgrade)

```php
$subscription = $subscriptionService->changeSubscriptionPrice(
    subscriptionId: 'sub_xxxxx',
    newPriceId: 'price_yyyyy',
    options: [
        'proration_behavior' => 'create_prorations', // or 'none', 'always_invoice'
    ]
);
```

### 9. Cancel a Subscription

```php
// Cancel immediately
$subscription = $subscriptionService->cancelSubscription('sub_xxxxx');

// Cancel at end of billing period
$subscription = $subscriptionService->cancelSubscription(
    subscriptionId: 'sub_xxxxx',
    cancelAtPeriodEnd: true
);
```

### 10. Pause/Resume a Subscription

```php
// Pause subscription
$subscription = $subscriptionService->pauseSubscription('sub_xxxxx');

// Resume subscription
$subscription = $subscriptionService->unpauseSubscription('sub_xxxxx');
```

## API Endpoints

The package automatically registers these API routes:

### Payment Intents

```bash
# Create Payment Intent
POST /api/stripe/payment-intents
{
  "amount": 2000,
  "currency": "usd",
  "customer_id": "cus_xxxxx",
  "description": "Order #12345"
}

# Get Payment Intent
GET /api/stripe/payment-intents/{id}

# Confirm Payment Intent
POST /api/stripe/payment-intents/{id}/confirm
{
  "payment_method": "pm_xxxxx"
}

# Cancel Payment Intent
POST /api/stripe/payment-intents/{id}/cancel

# Capture Payment Intent
POST /api/stripe/payment-intents/{id}/capture
```

### Customers

```bash
# Create Customer
POST /api/stripe/customers
{
  "email": "customer@example.com",
  "name": "John Doe"
}
```

### Subscriptions

```bash
# Create Subscription
POST /api/stripe/subscriptions
{
  "customer_id": "cus_xxxxx",
  "price_id": "price_xxxxx",
  "trial_period_days": 14
}

# Cancel Subscription
POST /api/stripe/subscriptions/{id}/cancel
{
  "cancel_at_period_end": true
}
```

## Webhooks

The package automatically handles these Stripe webhook events:

### Setup Webhooks

1. Go to https://dashboard.stripe.com/webhooks
2. Create a new webhook endpoint: `https://yourdomain.com/api/stripe/webhook`
3. Select events to listen for (or select "Select all events")
4. Copy the webhook signing secret to `.env` as `STRIPE_WEBHOOK_SECRET`

### Handled Events

**Payment Intent Events:**
- `payment_intent.succeeded` - Payment completed successfully
- `payment_intent.payment_failed` - Payment failed
- `payment_intent.canceled` - Payment canceled
- `payment_intent.created` - Payment created
- `payment_intent.processing` - Payment processing

**Subscription Events:**
- `customer.subscription.created` - Subscription created
- `customer.subscription.updated` - Subscription updated
- `customer.subscription.deleted` - Subscription canceled
- `customer.subscription.trial_will_end` - Trial ending soon

**Invoice Events:**
- `invoice.created` - Invoice created
- `invoice.finalized` - Invoice finalized
- `invoice.paid` - Invoice paid
- `invoice.payment_failed` - Invoice payment failed

**Customer Events:**
- `customer.created` - Customer created
- `customer.updated` - Customer updated
- `customer.deleted` - Customer deleted

**Payment Method Events:**
- `payment_method.attached` - Payment method attached
- `payment_method.detached` - Payment method detached

**Charge Events:**
- `charge.succeeded` - Charge succeeded
- `charge.failed` - Charge failed
- `charge.refunded` - Charge refunded

### Custom Event Handling

You can listen for webhook events in your application:

```php
// In EventServiceProvider.php
protected $listen = [
    \Eduardoks98\PaymentStripe\Events\PaymentSucceeded::class => [
        \App\Listeners\SendPaymentConfirmation::class,
    ],
];
```

## Testing

### Test Mode

Use Stripe test keys (starting with `sk_test_` and `pk_test_`) for development.

### Test Cards

```
# Successful payment
4242 4242 4242 4242

# Requires authentication (3D Secure)
4000 0027 6000 3184

# Declined card
4000 0000 0000 0002
```

Full list: https://stripe.com/docs/testing

### Webhook Testing

Use Stripe CLI for local webhook testing:

```bash
# Install Stripe CLI
# https://stripe.com/docs/stripe-cli

# Forward webhooks to local endpoint
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Trigger test events
stripe trigger payment_intent.succeeded
stripe trigger customer.subscription.created
```

## Models

### StripePayment

```php
use Eduardoks98\PaymentStripe\Models\StripePayment;

$payment = StripePayment::where('stripe_payment_intent_id', 'pi_xxxxx')->first();

// Check status
if ($payment->isSuccessful()) { }
if ($payment->isPending()) { }
if ($payment->isFailed()) { }

// Get amount in dollars
$amount = $payment->amount_in_dollars;

// Relationships
$user = $payment->user;
$customer = $payment->customer;
```

### StripeCustomer

```php
use Eduardoks98\PaymentStripe\Models\StripeCustomer;

$customer = StripeCustomer::where('stripe_customer_id', 'cus_xxxxx')->first();

// Relationships
$user = $customer->user;
$payments = $customer->payments;
$subscriptions = $customer->subscriptions;
$activeSubscriptions = $customer->activeSubscriptions;
```

### StripeSubscription

```php
use Eduardoks98\PaymentStripe\Models\StripeSubscription;

$subscription = StripeSubscription::where('stripe_subscription_id', 'sub_xxxxx')->first();

// Check status
if ($subscription->isActive()) { }
if ($subscription->isOnTrial()) { }
if ($subscription->isCanceled()) { }

// Relationships
$user = $subscription->user;
$customer = $subscription->customer;
```

## Enums

### PaymentStatus

```php
use Eduardoks98\PaymentStripe\Enums\PaymentStatus;

PaymentStatus::REQUIRES_PAYMENT_METHOD
PaymentStatus::REQUIRES_CONFIRMATION
PaymentStatus::REQUIRES_ACTION
PaymentStatus::PROCESSING
PaymentStatus::REQUIRES_CAPTURE
PaymentStatus::CANCELED
PaymentStatus::SUCCEEDED
```

### SubscriptionStatus

```php
use Eduardoks98\PaymentStripe\Enums\SubscriptionStatus;

SubscriptionStatus::INCOMPLETE
SubscriptionStatus::INCOMPLETE_EXPIRED
SubscriptionStatus::TRIALING
SubscriptionStatus::ACTIVE
SubscriptionStatus::PAST_DUE
SubscriptionStatus::CANCELED
SubscriptionStatus::UNPAID
SubscriptionStatus::PAUSED
```

## Official Documentation

- **Stripe API Documentation**: https://stripe.com/docs/api
- **Payment Intents API**: https://stripe.com/docs/api/payment_intents
- **Customer API**: https://stripe.com/docs/api/customers
- **Subscription API**: https://stripe.com/docs/api/subscriptions
- **Webhooks**: https://stripe.com/docs/webhooks
- **stripe-php SDK**: https://github.com/stripe/stripe-php

## Security

- Webhook signatures are automatically verified using `VerifyStripeWebhook` middleware
- Never expose your secret key (`sk_test_` or `sk_live_`)
- Always use HTTPS in production
- Store webhook secret securely in `.env`

## License

MIT License

## Author

**Eduardo Steffens**
- GitHub: [@eduardoks98](https://github.com/eduardoks98)
- Email: eduardo@example.com

---

Built with the official **stripe/stripe-php v13.x SDK**
