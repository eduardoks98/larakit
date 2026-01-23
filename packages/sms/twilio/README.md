# 📱 Twilio SMS - Laravel Package

Send SMS messages globally using Twilio's powerful API with Laravel.

## ✨ Features

- ✅ **Easy SMS Sending** - Send SMS to any country using Twilio
- ✅ **Template Support** - Use message templates with variables
- ✅ **Bulk Sending** - Send to multiple recipients efficiently
- ✅ **Delivery Tracking** - Track delivery status in database
- ✅ **Webhook Handling** - Automatic status updates via webhooks
- ✅ **Phone Validation** - E.164 format validation
- ✅ **Error Handling** - Comprehensive error handling and logging
- ✅ **Cost Tracking** - Track SMS costs per message
- ✅ **Retry Logic** - Automatic retry for failed messages (configurable)

## 📦 Installation

```bash
composer require eduardoks98/sms-twilio
```

### Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --provider="Eduardoks98\SmsTwilio\SmsTwilioServiceProvider" --tag="config"

# Publish migrations
php artisan vendor:publish --provider="Eduardoks98\SmsTwilio\SmsTwilioServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate
```

## ⚙️ Configuration

Add to your `.env`:

```env
# Twilio Credentials (from https://www.twilio.com/console)
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token

# Twilio Phone Number (E.164 format)
TWILIO_FROM_NUMBER=+15551234567

# Or use Messaging Service SID instead
TWILIO_MESSAGING_SERVICE_SID=MGxxxxxxxxxxxxxxxxxxxx

# Webhook Configuration
TWILIO_WEBHOOK_ENABLED=true
TWILIO_WEBHOOK_URL="${APP_URL}/api/webhooks/twilio/status"

# Delivery Tracking
TWILIO_TRACK_DELIVERY=true

# Phone Validation
TWILIO_VALIDATE_PHONE=true

# Trial Mode (can only send to verified numbers)
TWILIO_TRIAL_MODE=false

# Default Country Code (for numbers without country prefix)
TWILIO_DEFAULT_COUNTRY_CODE=55  # Brazil

# Retry Configuration
TWILIO_RETRY_ENABLED=true
TWILIO_RETRY_MAX_ATTEMPTS=3
TWILIO_RETRY_DELAY_SECONDS=60
```

## 🚀 Usage

### Basic SMS Sending

```php
use Eduardoks98\SmsTwilio\Services\TwilioService;

$twilio = new TwilioService();

// Send SMS
$message = $twilio->send(
    to: '+5511987654321',
    body: 'Hello from Laravel!'
);

// Check status
echo $message->status->value; // 'queued', 'sent', 'delivered', etc.
echo $message->message_sid;   // Twilio message SID
```

### Using Dependency Injection

```php
use Eduardoks98\SmsTwilio\Services\TwilioService;

class NotificationService
{
    public function __construct(
        protected TwilioService $twilio
    ) {}

    public function sendWelcomeSMS(User $user)
    {
        return $this->twilio->send(
            to: $user->phone,
            body: "Welcome to our platform, {$user->name}!"
        );
    }
}
```

### Template Messages

```php
$message = $twilio->sendTemplate(
    to: '+5511987654321',
    template: 'Hello {name}, your order #{order_id} is {status}!',
    variables: [
        'name' => 'John',
        'order_id' => '12345',
        'status' => 'confirmed',
    ]
);

// Sends: "Hello John, your order #12345 is confirmed!"
```

### Bulk Sending

```php
$recipients = [
    '+5511987654321',
    '+5511987654322',
    '+5511987654323',
];

$messages = $twilio->sendBulk(
    recipients: $recipients,
    body: 'Flash sale! 50% off today only!'
);

// Returns array of TwilioMessage objects
foreach ($messages as $message) {
    echo "Sent to {$message->to}: {$message->status->value}\n";
}
```

### Sending with Media (MMS)

```php
$message = $twilio->send(
    to: '+5511987654321',
    body: 'Check out this image!',
    options: [
        'mediaUrl' => [
            'https://example.com/image.jpg'
        ]
    ]
);
```

### Check Message Status

```php
$status = $twilio->getMessageStatus('SM1234567890abcdef');

echo "Status: {$status['status']}\n";
echo "Price: {$status['price']} {$status['priceUnit']}\n";
echo "Segments: {$status['numSegments']}\n";
```

### Query Messages from Database

```php
use Eduardoks98\SmsTwilio\Models\TwilioMessage;

// Get all delivered messages
$delivered = TwilioMessage::delivered()->get();

// Get failed messages
$failed = TwilioMessage::failed()->get();

// Get messages to specific number
$messages = TwilioMessage::where('to', '+5511987654321')->get();

// Get today's messages
$today = TwilioMessage::whereDate('created_at', today())->get();

// Calculate total cost
$totalCost = TwilioMessage::delivered()
    ->sum('price');
