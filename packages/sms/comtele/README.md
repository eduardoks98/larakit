# 📱 Comtele SMS - Laravel Package

Send SMS messages in Brazil using Comtele's cost-effective API with Laravel.

## ✨ Features

- ✅ **Brazilian SMS Provider** - Optimized for Brazil with competitive pricing
- ✅ **Easy SMS Sending** - Simple API for sending SMS
- ✅ **Template Support** - Use message templates with variables
- ✅ **Bulk Sending** - Send to up to 100 recipients per request
- ✅ **Delivery Tracking** - Track delivery status in database
- ✅ **Webhook Handling** - Automatic status and reply callbacks
- ✅ **Phone Validation** - Brazilian phone number validation (DDD+Number)
- ✅ **Error Handling** - Comprehensive error handling and logging
- ✅ **Detailed Reporting** - Query sent messages by date range

## 📦 Installation

```bash
composer require eduardoks98/sms-comtele
```

### Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --provider="Eduardoks98\SmsComtele\SmsComteleServiceProvider" --tag="config"

# Publish migrations
php artisan vendor:publish --provider="Eduardoks98\SmsComtele\SmsComteleServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate
```

## ⚙️ Configuration

Add to your `.env`:

```env
# Comtele Credentials (from https://sms.comtele.com.br)
COMTELE_API_KEY=your-api-key-here

# API Configuration
COMTELE_API_URL=https://sms.comtele.com.br/api/v2
COMTELE_DEFAULT_SENDER=laravel-app

# Webhook Configuration (configure in Comtele dashboard)
COMTELE_WEBHOOK_STATUS="${APP_URL}/api/webhooks/comtele/status"
COMTELE_WEBHOOK_REPLY="${APP_URL}/api/webhooks/comtele/reply"

# Features
COMTELE_TRACK_DELIVERY=true
COMTELE_VALIDATE_PHONE=true

# Bulk Sending
COMTELE_BULK_MAX_RECIPIENTS=100
COMTELE_BULK_CHUNK_SIZE=50

# Rate Limiting
COMTELE_REPORTING_COOLDOWN=30  # seconds between report queries

# Retry Configuration
COMTELE_RETRY_ENABLED=true
COMTELE_RETRY_MAX_ATTEMPTS=3
COMTELE_RETRY_DELAY_SECONDS=60

# HTTP Client
COMTELE_HTTP_TIMEOUT=30
COMTELE_HTTP_CONNECT_TIMEOUT=10
COMTELE_VERIFY_SSL=true
```

## 🔑 Getting Your API Key

1. Access [https://sms.comtele.com.br](https://sms.comtele.com.br)
2. Navigate to: **Developer Information** → **API Section**
3. Copy your **API Key** (format: `XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX`)

## 🚀 Usage

### Basic SMS Sending

```php
use Eduardoks98\SmsComtele\Services\ComteleService;

$comtele = new ComteleService();

// Send SMS to one recipient
$message = $comtele->send(
    receivers: '11987654321',  // DDD + Number format
    content: 'Hello from Laravel!'
);

// Check status
echo $message->status->value; // 'Pending', 'Processed', 'Delivered', 'Error'
echo $message->request_unique_id;  // UUID from Comtele
```

### Using Dependency Injection

```php
use Eduardoks98\SmsComtele\Services\ComteleService;

class NotificationService
{
    public function __construct(
        protected ComteleService $comtele
    ) {}

    public function sendWelcomeSMS(User $user)
    {
        return $this->comtele->send(
            receivers: $user->phone,
            content: "Welcome {$user->name}! Your account is active."
        );
    }
}
```

### Send to Multiple Recipients

```php
// Send to up to 100 recipients in a single request
$message = $comtele->send(
    receivers: ['11987654321', '11987654322', '11987654323'],
    content: 'Flash sale! 50% off today only!'
);

// All recipients will receive the same message
echo $message->getRecipientCount(); // 3
```

### Template Messages

```php
$message = $comtele->sendTemplate(
    receivers: '11987654321',
    template: 'Hello {name}, your order #{order_id} is {status}!',
    variables: [
        'name' => 'John',
        'order_id' => '12345',
        'status' => 'confirmed',
    ]
);

// Sends: "Hello John, your order #12345 is confirmed!"
```

### Bulk Sending (Auto-chunked)

```php
$recipients = [
    '11987654321',
    '11987654322',
    // ... up to thousands of numbers
];

// Automatically chunks into batches of 50 (configurable)
$messages = $comtele->sendBulk(
    receivers: $recipients,
    content: 'Important notification for all users!'
);

// Returns array of ComteleMessage objects
foreach ($messages as $message) {
    echo "Batch sent: {$message->request_unique_id}\n";
}
```

### Custom Sender ID

```php
$message = $comtele->send(
    receivers: '11987654321',
    content: 'Test message',
    sender: 'my-campaign-2024'  // Internal tracking identifier
);

// Later, filter reports by sender
$report = $comtele->getDetailedReport(
    startDate: '2024-01-01',
    endDate: '2024-01-31',
    sender: 'my-campaign-2024'
);
```

### Query Messages from Database

```php
use Eduardoks98\SmsComtele\Models\ComteleMessage;

// Get all delivered messages
$delivered = ComteleMessage::delivered()->get();

// Get failed messages
$failed = ComteleMessage::failed()->get();

// Get pending messages
$pending = ComteleMessage::pending()->get();

// Get messages by sender
$campaign = ComteleMessage::bySender('my-campaign-2024')->get();

// Get today's messages
$today = ComteleMessage::whereDate('created_at', today())->get();

// Get messages from date range
$range = ComteleMessage::byDateRange('2024-01-01', '2024-01-31')->get();
```

### Using Model Methods

```php
$message = ComteleMessage::find(1);

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

