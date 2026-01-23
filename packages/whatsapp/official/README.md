# 💬 WhatsApp Official - Laravel Package

Send WhatsApp messages using Meta's official WhatsApp Business Cloud API with Laravel.

## ✨ Features

- ✅ **Official Meta API** - WhatsApp Business Cloud API integration
- ✅ **Text Messages** - Send text with URL preview
- ✅ **Media Messages** - Images, videos, audio, documents
- ✅ **Template Messages** - Pre-approved message templates
- ✅ **Interactive Messages** - Buttons and lists (via SDK)
- ✅ **Delivery Tracking** - Track message status (sent, delivered, read)
- ✅ **Webhook Handling** - Automatic status updates and incoming messages
- ✅ **Free Tier** - 1,000 free conversations per month
- ✅ **Read Receipts** - Know when messages are read
- ✅ **Error Handling** - Comprehensive error handling and logging

## 📦 Installation

```bash
composer require eduardoks98/whatsapp-official
```

### Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --provider="Eduardoks98\WhatsAppOfficial\WhatsAppOfficialServiceProvider" --tag="config"

# Publish migrations
php artisan vendor:publish --provider="Eduardoks98\WhatsAppOfficial\WhatsAppOfficialServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate
```

## 🔑 Getting Your Credentials

### 1. Create WhatsApp Business App

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Create a new app → Select "Business" type
3. Add **WhatsApp** product to your app

### 2. Get Your Credentials

From WhatsApp → Getting Started page, copy:
- **Phone Number ID** (`from_phone_number_id`)
- **Access Token** (`access_token`)
- **Business Account ID** (optional)

### 3. Configure .env

```env
# WhatsApp Business Cloud API Credentials
WHATSAPP_FROM_PHONE_NUMBER_ID=your-phone-number-id
WHATSAPP_ACCESS_TOKEN=your-access-token
WHATSAPP_BUSINESS_ACCOUNT_ID=your-business-account-id

# API Configuration
WHATSAPP_API_VERSION=v18.0

# Webhook Configuration
WHATSAPP_WEBHOOK_URL="${APP_URL}/api/webhooks/whatsapp"
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your-secure-verify-token

# Features
WHATSAPP_TRACK_MESSAGES=true
WHATSAPP_DEFAULT_LANGUAGE=pt_BR

# Rate Limiting
WHATSAPP_RATE_LIMIT_PER_SECOND=80
WHATSAPP_DAILY_LIMIT=100000

# Free Tier
WHATSAPP_FREE_TIER_ENABLED=true
```

## 🚀 Usage

### Send Text Message

```php
use Eduardoks98\WhatsAppOfficial\Services\WhatsAppService;

$whatsapp = new WhatsAppService();

// Simple text message
$message = $whatsapp->sendText(
    to: '+5511987654321',
    text: 'Hello from Laravel!'
);

// Text with URL preview
$message = $whatsapp->sendText(
    to: '+5511987654321',
    text: 'Check this out: https://example.com',
    previewUrl: true
);

// Check status
echo $message->status->value; // 'queued', 'sent', 'delivered', 'read'
echo $message->message_id;    // WhatsApp message ID (wamid.*)
```

### Using Dependency Injection

```php
use Eduardoks98\WhatsAppOfficial\Services\WhatsAppService;

class NotificationService
{
    public function __construct(
        protected WhatsAppService $whatsapp
    ) {}

    public function sendWelcomeMessage(User $user)
    {
        return $this->whatsapp->sendText(
            to: $user->phone,
            text: "Welcome {$user->name}! Your account is ready."
        );
    }
}
```

### Send Image

```php
// Send image from URL
$message = $whatsapp->sendImage(
    to: '+5511987654321',
    imageUrl: 'https://example.com/image.jpg',
    caption: 'Check out this image!'
);

// Send image from media ID (uploaded to WhatsApp)
$message = $whatsapp->sendImage(
    to: '+5511987654321',
    imageUrl: 'media-id-from-whatsapp'
);
```

### Send Document

```php
$message = $whatsapp->sendDocument(
    to: '+5511987654321',
    documentUrl: 'https://example.com/invoice.pdf',
    caption: 'Your invoice',
    filename: 'Invoice-2024.pdf'
);
```

### Send Template Message

**Important**: Templates must be pre-approved by WhatsApp.

```php
// Template without parameters
$message = $whatsapp->sendTemplate(
    to: '+5511987654321',
    templateName: 'hello_world',
    language: 'en_US'
);

// Template with parameters
$message = $whatsapp->sendTemplate(
    to: '+5511987654321',
    templateName: 'order_confirmation',
    language: 'pt_BR',
    parameters: [
        'John Doe',      // {{1}}
        '12345',         // {{2}}
        'R$ 99,90'       // {{3}}
    ]
);
```

**Template Example**:
```
Olá {{1}}, seu pedido #{{2}} no valor de {{3}} foi confirmado!
```

### Query Messages from Database

```php
use Eduardoks98\WhatsAppOfficial\Models\WhatsAppMessage;

// Get all delivered messages
$delivered = WhatsAppMessage::delivered()->get();

// Get read messages
$read = WhatsAppMessage::read()->get();

// Get failed messages
$failed = WhatsAppMessage::failed()->get();

// Get text messages only
$textMessages = WhatsAppMessage::textMessages()->get();

// Get media messages
$mediaMessages = WhatsAppMessage::mediaMessages()->get();

// Get template messages
$templates = WhatsAppMessage::templateMessages()->get();

// Get today's messages
$today = WhatsAppMessage::whereDate('created_at', today())->get();
```

### Using Model Methods

```php
$message = WhatsAppMessage::find(1);

