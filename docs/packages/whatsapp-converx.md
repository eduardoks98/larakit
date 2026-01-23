# WhatsApp Converx Package

> Converx WhatsApp integration for Brazilian businesses (Chatwoot-based).

## Overview

The `eduardoks98/whatsapp-converx` package provides WhatsApp integration via Converx, a Brazilian provider built on Chatwoot that offers simplified WhatsApp Business API access.

## Installation

```bash
composer require eduardoks98/whatsapp-converx
```

## Configuration

### Environment Variables

```env
CONVERX_ACCOUNT_ID=8
CONVERX_API_TOKEN=your_api_token
CONVERX_API_URL=https://converx.app/api/v1
CONVERX_INBOX_ID=1
CONVERX_TEMPLATE_NAMESPACE=your_namespace_uuid
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\WhatsAppConverx\WhatsAppConverxServiceProvider" --tag="config"
```

## Usage

### Send Text Message

```php
use Eduardoks98\WhatsAppConverx\Services\ConverxService;

$converx = app(ConverxService::class);

// Simple text message
$response = $converx->sendMessage(
    phoneNumber: '11987654321',
    message: 'Ola! Seu pedido foi confirmado.'
);

// The service automatically:
// 1. Searches for existing contact
// 2. Creates contact if not found
// 3. Gets or creates conversation
// 4. Sends the message
```

### Send Template Message

```php
// Send pre-approved template
$response = $converx->sendTemplate(
    phoneNumber: '11987654321',
    templateName: 'order_confirmation',
    parameters: [
        '1' => 'Joao Silva',      // {{1}} - Customer name
        '2' => '12345',            // {{2}} - Order number
        '3' => 'R$ 149,90'         // {{3}} - Total value
    ]
);

// Lead notification template
$response = $converx->sendTemplate(
    phoneNumber: '11987654321',
    templateName: 'notificar_lead_vendedor',
    parameters: [
        '1' => 'Maria Santos',     // Lead name
        '2' => '21987654321',      // Lead phone
        '3' => 'Produto X'         // Interest
    ]
);
```

### Get or Create Conversation

```php
// Manually manage conversations
$conversationId = $converx->getOrCreateConversation('11987654321');

// Then send multiple messages
$converx->sendToConversation($conversationId, 'First message');
$converx->sendToConversation($conversationId, 'Second message');
```

### Search Contact

```php
// Search by phone number
$contact = $converx->searchContact('11987654321');

if ($contact) {
    echo "Contact found: {$contact['name']}";
} else {
    echo "Contact not found";
}
```

## Phone Number Format

Converx uses Brazilian format with country code:
- Format: `55[DDD][number]`
- Example: `5511987654321`

```php
// All formats accepted (auto-converted)
$converx->sendMessage('11987654321', 'Hello!');      // DDD+number
$converx->sendMessage('5511987654321', 'Hello!');    // With country code
$converx->sendMessage('+5511987654321', 'Hello!');   // E.164
$converx->sendMessage('(11) 98765-4321', 'Hello!');  // Formatted
```

## Template Setup

### Creating Templates in Converx

1. Access Converx Dashboard
2. Go to Settings > WhatsApp Templates
3. Create new template following WhatsApp guidelines
4. Submit for approval
5. Use template name in code after approval

### Template Example

```
Template Name: order_confirmation
Language: pt_BR
Category: UTILITY

Content:
Ola {{1}}!

Seu pedido #{{2}} foi confirmado.
Valor total: {{3}}

Obrigado por comprar conosco!
```

## Error Handling

```php
use Eduardoks98\WhatsAppConverx\Exceptions\ConverxException;

try {
    $response = $converx->sendMessage($phone, $message);
} catch (ConverxException $e) {
    Log::error('Converx error: ' . $e->getMessage());

    // Common errors:
    // - Invalid phone number
    // - Contact not found
    // - Template not approved
    // - API rate limit
}
```

## Configuration Options

```php
// config/converx.php
return [
    'account_id' => env('CONVERX_ACCOUNT_ID', '8'),
    'api_token' => env('CONVERX_API_TOKEN'),
    'api_url' => env('CONVERX_API_URL', 'https://converx.app/api/v1'),
    'inbox_id' => env('CONVERX_INBOX_ID', '1'),

    'templates' => [
        'lead_notification' => env('CONVERX_TEMPLATE_LEAD', 'notificar_lead_vendedor'),
        'namespace' => env('CONVERX_TEMPLATE_NAMESPACE'),
    ],

    'defaults' => [
        'country_code' => '55', // Brazil
    ],
];
```

## Features

- Text messages
- Template messages
- Automatic contact management
- Conversation handling
- Brazilian phone format support
- Auto country code (55)
- Error handling
- Simple configuration

## Use Cases

### 1. Customer Support

```php
// Incoming support request
$converx->sendMessage(
    $customer->phone,
    'Ola! Recebemos sua solicitacao e responderemos em breve.'
);
```

### 2. Order Notifications

```php
// Order confirmed
$converx->sendTemplate(
    $order->customer_phone,
    'order_confirmation',
    [
        '1' => $order->customer_name,
        '2' => $order->id,
        '3' => $order->formatted_total
    ]
);
```

### 3. Lead Notification to Sales

```php
// Notify sales team about new lead
foreach ($salesTeam as $seller) {
    $converx->sendTemplate(
        $seller->phone,
        'notificar_lead_vendedor',
        [
            '1' => $lead->name,
            '2' => $lead->phone,
            '3' => $lead->interest
        ]
    );
}
```

### 4. Appointment Reminders

```php
// Send reminder
$converx->sendTemplate(
    $appointment->customer_phone,
    'appointment_reminder',
    [
        '1' => $appointment->customer_name,
        '2' => $appointment->date->format('d/m/Y'),
        '3' => $appointment->time
    ]
);
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Why Converx?

1. **Brazilian Provider**: Local support in Portuguese
2. **Chatwoot-based**: Familiar API for Chatwoot users
3. **Simple Setup**: Easy configuration
4. **Affordable**: Competitive pricing for Brazil
5. **Multi-agent**: Support for multiple agents
6. **Dashboard**: Web interface for management

## Converx vs Official API

| Feature | Converx | Official API |
|---------|---------|--------------|
| Setup | Simple | Complex |
| Approval | Fast | 24-48h |
| Support | Portuguese | English |
| Dashboard | Yes | Meta Business |
| Price | Competitive | Meta pricing |
| Best for | Small/Medium | Enterprise |

## Related

- [WhatsApp Official](./whatsapp-official.md) - Meta's official API
- [SMS Comtele](./sms-comtele.md) - Brazilian SMS
