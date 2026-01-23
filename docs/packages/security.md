# 🔒 Package: security

**Package Name**: `eduardoks98/security`
**Propósito**: Security headers, CSP, IP blocking, encryption - OWASP API Security Top 10 compliant

---

## 📋 Visão Geral

O package `security` fornece ferramentas de segurança essenciais para APIs REST:

- **Security Headers** - CSP, HSTS, X-Frame-Options, etc.
- **IP Blocking** - Bloqueio de IPs com geolocation
- **CORS** - Configuração de CORS simplificada
- **Encryption** - JWE encryption/decryption

**Compliance**: OWASP API Security Top 10 2023

---

## 🎯 Quando Usar

✅ **Use este package quando**:
- Você precisa de security headers automáticos
- Você quer bloquear IPs maliciosos
- Você precisa configurar CORS
- Você precisa criptografar dados sensíveis

---

## 📦 Instalação

```bash
composer require eduardoks98/security
```

### Publicar Configuração e Migrations

```bash
php artisan vendor:publish --provider="Eduardoks98\Security\SecurityServiceProvider"
php artisan migrate
```

Isso criará:
- `config/security.php` - Configurações
- Migration: `banned_ips` table

---

## ⚙️ Configuração

### Arquivo de Configuração

```php
return [
    // Content Security Policy
    'csp' => [
        'enabled' => true,
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "https://www.google.com"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", "data:", "https:"],
            'font-src' => ["'self'", "data:"],
            'connect-src' => ["'self'"],
        ],
    ],

    // Security Headers
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    // HSTS
    'hsts' => [
        'enabled' => true,
        'max_age' => 31536000, // 1 year
        'include_subdomains' => true,
    ],

    // IP Blocking
    'ip_blocking' => [
        'enabled' => true,
        'whitelist_enabled' => true,
        'geolocation_enabled' => true,
    ],
];
```

---

## 🚀 Uso

### 1. Security Headers Middleware

#### Registrar no Kernel

```php
protected $middlewareGroups = [
    'api' => [
        \Eduardoks98\Security\Http\Middleware\SecurityHeaders::class,
        // ...
    ],
];
```

#### Headers Aplicados Automaticamente

```
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'...
```

### 2. IP Blocking

#### Ban IP Programaticamente

```php
use Eduardoks98\Security\Services\IpBlockingService;

$service = app(IpBlockingService::class);

// Banir IP
$service->banIp('192.168.1.100', 'Multiple failed login attempts');

// Desbanir IP
$service->unbanIp('192.168.1.100');

// Verificar se IP está banido
$isBanned = $service->isBanned('192.168.1.100');

// Verificar se IP está na whitelist
$isWhitelisted = $service->isWhitelisted('192.168.1.100');
```

#### Middleware BannedIP

```php
// Em routes/api.php
Route::middleware(['banned.ip'])->group(function () {
    // Suas rotas protegidas
});
```

#### Ban com Geolocation

```php
use Eduardoks98\Security\Models\BannedIp;

$bannedIp = BannedIp::create([
    'ip_address' => '192.168.1.100',
    'user_agent' => request()->userAgent(),
    'country' => 'BR',
    'city' => 'São Paulo',
    'latitude' => -23.5505,
    'longitude' => -46.6333,
]);
```

### 3. CORS Middleware

#### Configurar CORS

```php
// config/cors.php (Laravel padrão)
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://example.com'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 4. Encryption Service (JWE)

#### Criptografar Dados

```php
use Eduardoks98\Security\Services\EncryptionService;

$service = app(EncryptionService::class);

$encrypted = $service->encrypt([
    'credit_card' => '1234 5678 9012 3456',
    'cvv' => '123',
]);

// Armazenar $encrypted no banco
```

#### Descriptografar Dados

```php
$decrypted = $service->decrypt($encrypted);

echo $decrypted['credit_card']; // "1234 5678 9012 3456"
```

---

## 📝 Exemplos Completos

### Exemplo 1: Proteger Endpoint de Login

```php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['banned.ip', 'throttle:5,1']);
```

### Exemplo 2: Banir IP Após Múltiplas Tentativas

```php
namespace App\Http\Controllers\Api;

use Eduardoks98\Security\Services\IpBlockingService;

class AuthController extends ApiController
{
    public function login(Request $request, IpBlockingService $ipService)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            // Incrementar contador de falhas
            $failures = Cache::increment("login_failures:{$request->ip()}");

            if ($failures >= 5) {
                $ipService->banIp($request->ip(), 'Exceeded login attempts');

                return problemDetails(
                    type: 'https://api.example.com/errors/ip-banned',
                    title: 'IP Banned',
                    status: 403,
                    detail: 'Your IP has been banned due to multiple failed login attempts'
                );
            }

            return problemDetails(
                type: 'https://api.example.com/errors/invalid-credentials',
                title: 'Invalid Credentials',
                status: 401,
                detail: 'Invalid email or password'
            );
        }

        // Limpar contador de falhas
        Cache::forget("login_failures:{$request->ip()}");

        // ... restante do login
    }
}
```

### Exemplo 3: Criptografar Dados Sensíveis no Banco

```php
namespace App\Models;

use Eduardoks98\Security\Services\EncryptionService;

class CreditCard extends Model
{
    protected $fillable = ['user_id', 'encrypted_data'];

    public function setCardDataAttribute($value)
    {
        $service = app(EncryptionService::class);
        $this->attributes['encrypted_data'] = $service->encrypt($value);
    }

    public function getCardDataAttribute()
    {
        $service = app(EncryptionService::class);
        return $service->decrypt($this->encrypted_data);
    }
}
```

---

## 📚 API Reference

### Global Helpers

| Função | Descrição |
|--------|-----------|
| `banIpOnSSH($ip)` | Ban IP no fail2ban (SSH) |
| `unbanIpOnSSH($ip)` | Unban IP no fail2ban |
| `getBannedIPs()` | Lista IPs banidos |

### Services

| Service | Métodos |
|---------|---------|
| `IpBlockingService` | `banIp()`, `unbanIp()`, `isBanned()`, `isWhitelisted()` |
| `EncryptionService` | `encrypt()`, `decrypt()` |

### Models

| Model | Descrição |
|-------|-----------|
| `BannedIp` | Eloquent model para IPs banidos |

---

## 🔐 OWASP Compliance

### API8:2023 - Security Misconfiguration

✅ Security headers automáticos
✅ CSP configurável
✅ HSTS habilitado
✅ Error handling seguro

---

## ⚠️ Troubleshooting

### CSP bloqueando recursos

**Problema**: Content Security Policy está bloqueando scripts/estilos.

**Solução**: Adicione as origens permitidas em `config/security.php`:

```php
'script-src' => ["'self'", "https://trusted-cdn.com"],
```

---

## 🔗 Dependências

```json
{
  "eduardoks98/base-api": "^1.0",
  "spomky-labs/aes-key-wrap": "^7.0",
  "web-token/jwt-framework": "^4.1"
}
```

---

**Anterior**: [← Helpers](./helpers.md) | **Próximo**: [Rate Limiter →](./rate-limiter.md)
