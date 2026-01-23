# Package Structure

```
payment-stripe/
├── config/
│   └── stripe.php                      # Stripe configuration file
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_stripe_customers_table.php
│       ├── 2024_01_01_000002_create_stripe_payments_table.php
│       └── 2024_01_01_000003_create_stripe_subscriptions_table.php
├── routes/
│   └── api.php                         # API routes for Stripe endpoints
├── src/
│   ├── Enums/
│   │   ├── PaymentStatus.php          # Payment Intent status enum
│   │   └── SubscriptionStatus.php     # Subscription status enum
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PaymentController.php  # Payment API endpoints
│   │   │   └── WebhookController.php  # Webhook event handler
│   │   └── Middleware/
│   │       └── VerifyStripeWebhook.php # Webhook signature verification
│   ├── Models/
│   │   ├── StripeCustomer.php         # Stripe Customer model
│   │   ├── StripePayment.php          # Stripe Payment model
│   │   └── StripeSubscription.php     # Stripe Subscription model
│   ├── Services/
│   │   ├── CustomerService.php        # Customer API service
│   │   ├── StripeService.php          # Payment Intents API service
│   │   └── SubscriptionService.php    # Subscription API service
│   └── StripeServiceProvider.php      # Laravel service provider
├── tests/
│   └── Feature/
│       └── StripeServiceTest.php       # Feature tests
├── .env.example                        # Environment variables example
├── .gitignore                          # Git ignore file
├── composer.json                       # Composer dependencies
├── EXAMPLES.md                         # Practical usage examples
├── LICENSE                             # MIT License
├── README.md                           # Main documentation
└── STRUCTURE.md                        # This file
```

## Architecture Overview

### Services Layer

**StripeService** (Payment Intents API)
- `createPaymentIntent()` - Create new payment
- `confirmPaymentIntent()` - Confirm payment
- `cancelPaymentIntent()` - Cancel payment
- `capturePaymentIntent()` - Capture authorized payment
- `updatePaymentIntent()` - Update payment
- `syncPaymentIntent()` - Sync Stripe data to database

**CustomerService** (Customer API)
- `createCustomer()` - Create Stripe customer
- `updateCustomer()` - Update customer
- `deleteCustomer()` - Delete customer
- `attachPaymentMethod()` - Attach payment method
- `detachPaymentMethod()` - Detach payment method
- `listPaymentMethods()` - List customer's payment methods
- `setDefaultPaymentMethod()` - Set default payment method
- `findOrCreateCustomer()` - Find or create customer by user

**SubscriptionService** (Subscription API)
- `createSubscription()` - Create subscription
- `updateSubscription()` - Update subscription
- `cancelSubscription()` - Cancel subscription
- `resumeSubscription()` - Resume subscription
- `pauseSubscription()` - Pause subscription
- `unpauseSubscription()` - Unpause subscription
- `changeSubscriptionPrice()` - Change price (upgrade/downgrade)
- `listSubscriptions()` - List customer subscriptions

### Models Layer

**StripePayment**
- Stores Payment Intent data locally
- Relationships: `user()`, `customer()`
- Scopes: `successful()`, `pending()`
- Helpers: `isSuccessful()`, `isPending()`, `isFailed()`

**StripeCustomer**
- Stores Customer data locally
- Relationships: `user()`, `payments()`, `subscriptions()`, `activeSubscriptions()`

**StripeSubscription**
- Stores Subscription data locally
- Relationships: `user()`, `customer()`
- Scopes: `active()`, `canceled()`, `onTrial()`
- Helpers: `isActive()`, `isOnTrial()`, `isCanceled()`, `hasEnded()`

### Controllers Layer

**PaymentController**
- REST API endpoints for payments
- Endpoints: create, confirm, cancel, capture, get payment
- Customer creation
- Subscription creation and cancellation

**WebhookController**
- Handles all Stripe webhook events
- Event handlers for Payment Intents, Subscriptions, Invoices, Customers
- Automatic data synchronization
- Extensible for custom event handling

### Middleware

**VerifyStripeWebhook**
- Verifies webhook signatures using Stripe SDK
- Prevents unauthorized webhook calls
- Uses webhook secret from configuration

### Enums

**PaymentStatus**
- Type-safe payment statuses
- Based on official Stripe PaymentIntent statuses
- Helper methods: `isPending()`, `isCompleted()`, `isFailed()`

**SubscriptionStatus**
- Type-safe subscription statuses
- Based on official Stripe Subscription statuses
- Helper methods: `isActive()`, `hasIssues()`, `isEnded()`

### Configuration

**config/stripe.php**
- API keys (secret, publishable)
- Webhook configuration
- Payment settings
- Subscription settings
- Customer settings
- Logging options

### Migrations

**stripe_customers**
- `stripe_customer_id` (unique, indexed)
- User relationship
- Customer details (email, name, phone, address)
- Metadata
- Default payment method

