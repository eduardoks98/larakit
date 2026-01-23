# AbacatePay Payment Integration for Laravel

[![Latest Version](https://img.shields.io/packagist/v/eduardoks98/payment-abacatepay.svg)](https://packagist.org/packages/eduardoks98/payment-abacatepay)
[![License](https://img.shields.io/packagist/l/eduardoks98/payment-abacatepay.svg)](LICENSE)

Official Laravel wrapper for the [AbacatePay PHP SDK](https://github.com/AbacatePay/abacatepay-php-sdk). Create PIX and credit card payments with ease using the AbacatePay payment gateway.

## Features

- **Official SDK Integration**: Built on top of the official AbacatePay PHP SDK
- **Laravel-Friendly**: Service provider, facades, and dependency injection support
- **Multiple Payment Methods**: Support for PIX and credit card payments
- **Flexible Billing**: One-time payments, monthly, and yearly subscriptions
- **Database Persistence**: Optional storage of billing records in your database
- **Webhook Support**: Built-in webhook verification and handling
- **Type-Safe**: PHP 8.1+ enums for payment methods, frequencies, and statuses
- **RESTful API**: Ready-to-use controllers for billing and customer management

## Requirements

- PHP 7.2.5 or higher (PHP 8.1+ recommended for enum support)
- Laravel 10.x, 11.x, or 12.x
- Composer
- AbacatePay account and API token

## Installation

Install the package via Composer:

```bash
composer require eduardoks98/payment-abacatepay
```

The package will automatically register its service provider.

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Eduardoks98\PaymentAbacatePay\AbacatePayServiceProvider" --tag="config"
```

### Run Migrations

Run the migrations to create the `abacatepay_billings` table:

```bash
php artisan migrate
```

### Environment Configuration

Add your AbacatePay credentials to your `.env` file:

```env
ABACATEPAY_TOKEN=your_api_token_here
ABACATEPAY_WEBHOOK_SECRET=your_webhook_secret_here
ABACATEPAY_DEFAULT_METHOD=pix
ABACATEPAY_DEFAULT_FREQUENCY=one_time
ABACATEPAY_RETURN_URL=https://yourapp.com/payment/return
ABACATEPAY_COMPLETION_URL=https://yourapp.com/payment/success
```

Get your API token from the [AbacatePay Dashboard](https://dashboard.abacatepay.com/settings/api).

## Usage

### Basic Example

```php
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

// Inject the service
public function __construct(
    private AbacatePayService $abacatePayService
) {}

public function createPayment()
{
    // Create billing data
    $billingData = new BillingData(
        frequency: Frequency::ONE_TIME,
        methods: [PaymentMethod::PIX],
        products: [
            new ProductData(
                name: 'Premium Plan',
                price: 10000, // R$ 100.00 in cents
                quantity: 1
            )
        ],
        customer: new CustomerData(
            email: 'customer@example.com',
            name: 'John Doe',
            cellphone: '11999999999',
            taxId: '12345678900'
        ),
        metadata: ['order_id' => '123']
    );

    // Create billing
    $response = $this->abacatePayService->createBilling(
        $billingData,
        $userId = auth()->id()
    );

    // Redirect user to payment URL
    return redirect($response['url']);
}
```

### Using Multiple Payment Methods

```php
$billingData = new BillingData(
    frequency: Frequency::ONE_TIME,
    methods: [
        PaymentMethod::PIX,
        PaymentMethod::CARD
    ],
    products: [
        new ProductData(
            name: 'Product Bundle',
            price: 50000, // R$ 500.00
        )
    ],
    customer: CustomerData::fromUser(auth()->user())
);

$response = $this->abacatePayService->createBilling($billingData);
```

### Creating Subscriptions

```php
// Monthly subscription
$billingData = new BillingData(
    frequency: Frequency::MONTHLY,
    methods: [PaymentMethod::PIX, PaymentMethod::CARD],
    products: [
        new ProductData(
            name: 'Monthly Subscription',
            price: 2990, // R$ 29.90/month
        )
    ],
    customer: CustomerData::fromUser(auth()->user())
);

// Yearly subscription
$billingData = new BillingData(
    frequency: Frequency::YEARLY,
    methods: [PaymentMethod::PIX, PaymentMethod::CARD],
    products: [
        new ProductData(
            name: 'Yearly Subscription',
            price: 29900, // R$ 299.00/year
        )
    ],
    customer: CustomerData::fromUser(auth()->user())
);
```

### Using the API Endpoints

The package provides ready-to-use RESTful API endpoints:

#### Create Billing

```http
POST /api/abacatepay/billings
Content-Type: application/json

{
  "frequency": "one_time",
  "methods": ["pix"],
  "products": [
    {
      "name": "Product Name",
      "price": 10000,
      "quantity": 1,
      "description": "Product description"
    }
  ],
  "customer": {
    "email": "customer@example.com",
    "name": "Customer Name",
    "cellphone": "11999999999",
    "taxId": "12345678900"
  },
  "metadata": {
    "order_id": "123"
  }
}
```

#### Get Billing

```http
GET /api/abacatepay/billings/{billingId}
```

#### List Billings

```http
GET /api/abacatepay/billings?limit=10&offset=0
```

#### Webhook Endpoint

```http
POST /webhooks/abacatepay
```

### Direct SDK Access

Access the official SDK clients directly:

```php
// Get BillingClient
$billingClient = $this->abacatePayService->getBillingClient();

// Get CustomerClient
$customerClient = $this->abacatePayService->getCustomerClient();

// Use official SDK methods
$billing = new \AbacatePay\Billing([
    'frequency' => \AbacatePay\Frequencies::ONE_TIME,
    'methods' => [\AbacatePay\Methods::PIX],
    'products' => [
        new \AbacatePay\Product([
            'name' => 'Product',
            'price' => 10000
        ])
    ]
]);

$response = $billingClient->create($billing);
```

### Database Queries

Query stored billings using Eloquent:

```php
use Eduardoks98\PaymentAbacatePay\Models\AbacatePayBilling;
use Eduardoks98\PaymentAbacatePay\Enums\BillingStatus;

// Get all paid billings for a user
$paidBillings = AbacatePayBilling::byUser(auth()->id())
    ->paid()
    ->get();

// Get pending billings
$pendingBillings = AbacatePayBilling::pending()->get();

// Get billings by status
$cancelledBillings = AbacatePayBilling::byStatus(BillingStatus::CANCELLED)->get();

// Get monthly subscriptions
$monthlyBillings = AbacatePayBilling::byFrequency(Frequency::MONTHLY)->get();

// Check billing status
$billing = AbacatePayBilling::find(1);
if ($billing->isPaid()) {
    // Process order
}
```

### Webhook Handling

The package automatically handles webhooks at `/webhooks/abacatepay` with signature verification.

Configure your webhook URL in the [AbacatePay Dashboard](https://dashboard.abacatepay.com/settings/webhooks):

```
https://yourapp.com/webhooks/abacatepay
```

The webhook will automatically:
- Verify the signature using your webhook secret
- Update billing status in the database
- Log all webhook events

You can customize webhook handling by extending the `WebhookController`.

## Configuration

All configuration options are available in `config/abacatepay.php`:

```php
return [
    // API token (required)
    'token' => env('ABACATEPAY_TOKEN'),

    // Webhook secret for signature verification
    'webhook_secret' => env('ABACATEPAY_WEBHOOK_SECRET'),

    // Default payment method (pix or card)
    'default_method' => env('ABACATEPAY_DEFAULT_METHOD', 'pix'),

    // Default frequency (one_time, monthly, yearly)
    'default_frequency' => env('ABACATEPAY_DEFAULT_FREQUENCY', 'one_time'),

    // Return URL after payment
    'return_url' => env('ABACATEPAY_RETURN_URL'),

    // Completion URL after successful payment
    'completion_url' => env('ABACATEPAY_COMPLETION_URL'),

    // Enable database storage
    'store_billings' => env('ABACATEPAY_STORE_BILLINGS', true),
];
```

## Available Enums

### Frequency

```php
Frequency::ONE_TIME   // One-time payment
Frequency::MONTHLY    // Monthly subscription
Frequency::YEARLY     // Yearly subscription
```

### PaymentMethod

```php
PaymentMethod::PIX    // PIX instant payment
PaymentMethod::CARD   // Credit/debit card
```

### BillingStatus

```php
BillingStatus::PENDING    // Payment pending
BillingStatus::PAID       // Payment confirmed
BillingStatus::CANCELLED  // Payment cancelled
BillingStatus::EXPIRED    // Payment expired
BillingStatus::REFUNDED   // Payment refunded
```

## Testing

Run the tests:

```bash
composer test
```

Set test environment variables:

```env
ABACATEPAY_TOKEN=test_token
ABACATEPAY_WEBHOOK_SECRET=test_secret
```

## Security

- Always use HTTPS in production
- Keep your API token secret
- Configure webhook signature verification
- Validate all user inputs
- Use strong webhook secrets

## License

This package is open-source software licensed under the [MIT license](LICENSE).

## Credits

- Built on top of the [Official AbacatePay PHP SDK](https://github.com/AbacatePay/abacatepay-php-sdk)
- Developed by [Eduardo Steffens](https://github.com/eduardoks98)

## Support

- [AbacatePay Documentation](https://docs.abacatepay.com)
- [AbacatePay Dashboard](https://dashboard.abacatepay.com)
- [GitHub Issues](https://github.com/eduardoks98/payment-abacatepay/issues)
