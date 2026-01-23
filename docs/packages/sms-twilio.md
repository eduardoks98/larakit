# Twilio SMS Package

> Twilio SMS integration for global messaging in Laravel applications.

## Overview

The `eduardoks98/sms-twilio` package provides comprehensive Twilio SMS integration with support for single and bulk messaging, templates, delivery tracking, and webhook handling.

## Installation

```bash
composer require eduardoks98/sms-twilio
```

## Configuration

### Environment Variables

```env
TWILIO_ACCOUNT_SID=ACxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
TWILIO_WEBHOOK_URL=https://yourapp.com/api/webhooks/twilio
```

### Publish Config & Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\SmsTwilio\SmsTwilioServiceProvider" --tag="config"
php artisan vendor:publish --provider="Eduardoks98\SmsTwilio\SmsTwilioServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage

### Send Single SMS

```php
use Eduardoks98\SmsTwilio\Services\TwilioService;

$twilio = app(TwilioService::class);

// Simple message
$message = $twilio->send(
    to: '+5511987654321',
    body: 'Hello! Your verification code is: 123456'
);

// With options
$message = $twilio->send(
    to: '+5511987654321',
    body: 'Your order has been shipped!',
    options: [
        'statusCallback' => 'https://yourapp.com/api/webhooks/twilio',
        'maxPrice' => '0.50'
    ]
);
```

### Send with Template

```php
// Define templates in config
// config/sms-twilio.php
'templates' => [
    'verification_code' => 'Your verification code is: {code}. Valid for 5 minutes.',
    'order_shipped' => 'Hi {name}! Your order #{order_id} has been shipped.',
    'payment_received' => 'Payment of {amount} received. Thank you!',
],

// Send using template
$message = $twilio->sendTemplate(
    to: '+5511987654321',
    template: 'verification_code',
    variables: ['code' => '123456']
);

$message = $twilio->sendTemplate(
    to: '+5511987654321',
    template: 'order_shipped',
    variables: [
        'name' => 'John',
        'order_id' => '12345'
    ]
);
```

### Bulk SMS

```php
// Send same message to multiple recipients
$results = $twilio->sendBulk(
    recipients: [
        '+5511987654321',
        '+5521987654321',
        '+5531987654321'
    ],
    body: 'Flash sale! 50% off today only!'
);

// Check results
foreach ($results as $result) {
    if ($result['success']) {
        echo "Sent to {$result['to']}: {$result['message_sid']}";
    } else {
        echo "Failed to {$result['to']}: {$result['error']}";
    }
}
```

### Check Message Status

```php
// Get message by SID
$message = $twilio->getMessage($messageSid);
echo $message->status; // queued, sending, sent, delivered, failed

// Get all messages for a number
$messages = TwilioMessage::where('to', '+5511987654321')->get();
```

## Webhook Handling

### Status Callback

```php
// Auto-registered route
Route::post('/webhooks/twilio', [TwilioWebhookController::class, 'handleStatus']);

// Configure in Twilio Console or per-message:
// Status Callback URL: https://yourapp.com/api/webhooks/twilio
```

### Message Statuses

| Status | Description |
|--------|-------------|
| `queued` | Message queued for sending |
| `sending` | Message being sent |
| `sent` | Message sent to carrier |
| `delivered` | Message delivered to phone |
| `failed` | Message failed |
| `undelivered` | Message not delivered |

### Custom Status Handler

```php
use Eduardoks98\SmsTwilio\Events\MessageDelivered;
use Eduardoks98\SmsTwilio\Events\MessageFailed;

// In EventServiceProvider
protected $listen = [
    MessageDelivered::class => [
        HandleSmsDelivered::class,
    ],
    MessageFailed::class => [
        HandleSmsFailed::class,
    ],
];
```

## Phone Number Format

Twilio requires E.164 format:
- Format: `+[country code][number]`
- Example Brazil: `+5511987654321`
- Example USA: `+12025551234`

```php
// The service auto-formats numbers
$twilio->send('+5511987654321', 'Hello!'); // Already E.164
$twilio->send('5511987654321', 'Hello!');  // Auto-adds +
```

## Database Model

```php
use Eduardoks98\SmsTwilio\Models\TwilioMessage;

$message = TwilioMessage::find($id);
$message->sid;        // Twilio message SID
$message->to;         // Recipient number
$message->body;       // Message content
$message->status;     // Current status
$message->price;      // Message cost

// Scopes
TwilioMessage::delivered()->get();
TwilioMessage::failed()->get();
TwilioMessage::pending()->get();
TwilioMessage::forNumber('+5511987654321')->get();
```

## Features

- Single SMS sending
- Template support with variables
- Bulk SMS (batch sending)
- Delivery status tracking
- Webhook handling
- Database logging
- E.164 auto-formatting
- Error handling with retries

## Error Handling

```php
use Eduardoks98\SmsTwilio\Exceptions\TwilioException;

try {
    $message = $twilio->send($to, $body);
} catch (TwilioException $e) {
    // Handle specific Twilio errors
    Log::error('Twilio error: ' . $e->getMessage());

    // Check error code
    if ($e->getCode() === 21211) {
        // Invalid phone number
    }
}
```

## Dependencies

- `twilio/sdk` ^8.0
- `eduardoks98/base-api` ^1.0

## Pricing

Twilio SMS pricing varies by country:
- USA: ~$0.0079/message
- Brazil: ~$0.0675/message
- See [Twilio Pricing](https://www.twilio.com/sms/pricing)

## Related

- [SMS Comtele](./sms-comtele.md) - Brazilian SMS provider
- [WhatsApp Official](./whatsapp-official.md)