**stripe_payments**
- `stripe_payment_intent_id` (unique, indexed)
- User and customer relationships
- Amount and currency
- Payment status
- Payment method details
- Client secret
- Error information

**stripe_subscriptions**
- `stripe_subscription_id` (unique, indexed)
- User and customer relationships
- Price and product IDs
- Subscription status
- Period dates
- Trial dates
- Cancellation dates

## Data Flow

### Payment Creation Flow

1. **Frontend** → Creates payment request
2. **PaymentController** → Receives request
3. **StripeService** → Calls Stripe API
4. **Stripe API** → Creates PaymentIntent
5. **StripeService** → Syncs to database (StripePayment model)
6. **PaymentController** → Returns client_secret to frontend
7. **Frontend** → Confirms payment with Stripe.js
8. **Stripe** → Sends webhook event
9. **WebhookController** → Handles event
10. **StripeService** → Updates payment status in database

### Subscription Creation Flow

1. **Frontend** → Collects payment method
2. **PaymentController** → Receives request
3. **CustomerService** → Creates/finds customer
4. **CustomerService** → Attaches payment method
5. **SubscriptionService** → Creates subscription via Stripe API
6. **SubscriptionService** → Syncs to database (StripeSubscription model)
7. **Stripe** → Sends webhook events (subscription.created, invoice.paid)
8. **WebhookController** → Handles events
9. **SubscriptionService** → Updates subscription in database

### Webhook Event Flow

1. **Stripe** → Event occurs (payment succeeds, subscription renews, etc.)
2. **Stripe** → Sends webhook to `/api/stripe/webhook`
3. **VerifyStripeWebhook** → Verifies signature
4. **WebhookController** → Routes event to appropriate handler
5. **Service Layer** → Syncs data to database
6. **Application Events** → Custom events fired (optional)
7. **Response** → 200 OK to Stripe

## Integration Points

### Frontend Integration
- Stripe.js v3 for card elements
- Payment Intents for SCA-ready payments
- Setup Intents for saving cards
- Customer Portal for subscription management

### Backend Integration
- Service Layer for business logic
- Controllers for API endpoints
- Models for data persistence
- Webhooks for automatic sync

### Database Integration
- Migrations for schema
- Models with relationships
- Scopes for queries
- Type casting with Enums

## Security Features

1. **Webhook Verification** - Signature verification on all webhooks
2. **HTTPS Required** - Production webhooks require HTTPS
3. **Secret Key Protection** - Keys stored in .env, never exposed
4. **PCI Compliance** - Card data never touches your server
5. **SCA Support** - 3D Secure 2 via Payment Intents API

## Extensibility

### Custom Event Listeners
Add custom listeners in `EventServiceProvider`:
```php
protected $listen = [
    'payment.succeeded' => [SendPaymentEmail::class],
];
```

### Custom Webhook Handlers
Extend `WebhookController` and override handlers:
```php
protected function handlePaymentIntentSucceeded($event): bool
{
    parent::handlePaymentIntentSucceeded($event);
    // Your custom logic
    return true;
}
```

### Custom Services
Extend services for custom functionality:
```php
class MyStripeService extends StripeService
{
    public function customMethod() { }
}
```

## Official Stripe SDK Usage

This package uses **stripe/stripe-php v13.x** exclusively:

```php
use Stripe\StripeClient;

$stripe = new StripeClient([
    'api_key' => config('stripe.secret_key'),
    'stripe_version' => config('stripe.api_version'),
]);

// All Stripe operations use official SDK methods
$paymentIntent = $stripe->paymentIntents->create([...]);
$customer = $stripe->customers->create([...]);
$subscription = $stripe->subscriptions->create([...]);
```

## Dependencies

Required:
- PHP ^8.1|^8.2|^8.3
- Laravel ^10.0|^11.0|^12.0
- stripe/stripe-php ^13.0

Optional:
- Laravel Sanctum (for API authentication)
- Laravel Events (for custom event handling)

## Testing Strategy

1. **Unit Tests** - Test service methods with mocked Stripe responses
2. **Feature Tests** - Test full payment/subscription flows
3. **Integration Tests** - Test with Stripe test mode
4. **Webhook Tests** - Test webhook handling with Stripe CLI

## Performance Considerations

1. **Database Indexing** - All Stripe IDs are indexed
2. **Lazy Loading** - Relationships use lazy loading
3. **Caching** - Consider caching customer/subscription data
4. **Queue Jobs** - Webhook processing can be queued
5. **Logging** - Configurable logging for debugging

## Monitoring

Recommended monitoring:
- Failed payments (webhook failures)
- Subscription cancellations
- Payment errors
- API rate limits
- Database sync issues

## Support

For issues or questions:
- GitHub Issues: https://github.com/eduardoks98/payment-stripe
- Stripe Documentation: https://stripe.com/docs
- Stripe Support: https://support.stripe.com
