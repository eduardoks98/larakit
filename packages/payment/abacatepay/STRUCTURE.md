# Package Structure - eduardoks98/payment-abacatepay

Complete implementation of AbacatePay payment integration following the official PHP SDK.

## Directory Structure

```
payment-abacatepay/
├── config/
│   └── abacatepay.php                 # Configuration file
├── database/
│   └── migrations/
│       └── 2024_01_01_000001_create_abacatepay_billings_table.php
├── src/
│   ├── AbacatePayServiceProvider.php  # Laravel Service Provider
│   ├── Enums/
│   │   ├── BillingStatus.php         # Billing status enum
│   │   ├── Frequency.php             # Billing frequency enum (ONE_TIME, MONTHLY, YEARLY)
│   │   └── PaymentMethod.php         # Payment methods enum (PIX, CARD)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AbacatePayController.php   # Main API controller
│   │   │   └── WebhookController.php      # Webhook handler
│   │   ├── Middleware/
│   │   │   └── VerifyAbacatePayWebhook.php  # Webhook signature verification
│   │   └── routes.php                # Package routes
│   ├── Models/
│   │   ├── AbacatePayBilling.php     # Eloquent model for database
│   │   ├── BillingData.php           # DTO for billing creation
│   │   ├── CustomerData.php          # DTO for customer data
│   │   └── ProductData.php           # DTO for product data
│   └── Services/
│       └── AbacatePayService.php     # Main service wrapping SDK
├── tests/
│   ├── Feature/
│   │   └── AbacatePayServiceTest.php
│   ├── Unit/
│   │   └── EnumTest.php
│   └── TestCase.php
├── .gitignore
├── composer.json
├── EXAMPLE.md                         # Usage examples
├── LICENSE                            # MIT License
├── phpunit.xml.dist                   # PHPUnit configuration
└── README.md                          # Complete documentation
```

## Key Components

### 1. Service Provider (AbacatePayServiceProvider.php)
- Registers AbacatePayService as singleton
- Publishes configuration
- Loads migrations
- Registers webhook middleware
- Loads routes

### 2. Main Service (AbacatePayService.php)
- Wraps official AbacatePay SDK clients (BillingClient, CustomerClient)
- Provides Laravel-friendly interface
- Handles database persistence
- Manages billing and customer operations
- Updates billing status from webhooks

### 3. Data Transfer Objects (DTOs)

#### BillingData
- Wraps billing creation parameters
- Converts to SDK's `AbacatePay\Billing` object
- Validates and structures data

#### ProductData
- Represents products in billings
- Converts to SDK's `AbacatePay\Product` object
- Handles price calculations (cents)

#### CustomerData
- Customer information wrapper
- Converts to SDK's `AbacatePay\Customer` object
- Supports creating from Laravel User model

### 4. Enums

#### Frequency
- ONE_TIME: Single payment
- MONTHLY: Monthly subscription
- YEARLY: Yearly subscription
- Maps to SDK's `AbacatePay\Frequencies`

#### PaymentMethod
- PIX: Instant PIX payment
- CARD: Credit/debit card
- Maps to SDK's `AbacatePay\Methods`

#### BillingStatus
- PENDING: Awaiting payment
- PAID: Payment confirmed
- CANCELLED: Payment cancelled
- EXPIRED: Payment expired
- REFUNDED: Payment refunded

### 5. Database Model (AbacatePayBilling)
- Stores billing records
- Tracks payment status
- Relations with User model
- Query scopes (paid, pending, byUser, etc.)
- Helper methods (isPaid, markAsPaid, etc.)

### 6. Controllers

#### AbacatePayController
- RESTful API endpoints
- Create billing
- Get billing
- List billings
- Customer management

#### WebhookController
- Handles AbacatePay webhooks
- Updates billing status
- Dispatches events
- Logging

### 7. Middleware (VerifyAbacatePayWebhook)
- Validates webhook signatures
- Prevents unauthorized requests
- HMAC SHA-256 verification

## SDK Integration

This package is a **wrapper** for the official AbacatePay PHP SDK. It:

1. **Uses official SDK classes:**
   - `AbacatePay\Client`
   - `AbacatePay\BillingClient`
   - `AbacatePay\CustomerClient`
   - `AbacatePay\Billing`
   - `AbacatePay\Product`
   - `AbacatePay\Customer`
   - `AbacatePay\Frequencies`
   - `AbacatePay\Methods`

2. **Provides Laravel integration:**
   - Service Provider
   - Configuration management
   - Database persistence
   - Route management
   - Middleware
   - Eloquent models

3. **Maintains SDK compatibility:**
   - DTOs convert to SDK objects
   - Direct SDK access available
   - Follows SDK patterns

## Installation

```bash
composer require eduardoks98/payment-abacatepay
```

Dependencies:
- `abacatepay/php-sdk: ^1.0`
- Laravel 10.x, 11.x, or 12.x
- PHP 8.1+

## Usage Example

```php
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

// Create billing
$billingData = new BillingData(
    frequency: Frequency::ONE_TIME,
    methods: [PaymentMethod::PIX],
    products: [
        new ProductData(
            name: 'Product',
            price: 10000 // R$ 100.00
        )
    ],
    customer: new CustomerData(
        email: 'customer@example.com'
    )
);

$response = app(AbacatePayService::class)->createBilling($billingData);
```

## API Routes

- `POST /api/abacatepay/billings` - Create billing
- `GET /api/abacatepay/billings` - List billings
- `GET /api/abacatepay/billings/{id}` - Get billing
- `POST /api/abacatepay/customers` - Create customer
- `GET /api/abacatepay/customers/{id}` - Get customer
- `POST /webhooks/abacatepay` - Webhook endpoint

## Configuration

Environment variables:
```env
ABACATEPAY_TOKEN=your_token
ABACATEPAY_WEBHOOK_SECRET=your_secret
ABACATEPAY_DEFAULT_METHOD=pix
ABACATEPAY_DEFAULT_FREQUENCY=one_time
ABACATEPAY_RETURN_URL=https://yourapp.com/payment/return
ABACATEPAY_COMPLETION_URL=https://yourapp.com/payment/success
ABACATEPAY_STORE_BILLINGS=true
```

## Testing

```bash
composer test
```

## Documentation

- [README.md](README.md) - Complete package documentation
- [EXAMPLE.md](EXAMPLE.md) - Practical usage examples
- [Official SDK](https://github.com/AbacatePay/abacatepay-php-sdk) - AbacatePay PHP SDK

## License

MIT License - See [LICENSE](LICENSE) file