// Check status
if ($message->isDelivered()) {
    echo "Message delivered!";
}

if ($message->isRead()) {
    echo "Message was read!";
}

if ($message->hasFailed()) {
    echo "Failed: {$message->error_message}";
}

// Check type
if ($message->hasMedia()) {
    echo "Media URL: {$message->media_url}";
}

if ($message->isTemplate()) {
    echo "Template: {$message->template_name}";
}
```

## 🎯 Webhook Setup

### 1. Configure Webhook in Meta Dashboard

1. Go to **WhatsApp** → **Configuration**
2. Under **Webhook**, click **Edit**
3. Set:
   - **Callback URL**: `https://yourdomain.com/api/webhooks/whatsapp`
   - **Verify Token**: Same as `WHATSAPP_WEBHOOK_VERIFY_TOKEN` in `.env`
4. Subscribe to:
   - `messages` - Incoming messages
   - `message_status` - Delivery status updates

### 2. Webhook Routes

The package automatically registers these routes:

```
GET  /api/webhooks/whatsapp → Webhook verification
POST /api/webhooks/whatsapp → Webhook handler
```

### 3. Status Update Webhook

When message status changes, WhatsApp sends:

```json
{
  "object": "whatsapp_business_account",
  "entry": [{
    "changes": [{
      "value": {
        "statuses": [{
          "id": "wamid.xxx",
          "status": "delivered",
          "timestamp": "1234567890"
        }]
      }
    }]
  }]
}
```

The package automatically updates the database with the new status.

### 4. Incoming Message Webhook

```json
{
  "object": "whatsapp_business_account",
  "entry": [{
    "changes": [{
      "value": {
        "messages": [{
          "from": "5511987654321",
          "id": "wamid.xxx",
          "type": "text",
          "text": {
            "body": "Hello!"
          }
        }]
      }
    }]
  }]
}
```

### Custom Webhook Handling

```php
namespace App\Http\Controllers;

use Eduardoks98\WhatsAppOfficial\Http\Controllers\WhatsAppWebhookController as BaseController;
use Illuminate\Http\Request;

class CustomWhatsAppWebhookController extends BaseController
{
    protected function handleIncomingMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $from = $message['from'];
            $text = $message['text']['body'] ?? '';

            // Custom logic
            // - Store in chat system
            // - Trigger auto-reply
            // - Send to CRM

            // Auto-reply example
            if (str_contains(strtolower($text), 'hello')) {
                $this->whatsappService->sendText(
                    $from,
                    'Hi! How can I help you today?'
                );
            }
        }

        parent::handleIncomingMessages($messages);
    }
}
```

## 📱 Phone Number Format

WhatsApp uses **E.164 format**:

```
Correct:
+5511987654321  (Brazil)
+15551234567    (USA)
+442071234567   (UK)

Incorrect:
5511987654321   (missing +)
11987654321     (missing country code)
```

The package automatically adds `+` if missing.

## 💰 Pricing & Free Tier

### Free Tier
- **1,000 service conversations per month** - FREE
- Includes all message types
- Resets monthly

### After Free Tier
- Pricing varies by country
- Check current rates: [WhatsApp Pricing](https://developers.facebook.com/docs/whatsapp/pricing)

### Conversation Types
- **Service conversations**: Business-initiated (templates)
- **User conversations**: User-initiated (24h window)

## 🎨 Message Templates

### Creating Templates

1. Go to **WhatsApp** → **Message Templates**
2. Click **Create Template**
3. Choose:
   - **Category**: Utility, Marketing, or Authentication
   - **Name**: `order_confirmation`, `welcome_message`, etc
   - **Language**: pt_BR, en_US, etc
4. Add content with variables `{{1}}`, `{{2}}`, etc
5. Submit for approval (usually approved within minutes)

### Template Example

**Name**: `order_confirmation`
**Category**: Utility
**Language**: pt_BR

**Content**:
```
Olá {{1}},

Seu pedido #{{2}} foi confirmado!
Valor: {{3}}
Previsão de entrega: {{4}}

Obrigado por comprar conosco!
```

**Usage**:
```php
$whatsapp->sendTemplate(
    to: '+5511987654321',
    templateName: 'order_confirmation',
    language: 'pt_BR',
    parameters: [
        'João Silva',
        '12345',
        'R$ 149,90',
        '3-5 dias úteis'
    ]
);
```

## 📊 Message Status Flow

```
queued → sent → delivered → read
  ↓
failed
```

- **queued**: Message is being sent
- **sent**: Message reached WhatsApp servers
- **delivered**: Message delivered to recipient's device
- **read**: Recipient opened the message
- **failed**: Message failed to send

## 🔧 Error Handling

```php
try {
    $message = $whatsapp->sendText('+invalid', 'Test');
} catch (\Exception $e) {
    // Common errors:
    // - Invalid phone number
    // - Template not found
    // - Template not approved
    // - Rate limit exceeded
    // - Invalid access token

    echo "Error: {$e->getMessage()}";
}
```

## 📏 Media Limits

### Image
- Formats: JPEG, PNG
- Max size: 5 MB
- Max dimension: 4096px

### Video
- Formats: MP4, 3GPP
- Max size: 16 MB

### Audio
- Formats: AAC, MP4, MPEG, AMR, OGG
- Max size: 16 MB

### Document
- Formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX
- Max size: 100 MB

## 🌐 Official Documentation

- [WhatsApp Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [API Reference](https://developers.facebook.com/docs/whatsapp/cloud-api/reference)
- [PHP SDK](https://github.com/netflie/whatsapp-cloud-api)
- [Message Templates](https://developers.facebook.com/docs/whatsapp/business-management-api/message-templates)
- [Webhooks](https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks)

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
