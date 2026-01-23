# Comtele SMS Package

> Comtele SMS integration for cost-effective messaging in Brazil.

## Overview

The `eduardoks98/sms-comtele` package provides Comtele SMS integration, a Brazilian SMS provider with competitive pricing and excellent delivery rates for the Brazilian market.

## Installation

```bash
composer require eduardoks98/sms-comtele
```

## Configuration

### Environment Variables

```env
COMTELE_API_KEY=your_api_key
COMTELE_API_URL=https://sms.comtele.com.br/api/v2
COMTELE_BULK_MAX_RECIPIENTS=100
COMTELE_REPORTING_COOLDOWN=30
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\SmsComtele\SmsComteleServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\SmsComtele\SmsComteleServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### Send Single SMS

```php
use Eduardoks98\SmsComtele\Services\ComteleService;

$comtele = app(ComteleService::class);

// Simple message
$message = $comtele->send(
    receivers: '11987654321', // DDD + number
    content: 'Seu codigo de verificacao: 123456'
);

// With custom sender (if enabled)
$message = $comtele->send(
    receivers: '11987654321',
    content: 'Seu pedido foi enviado!',
    sender: 'MinhaLoja'
);
```

### Send with Template

```php
// Define templates in config
// config/sms-comtele.php
'templates' => [
    'verification' => 'Codigo de verificacao: {code}. Valido por 5 minutos.',
    'order_status' => 'Ola {name}! Pedido #{order} atualizado: {status}',
    'payment' => 'Pagamento de R$ {value} confirmado. Obrigado!',
],

// Send using template
$message = $comtele->sendTemplate(
    receivers: '11987654321',
    template: 'verification',
    variables: ['code' => '123456']
);

$message = $comtele->sendTemplate(
    receivers: '21987654321',
    template: 'order_status',
    variables: [
        'name' => 'Maria',
        'order' => '54321',
        'status' => 'Em transporte'
    ]
);
```

### Bulk SMS

```php
// Send to multiple recipients (max 100)
$messages = $comtele->sendBulk(
    receivers: [
        '11987654321',
        '21987654321',
        '31987654321',
        '41987654321'
    ],
    content: 'Promocao relampago! 50% de desconto hoje!'
);

// Check results
foreach ($messages as $result) {
    if ($result['success']) {
        echo "Enviado para {$result['to']}";
    } else {
        echo "Falhou para {$result['to']}: {$result['error']}";
    }
}
```

### Get Detailed Reports

```php
// Get report for date range
$report = $comtele->getDetailedReport(
    startDate: '2026-01-01',
    endDate: '2026-01-31'
);

// Report includes:
// - Total messages sent
// - Delivery rates
// - Failed messages
// - Cost breakdown
```

### Check Account Balance

```php
$balance = $comtele->getBalance();

echo "Creditos disponiveis: {$balance['credits']}";
echo "Valor em R$: {$balance['value']}";
```

## Phone Number Format

Comtele uses Brazilian format (DDD + number):
- Format: `[DDD][number]`
- Example: `11987654321` (Sao Paulo)
- Example: `21987654321` (Rio de Janeiro)

```php
// All formats accepted
$comtele->send('11987654321', 'Hello!');     // Standard
$comtele->send('(11) 98765-4321', 'Hello!'); // Formatted (auto-cleaned)
$comtele->send('+5511987654321', 'Hello!');  // E.164 (auto-converted)
```

## Database Model

```php
use Eduardoks98\SmsComtele\Models\ComteleMessage;

$message = ComteleMessage::find($id);
$message->message_id;  // Comtele message ID
$message->receiver;    // Recipient number
$message->content;     // Message content
$message->status;      // sent, delivered, failed
$message->sender;      // Sender name

// Scopes
ComteleMessage::delivered()->get();
ComteleMessage::failed()->get();
ComteleMessage::forNumber('11987654321')->get();
ComteleMessage::today()->get();
```

## Message Status

| Status | Description |
|--------|-------------|
| `sent` | Message sent to carrier |
| `delivered` | Message delivered |
| `failed` | Message failed |
| `pending` | Awaiting processing |

## Features

- Single SMS sending
- Template support with variables
- Bulk SMS (up to 100 recipients)
- Detailed reporting
- Balance checking
- Database logging
- Auto phone formatting
- Rate limiting protection

## Rate Limiting

The package includes built-in rate limiting:

```php
// config/sms-comtele.php
'rate_limit' => [
    'reporting_cooldown' => 30, // Seconds between report requests
],
'bulk' => [
    'max_recipients' => 100, // Max recipients per bulk request
],
```

## Error Handling

```php
use Eduardoks98\SmsComtele\Exceptions\ComteleException;

try {
    $message = $comtele->send($to, $content);
} catch (ComteleException $e) {
    Log::error('Comtele error: ' . $e->getMessage());

    // Common errors:
    // - Invalid phone number
    // - Insufficient credits
    // - API rate limit exceeded
}
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Pricing

Comtele offers competitive pricing for Brazil:
- SMS starting at R$ 0,07 per message
- Volume discounts available
- No monthly fees
- See [Comtele Pricing](https://www.comtele.com.br/precos)

## Why Comtele?

1. **Brazilian Focus**: Optimized for Brazilian carriers
2. **Cost-Effective**: Lower prices than international providers
3. **High Delivery**: Excellent delivery rates in Brazil
4. **Simple API**: Easy to integrate
5. **No Setup Fee**: Pay only for messages sent

## Related

- [SMS Twilio](./sms-twilio.md) - Global SMS provider
- [WhatsApp Converx](./whatsapp-converx.md) - Brazilian WhatsApp
