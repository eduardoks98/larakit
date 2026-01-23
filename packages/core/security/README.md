# 🔒 Security - OWASP Compliance Package

Security headers, CSP, IP blocking, e encryption para APIs Laravel.

## 📦 Instalação

```bash
composer require eduardoks98/security
php artisan vendor:publish --provider="Eduardoks98\Security\SecurityServiceProvider"
php artisan migrate
```

## 🚀 Features

- ✅ **Security Headers** - CSP, HSTS, X-Frame-Options, etc.
- ✅ **IP Blocking** - Database-driven com geolocation
- ✅ **Content Security Policy** - Configurável
- ✅ **OWASP Compliance** - API Security Top 10

## 📖 Documentação Completa

Veja a [documentação completa](../../docs/packages/security.md) para exemplos detalhados.

## 🔧 Quick Start

### 1. Aplicar Security Headers

```php
// No Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Eduardoks98\Security\Http\Middleware\SecurityHeaders::class,
        // ...
    ],
];
```

### 2. Bloquear IPs

```php
use function Eduardoks98\Security\banIp;

// Banir IP
banIp('192.168.1.100', 'Multiple failed login attempts');

// Verificar se está banido
if (isIpBanned($request->ip())) {
    // IP is banned
}
```

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