```

### Using Model Methods

```php
$message = TwilioMessage::find(1);

// Check status
if ($message->isDelivered()) {
    echo "Message delivered!";
}

if ($message->hasFailed()) {
    echo "Failed: {$message->error_message}";
}

if ($message->isPending()) {
    echo "Still pending...";
}

// Get cost
$cost = $message->getTotalCost();
echo "Cost: \${$cost}";
```

## 🎯 Webhook Setup

### Configure Twilio Webhook

1. Go to [Twilio Console](https://www.twilio.com/console/phone-numbers/incoming)
2. Select your phone number
3. Under **Messaging**, set **A MESSAGE COMES IN** to:
   - Webhook: `https://yourdomain.com/api/webhooks/twilio/incoming`
   - HTTP POST

4. Set **STATUS CALLBACK URL** to:
   - `https://yourdomain.com/api/webhooks/twilio/status`
   - HTTP POST

### Webhook Routes

The package automatically registers these routes:

```
POST /api/webhooks/twilio/status    → Status updates
POST /api/webhooks/twilio/incoming  → Incoming messages
```

### Custom Webhook Handling

Extend the webhook controller for custom logic:

```php
namespace App\Http\Controllers;

use Eduardoks98\SmsTwilio\Http\Controllers\TwilioWebhookController as BaseTwilioWebhookController;
use Illuminate\Http\Request;

class CustomTwilioWebhookController extends BaseTwilioWebhookController
{
    public function handleIncomingMessage(Request $request)
    {
        $from = $request->input('From');
        $body = $request->input('Body');

        // Custom logic
        // - Auto-reply
        // - Store in chat system
        // - Trigger notifications

        return parent::handleIncomingMessage($request);
    }
}
```

## 📊 Status Tracking

### Available Statuses

Based on [Twilio official documentation](https://www.twilio.com/docs/messaging/api/message-resource#message-status-values):

- `queued` - Message is queued and waiting to be sent
- `sending` - Message is currently being sent
- `sent` - Message sent to carrier
- `delivered` - Message successfully delivered
- `failed` - Message failed to send
- `undelivered` - Carrier reported failure

### Status Enum Methods

```php
use Eduardoks98\SmsTwilio\Enums\MessageStatus;

$status = MessageStatus::DELIVERED;

$status->isSuccess();   // true
$status->isFailure();   // false
$status->isPending();   // false
$status->isFinal();     // true
```

## 🔧 Advanced Features

### Character Limits

```php
// GSM-7 encoding: 160 characters per segment
// UCS-2 (Unicode): 70 characters per segment

$message = $twilio->send(
    to: '+5511987654321',
    body: str_repeat('A', 200) // Will use 2 segments
);

echo "Segments: {$message->num_segments}";
```

### Trial Mode

```php
// In trial mode, you can only send to verified numbers
// Configure in .env: TWILIO_TRIAL_MODE=true

// To verify a number: https://www.twilio.com/console/phone-numbers/verified
```

### Error Handling

```php
use Twilio\Exceptions\TwilioException;

try {
    $message = $twilio->send('+invalid', 'Test');
} catch (TwilioException $e) {
    echo "Twilio Error: {$e->getMessage()}";
    echo "Error Code: {$e->getCode()}";
}
```

## 🌍 International SMS

### E.164 Format

All phone numbers must be in E.164 format:

```
+[country code][area code][local number]

Examples:
+15551234567    (USA)
+5511987654321  (Brazil)
+442071234567   (UK)
+861234567890   (China)
```

### Auto-formatting

The package automatically formats numbers:

```php
// These all work:
$twilio->send('11987654321', 'Test');      // Adds +55
$twilio->send('5511987654321', 'Test');    // Adds +
$twilio->send('+5511987654321', 'Test');   // Already formatted
```

## 💰 Cost Tracking

### View Costs

```php
use Eduardoks98\SmsTwilio\Models\TwilioMessage;

// Total cost today
$todayCost = TwilioMessage::whereDate('created_at', today())
    ->sum('price');

// Cost by country (based on phone prefix)
$costs = TwilioMessage::selectRaw('SUBSTRING(to, 1, 3) as country, SUM(price) as total')
    ->groupBy('country')
    ->get();

// Average cost per message
$avgCost = TwilioMessage::avg('price');
```

## 📚 Official Documentation

- [Twilio PHP SDK](https://github.com/twilio/twilio-php)
- [Twilio Messaging API](https://www.twilio.com/docs/messaging/api/message-resource)
- [Twilio Webhooks](https://www.twilio.com/docs/messaging/guides/webhook-request)
- [Twilio Error Codes](https://www.twilio.com/docs/api/errors)

## 🧪 Testing

```bash
composer test
```

## 📝 License

MIT License

## 🤝 Contributing

Pull requests are welcome!

## 📧 Support

For issues, please use GitHub Issues.

---

**Built with ❤️ for the Laravel community**
