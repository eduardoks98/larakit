# 💬 WhatsApp Converx - Laravel Package

Send WhatsApp messages using Converx's Brazilian provider with Laravel.

## ✨ Features

- ✅ **Brazilian Provider** - Converx WhatsApp integration (Chatwoot-based)
- ✅ **Easy Setup** - Simple Brazilian WhatsApp provider
- ✅ **Text Messages** - Send text messages to WhatsApp contacts
- ✅ **Template Support** - Pre-approved message templates
- ✅ **Conversation Management** - Auto-create and manage conversations
- ✅ **Contact Search** - Find existing contacts automatically
- ✅ **Error Handling** - Comprehensive error handling and logging
- ✅ **Phone Formatting** - Automatic Brazilian phone number formatting

## 📦 Installation

```bash
composer require eduardoks98/whatsapp-converx
```

### Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --provider="Eduardoks98\WhatsAppConverx\WhatsAppConverxServiceProvider" --tag="config"
```

## ⚙️ Configuration

Add to your `.env`:

```env
# Converx Credentials (from https://converx.app)
CONVERX_ACCOUNT_ID=8
CONVERX_API_TOKEN=your-api-token-here
CONVERX_API_URL=https://converx.app/api/v1

# Inbox Configuration
CONVERX_INBOX_ID=1

# Template Configuration (optional)
CONVERX_TEMPLATE_LEAD=notificar_lead_vendedor
CONVERX_TEMPLATE_NAMESPACE=2d984c77_0a6a_48a8_b1ff_0bc434fae591

# HTTP Configuration
CONVERX_HTTP_TIMEOUT=60
CONVERX_VERIFY_SSL=true

# Retry Configuration
CONVERX_RETRY_ENABLED=true
CONVERX_RETRY_MAX_ATTEMPTS=3
CONVERX_RETRY_DELAY_SECONDS=60
```

## 🔑 Getting Your Credentials

1. Access [https://converx.app](https://converx.app)
2. Go to **Settings** → **API**
3. Copy your:
   - **Account ID**
   - **API Access Token**
   - **Inbox ID** (from Inboxes page)

## 🚀 Usage

### Send Text Message

```php
use Eduardoks98\WhatsAppConverx\Services\ConverxService;

$converx = new ConverxService();

// Send WhatsApp message
$response = $converx->sendMessage(
    phoneNumber: '11987654321',  // Brazilian format (DDD + Number)
    message: 'Hello from Laravel!'
);

// Returns conversation data
print_r($response);
```

### Using Dependency Injection

```php
use Eduardoks98\WhatsAppConverx\Services\ConverxService;

class NotificationService
{
    public function __construct(
        protected ConverxService $converx
    ) {}

    public function notifySale(User $user, Order $order)
    {
        if (!$this->converx->isConfigured()) {
            return;
        }

        return $this->converx->sendMessage(
            phoneNumber: $user->phone,
            message: "🎉 Seu pedido #{$order->id} foi confirmado! Total: R$ {$order->total}"
        );
    }
}
```

### Send Template Message

```php
// Send pre-approved template with parameters
$response = $converx->sendTemplate(
    phoneNumber: '11987654321',
    templateName: 'order_confirmation',
    parameters: [
        '1' => 'João Silva',      // {{1}}
        '2' => '12345',            // {{2}}
        '3' => 'R$ 149,90',        // {{3}}
        '4' => '3-5 dias úteis',   // {{4}}
    ]
);
```

### Get or Create Conversation

```php
// Automatically finds existing conversation or creates new one
$conversationId = $converx->getOrCreateConversation('11987654321');

// Send message to existing conversation
$response = $converx->sendMessageToConversation(
    conversationId: $conversationId,
    message: 'Follow-up message'
);
```

### Check Configuration

```php
if ($converx->isConfigured()) {
    // Send messages
} else {
    // API not configured
    Log::warning('Converx not configured');
}
```

## 📱 Phone Number Format

Converx uses **Brazilian phone format** with country code:

```
Correct:
5511987654321   (São Paulo mobile - 13 digits with 55)
5511033334444   (São Paulo landline - 12 digits with 55)
11987654321     (Auto-adds 55)
1133334444      (Auto-adds 55)

