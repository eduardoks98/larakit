# QuickStart Guide - AbacatePay Laravel Integration

Get started with AbacatePay payments in 5 minutes.

## 1. Installation

```bash
composer require eduardoks98/payment-abacatepay
```

## 2. Configuration

Add to `.env`:

```env
ABACATEPAY_TOKEN=your_api_token_here
ABACATEPAY_WEBHOOK_SECRET=your_webhook_secret
```

Get your token: https://dashboard.abacatepay.com/settings/api

## 3. Migrate

```bash
php artisan migrate
```

## 4. Create Your First Payment

```php
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\{BillingData, ProductData, CustomerData};
use Eduardoks98\PaymentAbacatePay\Enums\{Frequency, PaymentMethod};

class PaymentController extends Controller
{
    public function checkout(AbacatePayService $abacatePay)
    {
        $billing = $abacatePay->createBilling(
            new BillingData(
                frequency: Frequency::ONE_TIME,
                methods: [PaymentMethod::PIX],
                products: [
                    new ProductData(
                        name: 'Premium Plan',
                        price: 10000 // R$ 100.00 in cents
                    )
                ],
                customer: new CustomerData(
                    email: auth()->user()->email,
                    name: auth()->user()->name
                )
            ),
            userId: auth()->id()
        );

        return redirect($billing['url']); // Send user to payment page
    }
}
```

## 5. Setup Webhook

Configure webhook URL in AbacatePay Dashboard:
```
https://yourapp.com/webhooks/abacatepay
```

The package automatically handles webhooks and updates billing status.

## 6. Check Payment Status

```php
use Eduardoks98\PaymentAbacatePay\Models\AbacatePayBilling;

$billing = AbacatePayBilling::find($id);

if ($billing->isPaid()) {
    // Payment confirmed! Grant access
}
```

## Common Use Cases

### PIX Payment
```php
new BillingData(
    frequency: Frequency::ONE_TIME,
    methods: [PaymentMethod::PIX],
    products: [new ProductData('Product', 5000)],
    customer: CustomerData::fromUser($user)
)
```

### Monthly Subscription
```php
new BillingData(
    frequency: Frequency::MONTHLY,
    methods: [PaymentMethod::PIX, PaymentMethod::CARD],
    products: [new ProductData('Pro Plan', 2990)],
    customer: CustomerData::fromUser($user)
)
```

### Multiple Products
```php
new BillingData(
    frequency: Frequency::ONE_TIME,
    methods: [PaymentMethod::PIX],
    products: [
        new ProductData('Product A', 1000, quantity: 2),
        new ProductData('Product B', 5000, quantity: 1),
    ],
    customer: CustomerData::fromUser($user)
)
```

## Query Billings

```php
// Get user's paid billings
AbacatePayBilling::byUser($userId)->paid()->get();

// Get pending payments
AbacatePayBilling::pending()->get();

// Get monthly subscriptions
AbacatePayBilling::byFrequency(Frequency::MONTHLY)->get();
```

## API Endpoints

Already configured and ready to use:

- `POST /api/abacatepay/billings` - Create billing
- `GET /api/abacatepay/billings` - List billings
- `GET /api/abacatepay/billings/{id}` - Get billing

Example API call:
```bash
curl -X POST https://yourapp.com/api/abacatepay/billings \
  -H "Content-Type: application/json" \
  -d '{
    "frequency": "one_time",
    "methods": ["pix"],
    "products": [{"name": "Product", "price": 10000}],
    "customer": {"email": "user@example.com"}
  }'
```

## Direct SDK Access

Need advanced features? Access the SDK directly:

```php
$billingClient = $abacatePay->getBillingClient();
$customerClient = $abacatePay->getCustomerClient();

// Use official SDK methods
$billing = new \AbacatePay\Billing([...]);
$response = $billingClient->create($billing);
```

## Next Steps

- Read [README.md](README.md) for complete documentation
- Check [EXAMPLE.md](EXAMPLE.md) for more examples
- Review [STRUCTURE.md](STRUCTURE.md) for package architecture

## Troubleshooting

### Token Error
Make sure `ABACATEPAY_TOKEN` is set in `.env` and you ran `php artisan config:clear`

### Webhook Not Working
1. Check webhook secret: `ABACATEPAY_WEBHOOK_SECRET`
2. Ensure URL is accessible: `https://yourapp.com/webhooks/abacatepay`
3. Verify signature verification middleware is enabled

### Database Not Storing Billings
Check config: `ABACATEPAY_STORE_BILLINGS=true`

## Support

- AbacatePay Docs: https://docs.abacatepay.com
- AbacatePay Dashboard: https://dashboard.abacatepay.com
- Official SDK: https://github.com/AbacatePay/abacatepay-php-sdk