// Get recipients
$receivers = $message->getReceiversArray();
echo "Sent to " . $message->getRecipientCount() . " recipients";

// Calculate segments
$segments = $message->getEstimatedSegments();
echo "Message uses {$segments} SMS segment(s)";
```

## 📊 Detailed Reporting

### Get Delivery Reports

```php
// Get detailed report from Comtele API
$report = $comtele->getDetailedReport(
    startDate: '2024-01-01',
    endDate: '2024-01-31'
);

// Filter by sender
$report = $comtele->getDetailedReport(
    startDate: '2024-01-01',
    endDate: '2024-01-31',
    sender: 'my-campaign'
);

// Process report
foreach ($report as $entry) {
    echo "Phone: {$entry['PhoneNumber']}\n";
    echo "Status: {$entry['Status']}\n";
    echo "Date: {$entry['StatusDate']}\n";
}
```

**Important**: Comtele has a **30-second cooldown** between report queries to prevent abuse.

## 🎯 Webhook Setup

### Configure Comtele Webhooks

1. Go to [Comtele Dashboard](https://sms.comtele.com.br)
2. Navigate to **Settings** → **Webhooks**
3. Configure:
   - **Status Callback URL**: `https://yourdomain.com/api/webhooks/comtele/status`
   - **Reply Callback URL**: `https://yourdomain.com/api/webhooks/comtele/reply`

### Webhook Routes

The package automatically registers these routes:

```
POST /api/webhooks/comtele/status  → Delivery status updates
POST /api/webhooks/comtele/reply   → SMS reply callbacks
```

### Status Callback Payload

```json
{
  "Status": "Delivered",
  "PhoneNumber": "11987654321",
  "Sender": "sender_id",
  "StatusDate": "2024-01-24 10:30:00"
}
```

### Reply Callback Payload

```json
{
  "Sender": "sender_id",
  "SentContent": "Original message",
  "ReceivedContent": "Reply from user",
  "ReceiveDate": "2024-01-24 10:35:00"
}
```

### Custom Webhook Handling

```php
namespace App\Http\Controllers;

use Eduardoks98\SmsComtele\Http\Controllers\ComteleWebhookController as BaseController;
use Illuminate\Http\Request;

class CustomComteleWebhookController extends BaseController
{
    public function handleReplyCallback(Request $request)
    {
        $reply = $request->input('ReceivedContent');
        $sender = $request->input('Sender');

        // Custom logic
        // - Store reply in chat system
        // - Trigger auto-response
        // - Notify admin

        return parent::handleReplyCallback($request);
    }
}
```

## 📱 Phone Number Format

### Brazilian Format

Comtele uses **DDD + Number** format (without country code):

```
Correct:
11987654321  (São Paulo mobile - 11 digits)
1133334444   (São Paulo landline - 10 digits)
21987654321  (Rio de Janeiro mobile)

Incorrect:
5511987654321  (has country code 55 - will be auto-removed)
987654321      (missing DDD)
+5511987654321 (has + and country code)
```

### Auto-formatting

The package automatically formats phone numbers:

```php
// These all work and are converted to: 11987654321
$comtele->send('11987654321', 'Test');       // Already correct
$comtele->send('5511987654321', 'Test');     // Removes 55
$comtele->send('+5511987654321', 'Test');    // Removes +55
$comtele->send('011987654321', 'Test');      // Removes leading 0
```

## 📏 Character Limits

### SMS Segmentation

- **Single SMS**: 160 characters
- **Multi-part SMS**: 153 characters per segment

**Important Note**: Some operators (Oi, Sercomtel) don't support message concatenation, so multi-part messages will be received as separate SMS.

### Calculate Segments

```php
$message = ComteleMessage::find(1);
$segments = $message->getEstimatedSegments();

// Examples:
// "Hello"             → 1 segment
// (160 chars message) → 1 segment
// (161 chars message) → 2 segments (153 + 8 chars)
```

## 🔧 Error Handling

### API Error Codes

```php
use Eduardoks98\SmsComtele\Services\ComteleService;

try {
    $message = $comtele->send('invalid', 'Test');
} catch (\Exception $e) {
    // Error codes:
    // 400 - Bad Request (missing params, insufficient credits)
    // 401 - Unauthorized (invalid API key)
    // 404 - Not Found
    // 429 - Rate Limit Exceeded
    // 500 - Internal Server Error
    // 503 - Service Unavailable

    echo "Error: {$e->getMessage()}";
    echo "Code: {$e->getCode()}";
}
```

### Common Errors

**Insufficient Credits**:
```json
{
  "Success": false,
  "Message": "Insufficient credits"
}
```

**Invalid API Key**:
```
HTTP 401 Unauthorized
```

**Too Many Recipients**:
```
Maximum 100 recipients per request. Got: 150
```

**Invalid Phone Number**:
```
Invalid Brazilian phone number: 123. Expected format: DDD+Number (e.g., 11987654321)
```

## 💰 Pricing

Comtele offers competitive pricing for SMS in Brazil. Check current rates at [https://comtele.com.br](https://comtele.com.br).

**Advantages**:
- Cost-effective for Brazilian market
- No monthly fees (pay per message)
- Bulk discounts available
- Support for all Brazilian carriers

## 🌐 API Documentation

- **Official Docs**: [https://docs.comtele.com.br](https://docs.comtele.com.br)
- **SDK Docs**: [https://docs.comtele.com.br/sdk](https://docs.comtele.com.br/sdk)
- **API Endpoint**: `https://sms.comtele.com.br/api/v2`

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

**Built with ❤️ for the Brazilian Laravel community**
