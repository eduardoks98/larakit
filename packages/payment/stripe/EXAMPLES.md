# Stripe Payment Integration - Practical Examples

Real-world examples using the official Stripe PHP SDK v13.x

## Table of Contents

1. [Simple One-Time Payment](#simple-one-time-payment)
2. [Payment with Customer](#payment-with-customer)
3. [Saved Cards (Payment Methods)](#saved-cards-payment-methods)
4. [Subscription Setup](#subscription-setup)
5. [Frontend Integration](#frontend-integration)
6. [Manual Capture (Authorization)](#manual-capture-authorization)
7. [Refunds](#refunds)
8. [Error Handling](#error-handling)

---

## Simple One-Time Payment

```php
use Eduardoks98\PaymentStripe\Services\StripeService;

// In your controller
public function createPayment(Request $request)
{
    $stripeService = app(StripeService::class);

    try {
        // Create Payment Intent for $20.00
        $payment = $stripeService->createPaymentIntent(
            amount: 2000, // $20.00 in cents
            currency: 'usd',
            options: [
                'description' => 'T-shirt purchase',
                'metadata' => [
                    'order_id' => '12345',
                ],
            ]
        );

        // Return client secret to frontend
        return response()->json([
            'client_secret' => $payment->client_secret,
            'payment_id' => $payment->stripe_payment_intent_id,
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
```

**Frontend (Stripe.js)**

```html
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('pk_test_...');

// Get client secret from your backend
fetch('/api/stripe/payment-intents', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ amount: 2000, currency: 'usd' })
})
.then(res => res.json())
.then(data => {
    // Confirm payment with card element
    stripe.confirmCardPayment(data.client_secret, {
        payment_method: {
            card: cardElement,
            billing_details: { name: 'John Doe' }
        }
    }).then(result => {
        if (result.error) {
            console.error(result.error.message);
        } else {
            console.log('Payment succeeded!', result.paymentIntent);
        }
    });
});
</script>
```

---

## Payment with Customer

```php
use Eduardoks98\PaymentStripe\Services\CustomerService;
use Eduardoks98\PaymentStripe\Services\StripeService;

public function paymentWithCustomer(Request $request)
{
    $customerService = app(CustomerService::class);
    $stripeService = app(StripeService::class);

    // 1. Create or get customer
    $customer = $customerService->createCustomer([
        'email' => $request->user()->email,
        'name' => $request->user()->name,
        'metadata' => [
            'user_id' => $request->user()->id,
        ],
    ]);

    // 2. Create payment with customer
    $payment = $stripeService->createPaymentIntent(
        amount: 5000, // $50.00
        currency: 'usd',
        options: [
            'customer' => $customer->stripe_customer_id,
            'description' => 'Premium subscription',
        ]
    );

    return response()->json([
        'client_secret' => $payment->client_secret,
        'customer_id' => $customer->stripe_customer_id,
    ]);
}
```

---

## Saved Cards (Payment Methods)

```php
use Eduardoks98\PaymentStripe\Services\CustomerService;

public function saveCard(Request $request)
{
    $customerService = app(CustomerService::class);

    // Attach payment method to customer
    $paymentMethod = $customerService->attachPaymentMethod(
        paymentMethodId: $request->payment_method_id, // From Stripe.js
        customerId: $request->customer_id
    );

    // Set as default
    $customer = $customerService->setDefaultPaymentMethod(
        customerId: $request->customer_id,
        paymentMethodId: $request->payment_method_id
    );

    return response()->json(['success' => true]);
}

public function listSavedCards(Request $request)
{
    $customerService = app(CustomerService::class);

    $cards = $customerService->listPaymentMethods(
        customerId: $request->customer_id,
        type: 'card'
    );

    return response()->json(['cards' => $cards]);
}

public function chargeWithSavedCard(Request $request)
{
    $stripeService = app(StripeService::class);

    $payment = $stripeService->createPaymentIntent(
        amount: 3000,
        currency: 'usd',
        options: [
            'customer' => $request->customer_id,
            'payment_method' => $request->payment_method_id,
            'confirm' => true, // Auto-confirm
            'off_session' => true, // Charge without customer present
        ]
    );

    return response()->json(['status' => $payment->status->value]);
}
```

---

## Subscription Setup

```php
use Eduardoks98\PaymentStripe\Services\SubscriptionService;
use Eduardoks98\PaymentStripe\Services\CustomerService;

public function createSubscription(Request $request)
{
    $customerService = app(CustomerService::class);
    $subscriptionService = app(SubscriptionService::class);

    // 1. Get or create customer
    $customer = $customerService->findOrCreateCustomer($request->user());

    // 2. Attach payment method
    $customerService->attachPaymentMethod(
        paymentMethodId: $request->payment_method_id,
        customerId: $customer->stripe_customer_id
    );

    // 3. Set as default payment method
    $customerService->setDefaultPaymentMethod(
        customerId: $customer->stripe_customer_id,
        paymentMethodId: $request->payment_method_id
    );

    // 4. Create subscription
    $subscription = $subscriptionService->createSubscription(
        customerId: $customer->stripe_customer_id,
        items: [
            [
                'price' => 'price_1ABC...', // Price ID from Stripe Dashboard
                'quantity' => 1,
            ],
        ],
        options: [
            'trial_period_days' => 14, // 14-day free trial
            'metadata' => [
                'user_id' => $request->user()->id,
            ],
        ]
    );

    return response()->json([
        'subscription_id' => $subscription->stripe_subscription_id,
        'status' => $subscription->status->value,
        'trial_end' => $subscription->trial_end,
    ]);
}

public function upgradeSubscription(Request $request)
{
    $subscriptionService = app(SubscriptionService::class);

    // Change to new price (with prorated charge)
    $subscription = $subscriptionService->changeSubscriptionPrice(
        subscriptionId: $request->subscription_id,
        newPriceId: 'price_premium_123',
        options: [
            'proration_behavior' => 'create_prorations',
        ]
    );

    return response()->json(['success' => true]);
}

public function cancelSubscription(Request $request)
{
    $subscriptionService = app(SubscriptionService::class);

    // Cancel at end of billing period (customer keeps access)
    $subscription = $subscriptionService->cancelSubscription(
        subscriptionId: $request->subscription_id,
        cancelAtPeriodEnd: true
    );

    return response()->json([
        'status' => $subscription->status->value,
        'current_period_end' => $subscription->current_period_end,
    ]);
}
```

---

## Frontend Integration

**Complete Stripe Elements Example**

```html
<!DOCTYPE html>
<html>
<head>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .StripeElement {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <form id="payment-form">
        <div id="card-element"></div>
        <button type="submit">Pay $20.00</button>
        <div id="error-message"></div>
    </form>

    <script>
        const stripe = Stripe('pk_test_...');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const form = document.getElementById('payment-form');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // 1. Create PaymentIntent on backend
            const response = await fetch('/api/stripe/payment-intents', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                body: JSON.stringify({
                    amount: 2000,
                    currency: 'usd',
                    description: 'Product purchase'
                })
            });

            const { client_secret } = await response.json();

            // 2. Confirm payment with card
            const { error, paymentIntent } = await stripe.confirmCardPayment(
                client_secret,
                {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: 'John Doe',
                            email: 'john@example.com'
                        }
                    }
                }
            );

            if (error) {
                document.getElementById('error-message').textContent = error.message;
            } else if (paymentIntent.status === 'succeeded') {
                alert('Payment successful!');
                window.location.href = '/success';
            }
        });
    </script>
</body>
</html>
```

---

## Manual Capture (Authorization)

```php
// Step 1: Create payment with manual capture
public function authorizePayment(Request $request)
{
    $stripeService = app(StripeService::class);

    $payment = $stripeService->createPaymentIntent(
        amount: 5000,
        currency: 'usd',
        options: [
            'capture_method' => 'manual', // Don't capture immediately
            'payment_method' => $request->payment_method_id,
            'confirm' => true,
        ]
    );

    // Payment is authorized but not captured
    return response()->json([
        'payment_id' => $payment->stripe_payment_intent_id,
        'status' => $payment->status->value, // 'requires_capture'
    ]);
}

// Step 2: Capture later (after shipping, etc.)
public function capturePayment(Request $request)
{
    $stripeService = app(StripeService::class);

    $payment = $stripeService->capturePaymentIntent(
        paymentIntentId: $request->payment_id
    );

    return response()->json([
        'status' => $payment->status->value, // 'succeeded'
    ]);
}

// Or cancel authorization
public function cancelAuthorization(Request $request)
{
    $stripeService = app(StripeService::class);

    $payment = $stripeService->cancelPaymentIntent($request->payment_id);

    return response()->json(['status' => 'canceled']);
}
```

---

## Refunds

```php
use Stripe\StripeClient;

public function refundPayment(Request $request)
{
    $stripe = new StripeClient(config('stripe.secret_key'));

    // Full refund
    $refund = $stripe->refunds->create([
        'payment_intent' => $request->payment_intent_id,
    ]);

    // Partial refund
    $refund = $stripe->refunds->create([
        'payment_intent' => $request->payment_intent_id,
        'amount' => 1000, // Refund $10.00
    ]);

    return response()->json([
        'refund_id' => $refund->id,
        'status' => $refund->status,
        'amount' => $refund->amount,
    ]);
}
```

---

## Error Handling

```php
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;

public function handlePayment(Request $request)
{
    $stripeService = app(StripeService::class);

    try {
        $payment = $stripeService->createPaymentIntent(
            amount: $request->amount,
            currency: 'usd'
        );

        return response()->json(['success' => true, 'payment' => $payment]);

    } catch (CardException $e) {
        // Card was declined
        return response()->json([
            'error' => 'Your card was declined: ' . $e->getMessage(),
            'decline_code' => $e->getDeclineCode(),
        ], 402);

    } catch (RateLimitException $e) {
        // Too many requests to Stripe API
        return response()->json(['error' => 'Rate limit exceeded'], 429);

    } catch (InvalidRequestException $e) {
        // Invalid parameters
        return response()->json(['error' => 'Invalid request: ' . $e->getMessage()], 400);

    } catch (AuthenticationException $e) {
        // Authentication failed
        return response()->json(['error' => 'Authentication error'], 401);

    } catch (ApiConnectionException $e) {
        // Network communication failed
        return response()->json(['error' => 'Network error'], 503);

    } catch (ApiErrorException $e) {
        // Generic Stripe error
        return response()->json(['error' => 'Payment error: ' . $e->getMessage()], 500);

    } catch (\Exception $e) {
        // Other errors
        return response()->json(['error' => 'Unexpected error'], 500);
    }
}
```

---

## Webhook Event Handling

```php
// The package automatically handles webhooks, but you can listen to events:

// In app/Providers/EventServiceProvider.php
protected $listen = [
    // Create custom events
    'payment.succeeded' => [
        SendPaymentConfirmationEmail::class,
        UpdateOrderStatus::class,
    ],
];

// In WebhookController, fire custom events:
protected function handlePaymentIntentSucceeded($event): bool
{
    $paymentIntent = $event->data->object;
    $payment = $this->stripeService->syncPaymentIntent($paymentIntent);

    // Fire custom event
    event('payment.succeeded', ['payment' => $payment]);

    return true;
}
```

---

## Testing with Stripe CLI

```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Forward webhooks to local server
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Trigger test events
stripe trigger payment_intent.succeeded
stripe trigger customer.subscription.created
stripe trigger invoice.payment_failed
```

---

These examples cover the most common use cases with Stripe. For more details, refer to the official Stripe documentation:

- https://stripe.com/docs/api
- https://stripe.com/docs/payments/accept-a-payment
- https://stripe.com/docs/billing/subscriptions/overview