Format:
55 + DDD + Number
```

The package automatically:
- Removes non-numeric characters
- Adds country code `55` if missing
- Validates phone number length

## 🎨 Message Templates

### Creating Templates in Converx

1. Access Converx dashboard
2. Go to **Settings** → **Message Templates**
3. Create template with:
   - **Name**: `order_confirmation`
   - **Category**: UTILITY
   - **Language**: pt_BR
   - **Content**: Use `{{1}}`, `{{2}}`, etc for variables

### Template Example

**Name**: `order_confirmation`
**Content**:
```
Olá {{1}},

Seu pedido #{{2}} foi confirmado!
Valor: {{3}}
Previsão de entrega: {{4}}

Obrigado pela preferência!
```

**Usage**:
```php
$converx->sendTemplate(
    '11987654321',
    'order_confirmation',
    [
        '1' => 'João Silva',
        '2' => '12345',
        '3' => 'R$ 149,90',
        '4' => '3-5 dias úteis'
    ]
);
```

## 🔧 Advanced Features

### Manual Conversation Management

```php
// Search for existing contact
$contacts = $converx->get("/accounts/{$accountId}/contacts/search?q=5511987654321");

// Get contact's conversations
$contactId = $contacts['payload'][0]['id'];
$conversations = $converx->get("/accounts/{$accountId}/contacts/{$contactId}/conversations");

// Send message to specific conversation
$conversationId = $conversations['payload'][0]['id'];
$converx->sendMessageToConversation($conversationId, 'Hello!');
```

### Error Handling

```php
use Eduardoks98\WhatsAppConverx\Services\ConverxService;

try {
    $response = $converx->sendMessage('11987654321', 'Test');
} catch (\Exception $e) {
    // Common errors:
    // - Invalid API token
    // - Invalid phone number
    // - Conversation not found
    // - Inbox not configured

    Log::error('Converx error', [
        'error' => $e->getMessage()
    ]);
}
```

## 🌐 Converx Platform

Converx is based on [Chatwoot](https://www.chatwoot.com/), an open-source customer engagement platform.

**Features**:
- WhatsApp Business API integration
- Multi-agent support
- Chat history
- Automation rules
- Reports and analytics
- Brazilian support

## 📊 API Endpoints

Converx uses Chatwoot-compatible API:

```
Base URL: https://converx.app/api/v1

Contacts:
GET  /accounts/{id}/contacts/search?q={phone}
GET  /accounts/{id}/contacts/{contactId}/conversations

Conversations:
POST /accounts/{id}/conversations
GET  /accounts/{id}/conversations/{id}

Messages:
POST /accounts/{id}/conversations/{id}/messages
```

## 🔐 Security

**API Token**: Keep your API token secure. Never commit it to version control.

```env
# ✅ Good - Use environment variable
CONVERX_API_TOKEN=your-token

# ❌ Bad - Never hardcode
$converx = new ConverxService('8', 'my-token-here');
```

## 💡 Use Cases

### Customer Support

```php
// Auto-reply to common questions
$converx->sendMessage(
    $customerPhone,
    "Olá! Recebemos sua mensagem. Nossa equipe responderá em breve. Horário de atendimento: 9h-18h"
);
```

### Order Notifications

```php
// Notify customer about order status
$converx->sendTemplate(
    $customer->phone,
    'order_shipped',
    [
        '1' => $customer->name,
        '2' => $order->tracking_code,
        '3' => $order->estimated_delivery,
    ]
);
```

### Lead Notifications

```php
// Notify sales team about new lead
foreach ($salesTeam as $seller) {
    $converx->sendTemplate(
        $seller->phone,
        'new_lead_notification',
        [
            '1' => $lead->name,
            '2' => $lead->phone,
            '3' => $lead->interest,
        ]
    );
}
```

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

For Converx platform support: [https://converx.app](https://converx.app)

---

**Built with ❤️ for the Brazilian Laravel community**
