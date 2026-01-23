# MercadoPago Payment Integration - Usage Examples

Complete examples of how to use the MercadoPago package in your Laravel application.

## Table of Contents

- [Setup](#setup)
- [PIX Payment Examples](#pix-payment-examples)
- [Card Payment Examples](#card-payment-examples)
- [Boleto Payment Examples](#boleto-payment-examples)
- [Payment Management](#payment-management)
- [Webhook Handling](#webhook-handling)
- [Frontend Integration](#frontend-integration)

## Setup

### 1. Install Package

```bash
composer require eduardoks98/payment-mercadopago
php artisan vendor:publish --provider="Eduardoks98\PaymentMercadoPago\PaymentMercadoPagoServiceProvider"
php artisan migrate
```

### 2. Configure Environment

```env
MERCADOPAGO_ACCESS_TOKEN=TEST-1234567890-123456-abcdef123456789012345678901234-123456789
MERCADOPAGO_PUBLIC_KEY=TEST-abcd1234-5678-90ab-cdef-1234567890ab
MERCADOPAGO_ENVIRONMENT=sandbox
MERCADOPAGO_WEBHOOK_SECRET=your_webhook_secret_here
```

## PIX Payment Examples

### Basic PIX Payment

```php
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;

class CheckoutController extends Controller
{
    public function createPixPayment(Request $request, MercadoPagoService $mercadoPago)
    {
        try {
            $payment = $mercadoPago->createPixPayment([
                'amount' => $request->total,
                'payer_email' => $request->email,
                'payer_name' => $request->name,
                'payer_document' => $request->cpf,
                'description' => "Order #{$request->order_id}",
                'external_reference' => "ORDER-{$request->order_id}",
            ]);

            return response()->json([
                'payment_id' => $payment->id,
                'qr_code_image' => $payment->getPixQrCodeDataUri(),
                'qr_code_text' => $payment->qr_code,
                'status' => $payment->status->value,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

### PIX with Custom Expiration

```php
$payment = $mercadoPago->createPixPayment([
    'amount' => 150.00,
    'payer_email' => 'customer@example.com',
    'description' => 'Premium Subscription',
    'expiration_time' => 'PT30M', // 30 minutes
    // or: 'PT1H' (1 hour), 'PT2H' (2 hours), 'P1D' (1 day)
]);
```

### Display PIX QR Code in Blade

```blade
<div class="pix-payment">
    <h3>Pagamento PIX</h3>

    <!-- QR Code Image -->
    <div class="qr-code">
        <img src="{{ $payment->getPixQrCodeDataUri() }}"
             alt="PIX QR Code"
             style="width: 300px; height: 300px;">
    </div>

    <!-- Copy-Paste Code -->
    <div class="pix-code">
        <label>Ou copie o código:</label>
        <input type="text"
               value="{{ $payment->qr_code }}"
               id="pix-code"
               readonly>
        <button onclick="copyPixCode()">Copiar Código</button>
    </div>

    <!-- Expiration -->
    <p>Válido até: {{ $payment->expiration_date->format('d/m/Y H:i') }}</p>
</div>

<script>
function copyPixCode() {
    const code = document.getElementById('pix-code');
    code.select();
    document.execCommand('copy');
    alert('Código PIX copiado!');
}
</script>
```

## Card Payment Examples

### Credit Card Payment

First, tokenize the card on frontend using MercadoPago.js:

```html
<!-- Include MercadoPago SDK -->
<script src="https://sdk.mercadopago.com/js/v2"></script>

<script>
const mp = new MercadoPago('{{ config("payment-mercadopago.public_key") }}');

// Create card form
const cardForm = mp.cardForm({
    amount: "100.00",
    iframe: true,
    form: {
        id: "payment-form",
        cardNumber: {
            id: "cardNumber",
            placeholder: "Número do cartão",
        },
        expirationDate: {
            id: "expirationDate",
            placeholder: "MM/YY",
        },
        securityCode: {
            id: "securityCode",
            placeholder: "CVV",
        },
        cardholderName: {
            id: "cardholderName",
            placeholder: "Nome no cartão",
        },
        installments: {
            id: "installments",
            placeholder: "Parcelas",
        },
    },
    callbacks: {
        onFormMounted: error => {
            if (error) console.error(error);
        },
        onSubmit: event => {
            event.preventDefault();

            const formData = cardForm.getCardFormData();

            fetch('/api/mercadopago/payments/card', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: formData.token,
                    payment_method_id: formData.paymentMethodId,
                    installments: formData.installments,
                    amount: 100.00,
                    payer_email: formData.payer.email,
                    description: 'Product purchase',
                }),
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Pagamento aprovado!');
                } else {
                    alert('Erro no pagamento: ' + result.message);
                }
            });
        },
    },
});
</script>
```

Backend:

```php
public function processCardPayment(Request $request, MercadoPagoService $mercadoPago)
{
    $payment = $mercadoPago->createCardPayment([
        'amount' => $request->amount,
        'token' => $request->token, // From MercadoPago.js
        'payment_method_id' => $request->payment_method_id,
        'installments' => $request->installments,
        'payer_email' => $request->payer_email,
        'description' => $request->description,
        'external_reference' => "ORDER-{$request->order_id}",
    ]);

    if ($payment->isApproved()) {
        // Payment successful - deliver product/service
        return response()->json(['status' => 'approved']);
    }

    return response()->json([
        'status' => $payment->status->value,
        'message' => $payment->status_detail,
    ]);
}
```

## Boleto Payment Examples

### Create Boleto

```php
$payment = $mercadoPago->createBoletoPayment([
    'amount' => 250.00,
    'payer_email' => 'customer@example.com',
    'payer_name' => 'João Silva',
    'payer_document' => '12345678909',
    'description' => 'Mensalidade Janeiro/2024',
    'external_reference' => 'INVOICE-2024-001',
    'expiration_days' => 3, // 3 days to pay
]);

// Redirect user to Boleto page
return redirect($payment->ticket_url);
```

### Display Boleto Information

```blade
<div class="boleto-payment">
    <h3>Boleto Bancário</h3>

    <div class="boleto-info">
        <p><strong>Valor:</strong> R$ {{ number_format($payment->amount, 2, ',', '.') }}</p>
        <p><strong>Vencimento:</strong> {{ $payment->expiration_date->format('d/m/Y') }}</p>
        <p><strong>Código de Barras:</strong></p>
        <code>{{ $payment->barcode }}</code>
    </div>

    <div class="boleto-actions">
        <a href="{{ $payment->ticket_url }}"
           target="_blank"
           class="btn btn-primary">
            Imprimir Boleto
        </a>
    </div>
</div>
```

## Payment Management

### Check Payment Status

```php
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;

// By external reference
$payment = MercadoPagoPayment::externalReference('ORDER-123')->first();

if ($payment->isApproved()) {
    // Deliver product/service
} elseif ($payment->isPending()) {
    // Show pending message
} elseif ($payment->isRejected()) {
    // Show error message
}
```

### Query Payments

```php
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentMethod;

// Approved PIX payments
$pixApproved = MercadoPagoPayment::paymentMethod(PaymentMethod::PIX)
    ->status(PaymentStatus::APPROVED)
    ->get();

// Pending payments from today
$todayPending = MercadoPagoPayment::status(PaymentStatus::PENDING)
    ->whereDate('created_at', today())
    ->get();

// All payments for a customer
$customerPayments = MercadoPagoPayment::where('payer_email', 'customer@example.com')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Refund Payment

```php
public function refundOrder(Request $request, MercadoPagoService $mercadoPago)
{
    $payment = MercadoPagoPayment::externalReference($request->order_id)->first();

    if (!$payment || !$payment->isApproved()) {
        return response()->json(['error' => 'Cannot refund this payment'], 400);
    }

    try {
        // Full refund
        $refund = $mercadoPago->refundPayment($payment->mercadopago_id);

        // Or partial refund
        // $refund = $mercadoPago->refundPayment($payment->mercadopago_id, 50.00);

        return response()->json(['status' => 'refunded']);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

## Webhook Handling

### Configure Webhook URL

In MercadoPago Dashboard:
```
https://your-domain.com/api/mercadopago/webhook
```

### Automatic Processing

Webhooks are automatically processed by the package. You can listen to payment updates:

```php
use Illuminate\Support\Facades\Event;
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;

// In your EventServiceProvider
Event::listen(
    'eloquent.updated: ' . MercadoPagoPayment::class,
    function ($payment) {
        // Check if status changed to approved
        if ($payment->wasChanged('status') && $payment->isApproved()) {
            // Send confirmation email
            Mail::to($payment->payer_email)
                ->send(new PaymentApprovedMail($payment));

            // Update order status
            Order::where('external_reference', $payment->external_reference)
                ->update(['status' => 'paid']);
        }
    }
);
```

### Manual Webhook Processing

```php
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoWebhook;

// Get unprocessed webhooks
$webhooks = MercadoPagoWebhook::unprocessed()->get();

foreach ($webhooks as $webhook) {
    // Reprocess if needed
    app(WebhookService::class)->processWebhook($webhook->payload);
}
```

## Frontend Integration

### Vue.js Example (PIX Payment)

```vue
<template>
  <div class="pix-payment">
    <div v-if="loading">Gerando pagamento PIX...</div>

    <div v-else-if="payment">
      <h3>Escaneie o QR Code</h3>
      <img :src="payment.qr_code_data_uri" alt="QR Code PIX">

      <div class="pix-code">
        <input v-model="payment.qr_code" readonly>
        <button @click="copyCode">Copiar Código</button>
      </div>

      <p>Status: {{ payment.status }}</p>
      <button @click="checkStatus">Verificar Pagamento</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      payment: null,
    }
  },
  methods: {
    async createPayment() {
      this.loading = true;

      try {
        const response = await fetch('/api/mercadopago/payments/pix', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            amount: this.amount,
            payer_email: this.email,
            description: 'Product purchase',
          }),
        });

        const result = await response.json();
        this.payment = result.data;

        // Auto-check status every 5 seconds
        this.startStatusPolling();

      } catch (error) {
        alert('Erro ao criar pagamento: ' + error.message);
      } finally {
        this.loading = false;
      }
    },

    async checkStatus() {
      const response = await fetch(`/api/mercadopago/payments/${this.payment.id}`);
      const result = await response.json();

      this.payment.status = result.data.status;

      if (result.data.status === 'approved') {
        alert('Pagamento aprovado!');
        this.stopStatusPolling();
      }
    },

    startStatusPolling() {
      this.pollingInterval = setInterval(this.checkStatus, 5000);
    },

    stopStatusPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
    },

    copyCode() {
      navigator.clipboard.writeText(this.payment.qr_code);
      alert('Código copiado!');
    },
  },

  mounted() {
    this.createPayment();
  },

  beforeUnmount() {
    this.stopStatusPolling();
  },
}
</script>
```

## Best Practices

1. **Always use external_reference**: Link payments to your orders
2. **Handle webhooks properly**: Don't rely only on frontend callbacks
3. **Validate payment status**: Check in backend before delivering
4. **Use idempotency keys**: Prevent duplicate payments
5. **Log everything**: Enable logging in production for debugging
6. **Test with sandbox**: Use test credentials before going live

## Testing in Sandbox

Use these test cards in sandbox mode:

**Approved:**
- Card: 5031 4332 1540 6351
- CVV: 123
- Expiration: 11/25

**Rejected:**
- Card: 5031 7557 3453 0604
- CVV: 123
- Expiration: 11/25

For PIX and Boleto, payments won't be actually processed in sandbox mode, but you can simulate status changes via webhooks.

---

For more examples and documentation, visit: https://github.com/eduardoks98/payment-mercadopago
