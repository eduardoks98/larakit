# 🔄 Package: reverb

**Package Name**: `eduardoks98/reverb`
**Propósito**: Laravel Reverb wrapper para WebSocket real-time

---

## 📋 Visão Geral

WebSocket real-time com Laravel Reverb:

- **Laravel Reverb** - WebSocket server nativo Laravel 11+
- **Private Channels** - Canais privados com autorização
- **Presence Channels** - Rastreamento de usuários online
- **Broadcasting** - Event broadcasting simplificado
- **Laravel Echo** - Cliente JavaScript pronto

---

## 📦 Instalação

```bash
composer require eduardoks98/reverb
php artisan vendor:publish --provider="Eduardoks98\Reverb\ReverbServiceProvider"
php artisan reverb:install
```

---

## ⚙️ Configuração

### .env

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### config/reverb.php

```php
return [
    'servers' => [
        [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_SERVER_HOSTNAME', 'localhost'),
            'max_request_size' => 10000,
        ],
    ],
];
```

---

## 🚀 Uso

### 1. Iniciar Server

```bash
php artisan reverb:start
```

### 2. Criar Event

```php
namespace App\Events;

use Illuminate\Broadcasting\{Channel, PrivateChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationSent implements ShouldBroadcast
{
    public function __construct(
        public User $user,
        public string $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }
}
```

### 3. Disparar Event

```php
use App\Events\NotificationSent;

broadcast(new NotificationSent($user, 'Hello!'));
```

### 4. Autorizar Canais

```php
// routes/channels.php
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    return $user->canAccessRoom($roomId);
});
```

### 5. Frontend (Laravel Echo)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Listen to private channel
Echo.private(`notifications.${userId}`)
    .listen('.notification.sent', (e) => {
        console.log('Notification:', e.message);
    });

// Presence channel
Echo.join(`chat.${roomId}`)
    .here((users) => {
        console.log('Users here:', users);
    })
    .joining((user) => {
        console.log('User joined:', user.name);
    })
    .leaving((user) => {
        console.log('User left:', user.name);
    });
```

---

## 📝 Exemplos

### Exemplo 1: Chat em Tempo Real

```php
// Event
namespace App\Events;

class MessageSent implements ShouldBroadcast
{
    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->room_id);
    }
}

// Controller
public function sendMessage(Request $request)
{
    $message = Message::create([
        'room_id' => $request->room_id,
        'user_id' => $request->user()->id,
        'content' => $request->content,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return new MessageResource($message);
}

// Frontend
Echo.private(`chat.${roomId}`)
    .listen('MessageSent', (e) => {
        addMessageToChat(e.message);
    });
```

### Exemplo 2: Notificações em Tempo Real

```php
// Notification
namespace App\Notifications;

class OrderShipped extends Notification implements ShouldBroadcast
{
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Order Shipped',
            'message' => "Your order #{$this->order->id} has been shipped",
        ]);
    }
}

// Controller
$user->notify(new OrderShipped($order));

// Frontend
Echo.private(`App.Models.User.${userId}`)
    .notification((notification) => {
        showNotification(notification);
    });
```

---

## 🔗 Dependências

```json
{
  "laravel/reverb": "^1.0",
  "eduardoks98/base-api": "^1.0"
}
```

---

**Anterior**: [← Performance](./performance.md) | **Próximo**: [API Docs →](./api-docs.md)
