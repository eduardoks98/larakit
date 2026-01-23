# AbacatePay Usage Examples

This document provides practical examples of using the AbacatePay package, demonstrating how it wraps the official AbacatePay PHP SDK.

## Table of Contents

- [Basic Setup](#basic-setup)
- [Creating a One-Time Payment](#creating-a-one-time-payment)
- [Creating a Subscription](#creating-a-subscription)
- [Multiple Products](#multiple-products)
- [Using Direct SDK Access](#using-direct-sdk-access)
- [Webhook Integration](#webhook-integration)
- [Database Queries](#database-queries)

## Basic Setup

### Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

class PaymentController extends Controller
{
    public function __construct(
        private AbacatePayService $abacatePayService
    ) {}

    // Your payment methods here
}
```

## Creating a One-Time Payment

### PIX Payment

```php
public function createPixPayment(Request $request)
{
    $billingData = new BillingData(
        frequency: Frequency::ONE_TIME,
        methods: [PaymentMethod::PIX],
        products: [
            new ProductData(
                name: 'Premium Course Access',
                price: 19900, // R$ 199.00 in cents
                quantity: 1,
                description: 'Lifetime access to premium courses'
            )
        ],
        customer: new CustomerData(
            email: $request->user()->email,
            name: $request->user()->name,
            cellphone: $request->input('phone'),
            taxId: $request->input('cpf')
        ),
        metadata: [
            'user_id' => $request->user()->id,
            'order_number' => 'ORD-' . now()->timestamp,
        ],
        returnUrl: route('payment.return'),
        completionUrl: route('payment.success')
    );

    $response = $this->abacatePayService->createBilling(
        $billingData,
        $request->user()->id
    );

    // Store billing ID in session for tracking
    session(['last_billing_id' => $response['id']]);

    // Redirect to payment page
    return redirect($response['url']);
}
```

### Credit Card Payment

```php
public function createCardPayment(Request $request)
{
    $billingData = new BillingData(
        frequency: Frequency::ONE_TIME,
        methods: [PaymentMethod::CARD],
        products: [
            new ProductData(
                name: 'Product Purchase',
                price: 9990, // R$ 99.90
            )
        ],
        customer: CustomerData::fromUser($request->user())
    );

    $response = $this->abacatePayService->createBilling($billingData);

    return response()->json([
        'payment_url' => $response['url'],
        'billing_id' => $response['id'],
    ]);
}
```

### Multiple Payment Methods

```php
public function createFlexiblePayment(Request $request)
{
    // Allow customer to choose between PIX and Card
    $billingData = new BillingData(
        frequency: Frequency::ONE_TIME,
        methods: [
            PaymentMethod::PIX,
            PaymentMethod::CARD
        ],
        products: [
            new ProductData(
                name: 'Digital Product',
                price: 14900, // R$ 149.00
            )
        ],
        customer: CustomerData::fromUser($request->user())
    );

    $response = $this->abacatePayService->createBilling($billingData);

    return view('payment.checkout', [
        'payment_url' => $response['url']
    ]);
}
```

## Creating a Subscription

### Monthly Subscription

```php
public function createMonthlySubscription(Request $request)
{
    $billingData = new BillingData(
        frequency: Frequency::MONTHLY,
        methods: [PaymentMethod::PIX, PaymentMethod::CARD],
        products: [
            new ProductData(
                name: 'Pro Plan - Monthly',
                price: 4990, // R$ 49.90/month
                description: 'Access to all premium features'
            )
        ],
        customer: new CustomerData(
            email: $request->user()->email,
            name: $request->user()->name
        ),
        metadata: [
            'plan' => 'pro',
            'billing_cycle' => 'monthly',
            'user_id' => $request->user()->id,
        ]
    );

    $response = $this->abacatePayService->createBilling(
        $billingData,
        $request->user()->id
    );

    // Update user subscription status
    $request->user()->update([
        'subscription_status' => 'pending',
        'subscription_billing_id' => $response['id'],
    ]);

    return redirect($response['url']);
}
```

### Yearly Subscription (with discount)

```php
public function createYearlySubscription(Request $request)
{
    $billingData = new BillingData(
        frequency: Frequency::YEARLY,
        methods: [PaymentMethod::PIX, PaymentMethod::CARD],
        products: [
            new ProductData(
                name: 'Pro Plan - Yearly (2 months free)',
                price: 49900, // R$ 499.00/year (instead of R$ 598.80)
                description: 'Annual subscription with 2 months free'
            )
        ],
        customer: CustomerData::fromUser($request->user()),
        metadata: [
            'plan' => 'pro',
            'billing_cycle' => 'yearly',
            'discount' => '16.6%',
        ]
    );

    $response = $this->abacatePayService->createBilling($billingData);

    return response()->json([
        'success' => true,
        'payment_url' => $response['url'],
        'savings' => 'R$ 99,80', // Show customer the savings
    ]);
}
```

## Multiple Products

```php
public function createCartCheckout(Request $request)
{
    $cart = $request->user()->cart; // Assume cart relationship exists

    $products = $cart->items->map(function ($item) {
        return new ProductData(
            name: $item->product->name,
            price: $item->product->price_cents,
            quantity: $item->quantity,
            description: $item->product->description,
            externalId: $item->product->id
        );
    })->toArray();

    $billingData = new BillingData(
        frequency: Frequency::ONE_TIME,
        methods: [PaymentMethod::PIX, PaymentMethod::CARD],
        products: $products,
        customer: CustomerData::fromUser($request->user()),
        metadata: [
            'cart_id' => $cart->id,
            'items_count' => $cart->items->count(),
        ]
    );

    $response = $this->abacatePayService->createBilling($billingData);

    // Clear cart after creating billing
    $cart->items()->delete();

    return redirect($response['url']);
}
```

## Using Direct SDK Access

For advanced use cases, you can access the official SDK clients directly:

```php
public function advancedBillingCreation()
{
    // Get the official SDK client
    $billingClient = $this->abacatePayService->getBillingClient();

    // Create billing using official SDK classes
    $billing = new \AbacatePay\Billing([
        'frequency' => \AbacatePay\Frequencies::ONE_TIME,
        'methods' => [\AbacatePay\Methods::PIX],
        'products' => [
            new \AbacatePay\Product([
                'name' => 'Direct SDK Product',
                'price' => 10000 // R$ 100.00
            ])
        ],
        'customer' => new \AbacatePay\Customer([
            'email' => 'customer@example.com',
            'name' => 'Customer Name'
        ])
    ]);

    // Use official SDK method
    $response = $billingClient->create($billing);

    return $response;
}
```

## Webhook Integration

### Basic Webhook Handling

The package automatically handles webhooks. You can extend the controller for custom logic:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Eduardoks98\PaymentAbacatePay\Http\Controllers\WebhookController as BaseWebhookController;
use Eduardoks98\PaymentAbacatePay\Models\AbacatePayBilling;
use App\Notifications\PaymentConfirmed;

class CustomWebhookController extends BaseWebhookController
{
    protected function dispatchWebhookEvents(string $billingId, string $status, array $payload, $billing): void
    {
        // Call parent method first
        parent::dispatchWebhookEvents($billingId, $status, $payload, $billing);

        // Custom logic based on status
        if ($status === 'paid' && $billing) {
            // Find the user
            $user = $billing->user;

            if ($user) {
                // Activate subscription
                $user->update([
                    'subscription_status' => 'active',
                    'subscription_activated_at' => now(),
                ]);

                // Send notification
                $user->notify(new PaymentConfirmed($billing));

                // Grant access to premium features
                $user->grantPremiumAccess();
            }
        }

        if ($status === 'cancelled' && $billing) {
            // Handle cancellation
            $billing->user?->update([
                'subscription_status' => 'cancelled'
            ]);
        }
    }
}
```

Then update your route to use the custom controller:

```php
// In routes/api.php or web.php
Route::post('/webhooks/abacatepay', [CustomWebhookController::class, 'handle'])
    ->middleware(['abacatepay.webhook'])
    ->name('abacatepay.webhook');
```

## Database Queries

### Query Billings

```php
use Eduardoks98\PaymentAbacatePay\Models\AbacatePayBilling;
use Eduardoks98\PaymentAbacatePay\Enums\BillingStatus;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;

// Get user's payment history
public function paymentHistory(Request $request)
{
    $billings = AbacatePayBilling::byUser($request->user()->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('account.payments', compact('billings'));
}

// Get active subscriptions
public function activeSubscriptions(Request $request)
{
    $subscriptions = AbacatePayBilling::byUser($request->user()->id)
        ->paid()
        ->whereIn('frequency', [
            Frequency::MONTHLY->value,
            Frequency::YEARLY->value
        ])
        ->get();

    return response()->json($subscriptions);
}

// Check if payment is completed
public function checkPaymentStatus($billingId)
{
    $billing = AbacatePayBilling::where('abacatepay_id', $billingId)->first();

    if (!$billing) {
        return response()->json(['status' => 'not_found'], 404);
    }

    return response()->json([
        'status' => $billing->status->value,
        'is_paid' => $billing->isPaid(),
        'amount' => $billing->formatted_amount,
        'paid_at' => $billing->paid_at,
    ]);
}

// Get revenue statistics
public function revenueStats()
{
    $totalRevenue = AbacatePayBilling::paid()
        ->sum('amount');

    $monthlyRevenue = AbacatePayBilling::paid()
        ->whereMonth('paid_at', now()->month)
        ->sum('amount');

    return response()->json([
        'total_revenue_cents' => $totalRevenue,
        'total_revenue_formatted' => 'R$ ' . number_format($totalRevenue / 100, 2, ',', '.'),
        'monthly_revenue_cents' => $monthlyRevenue,
        'monthly_revenue_formatted' => 'R$ ' . number_format($monthlyRevenue / 100, 2, ',', '.'),
    ]);
}
```

### Model Helpers

```php
// Check billing status
$billing = AbacatePayBilling::find(1);

if ($billing->isPaid()) {
    echo "Payment confirmed!";
}

if ($billing->isPending()) {
    echo "Waiting for payment...";
}

// Get formatted amount
echo $billing->formatted_amount; // "R$ 199,00"

// Get payment methods
foreach ($billing->payment_methods as $method) {
    echo $method->label(); // "PIX" or "Credit/Debit Card"
}
```

## Error Handling

```php
public function safePaymentCreation(Request $request)
{
    try {
        $billingData = new BillingData(
            frequency: Frequency::ONE_TIME,
            methods: [PaymentMethod::PIX],
            products: [
                new ProductData(
                    name: 'Product',
                    price: 10000
                )
            ],
            customer: CustomerData::fromUser($request->user())
        );

        $response = $this->abacatePayService->createBilling($billingData);

        return redirect($response['url']);
    } catch (\InvalidArgumentException $e) {
        // Configuration error
        return back()->withErrors([
            'payment' => 'Payment system configuration error. Please contact support.'
        ]);
    } catch (\Exception $e) {
        // API error
        logger()->error('AbacatePay error', [
            'error' => $e->getMessage(),
            'user_id' => $request->user()->id
        ]);

        return back()->withErrors([
            'payment' => 'Unable to create payment. Please try again.'
        ]);
    }
}
```

## Testing

```php
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

test('can create billing data from array', function () {
    $data = [
        'frequency' => 'one_time',
        'methods' => ['pix'],
        'products' => [
            [
                'name' => 'Test Product',
                'price' => 10000,
                'quantity' => 2
            ]
        ],
        'customer' => [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]
    ];

    $billingData = BillingData::fromArray($data);

    expect($billingData->frequency)->toBe(Frequency::ONE_TIME);
    expect($billingData->methods)->toHaveCount(1);
    expect($billingData->products)->toHaveCount(1);
    expect($billingData->products[0]->getTotalPrice())->toBe(20000);
});
```
