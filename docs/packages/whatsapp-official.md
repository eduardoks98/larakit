# WhatsApp Official Package

> WhatsApp Business Cloud API integration for Laravel applications.

## Overview

The `eduardoks98/whatsapp-official` package provides official WhatsApp Business Cloud API integration, supporting text messages, media, templates, and webhooks through Meta's official API.

## Installation

```bash
composer require eduardoks98/whatsapp-official
```

## Configuration

### Environment Variables

```env
WHATSAPP_FROM_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_verify_token
WHATSAPP_API_VERSION=v18.0
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\WhatsAppOfficial\WhatsAppOfficialServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\WhatsAppOfficial\WhatsAppOfficialServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### Send Text Message

```php
use Eduardoks98\WhatsAppOfficial\Services\WhatsAppService;

$whatsapp = app(WhatsAppService::class);

// Simple text
$message = $whatsapp->sendText(
    to: '+5511987654321',
    text: 'Hello! Your order has been confirmed.'
);

// With URL preview
$message = $whatsapp->sendText(
    to: '+5511987654321',
    text: 'Check out our new products: https://example.com/products',
    previewUrl: true
);
```

### Send Media Messages

```php
// Send image
$message = $whatsapp->sendImage(
    to: '+5511987654321',
    imageUrl: 'https://example.com/product.jpg',
    caption: 'Check out our new product!'
);

// Send document
$message = $whatsapp->sendDocument(
    to: '+5511987654321',
    documentUrl: 'https://example.com/invoice.pdf',
    caption: 'Your invoice is attached',
    filename: 'invoice_12345.pdf'
);

// Send video
$message = $whatsapp->sendVideo(
    to: '+5511987654321',
    videoUrl: 'https://example.com/demo.mp4',
    caption: 'Product demonstration'
);

// Send audio
$message = $whatsapp->sendAudio(
    to: '+5511987654321',
    audioUrl: 'https://example.com/message.mp3'
);
```

### Send Template Messages

Templates must be pre-approved by Meta.

```php
// Simple template (no parameters)
$message = $whatsapp->sendTemplate(
    to: '+5511987654321',
    templateName: 'hello_world',
    language: 'pt_BR'
);

// Template with parameters
$message = $whatsapp->sendTemplate(
    to: '+5511987654321',
    templateName: 'order_confirmation',
    language: 'pt_BR',
    parameters: [
        // Header parameters
        'header' => [
            ['type' => 'text', 'text' => 'John Doe']
        ],
        // Body parameters
        'body' => [
            ['type' => 'text', 'text' => '12345'],
            ['type' => 'text', 'text' => 'R$ 149,90'],
            ['type' => 'text', 'text' => '3 dias uteis']
        ]
    ]
);
```

### Interactive Messages

```php
// Button message
$message = $whatsapp->sendInteractiveButtons(
    to: '+5511987654321',
    body: 'How would you rate our service?',
    buttons: [
        ['id' => 'rating_good', 'title' => 'Good'],
        ['id' => 'rating_ok', 'title' => 'OK'],
        ['id' => 'rating_bad', 'title' => 'Bad']
    ]
);

// List message
$message = $whatsapp->sendInteractiveList(
    to: '+5511987654321',
    body: 'Select a product category:',
    buttonText: 'View Categories',
    sections: [
        [
            'title' => 'Electronics',
            'rows' => [
                ['id' => 'phones', 'title' => 'Smartphones'],
                ['id' => 'laptops', 'title' => 'Laptops']
            ]
        ],
        [
            'title' => 'Fashion',
            'rows' => [
                ['id' => 'clothes', 'title' => 'Clothing'],
                ['id' => 'shoes', 'title' => 'Shoes']
            ]
        ]
    ]
);
```

## Webhook Handling

### Webhook Verification

```php
// Auto-registered routes
Route::get('/api/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/api/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handleWebhook']);
```

### Configure in Meta Dashboard

1. Go to [Meta Developers](https://developers.facebook.com)
2. Select your app > WhatsApp > Configuration
3. Set Callback URL: `https://yourapp.com/api/webhooks/whatsapp`
4. Set Verify Token: same as `WHATSAPP_WEBHOOK_VERIFY_TOKEN`
5. Subscribe to: `messages`, `message_status`

### Message Statuses

| Status | Description |
|--------|-------------|
| `sent` | Message sent to WhatsApp servers |
| `delivered` | Message delivered to device |
| `read` | Message read by recipient |
| `failed` | Message failed to send |

### Handle Incoming Messages

```php
use Eduardoks98\WhatsAppOfficial\Events\MessageReceived;

// In EventServiceProvider
protected $listen = [
    MessageReceived::class => [
        HandleWhatsAppMessage::class,
    ],
];

// Handler
class HandleWhatsAppMessage
{
    public function handle(MessageReceived $event)
    {
        $message = $event->message;
        $from = $message['from'];
        $text = $message['text']['body'] ?? null;

        // Process incoming message
        if ($text === 'status') {
            // Send order status
        }
    }
}
```

## Phone Number Format

WhatsApp uses E.164 format:
- Format: `+[country code][number]`
- Example: `+5511987654321`

```php
// Auto-formatting
$whatsapp->sendText('+5511987654321', 'Hello!'); // Already E.164
$whatsapp->sendText('5511987654321', 'Hello!');  // Auto-adds +
```

## Database Model

```php
use Eduardoks98\WhatsAppOfficial\Models\WhatsAppMessage;

$message = WhatsAppMessage::find($id);
$message->wamid;       // WhatsApp message ID
$message->to;          // Recipient number
$message->type;        // text, image, template, etc.
$message->content;     // Message content/payload
$message->status;      // sent, delivered, read, failed

// Scopes
WhatsAppMessage::delivered()->get();
WhatsAppMessage::read()->get();
WhatsAppMessage::failed()->get();
WhatsAppMessage::ofType('template')->get();
```

## Message Types

| Type | Description |
|------|-------------|
| `text` | Plain text message |
| `image` | Image with optional caption |
| `video` | Video with optional caption |
| `audio` | Audio message |
| `document` | Document/file |
| `template` | Pre-approved template |
| `interactive` | Buttons or list |

## Features

- Text messages with URL preview
- Media messages (image, video, audio, document)
- Template messages (pre-approved)
- Interactive messages (buttons, lists)
- Webhook handling (status updates, incoming)
- Message tracking in database
- E.164 auto-formatting
- Comprehensive error handling

## Template Creation

Templates must be created and approved in Meta Business Manager:

1. Go to Meta Business Suite
2. Navigate to WhatsApp Manager > Message Templates
3. Create new template
4. Wait for approval (usually 24-48 hours)
5. Use template name in your code

## Dependencies

- `netflie/whatsapp-cloud-api` ^3.0
- `eduardoks98/base-api` ^1.0

## Pricing

WhatsApp Business API pricing:
- **User-initiated**: Varies by country (~$0.05 Brazil)
- **Business-initiated**: Varies by country (~$0.08 Brazil)
- First 1,000 conversations/month are free
- See [WhatsApp Pricing](https://developers.facebook.com/docs/whatsapp/pricing)

## Related

- [WhatsApp Converx](./whatsapp-converx.md) - Brazilian provider
- [SMS Twilio](./sms-twilio.md)
