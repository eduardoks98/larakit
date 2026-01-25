# 📦 Instalação Completa

Guia completo para instalar todos os packages do Larakit.

## 🎯 Cenários de Instalação

### Cenário 1: API REST Completa (Recomendado)

**Quando usar**: Novo projeto API REST com autenticação, segurança, monitoring.

```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/helpers \
  eduardoks98/security \
  eduardoks98/rate-limiter \
  eduardoks98/recaptcha \
  eduardoks98/auth \
  eduardoks98/performance \
  eduardoks98/api-docs \
  eduardoks98/health
```

### Cenário 2: API Mínima

**Quando usar**: MVP, prototipagem rápida, projeto pequeno.

```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/auth \
  eduardoks98/helpers
```

### Cenário 3: API com Real-time

**Quando usar**: Chat, notificações live, dashboards real-time.

```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/auth \
  eduardoks98/reverb
```

### Cenário 4: Microserviço

**Quando usar**: Parte de arquitetura de microserviços.

```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/health \
  eduardoks98/performance
```

## 📋 Pré-requisitos

### Requisitos Mínimos

| Requisito | Versão |
|-----------|--------|
| PHP | 8.1, 8.2 ou 8.3 |
| Composer | 2.x |
| Laravel | 10.x, 11.x ou 12.x |
| MySQL/MariaDB | 5.7+ / 10.3+ |
| Redis | 6.0+ (recomendado) |

### Extensões PHP Requeridas

```bash
php -m | grep -E "pdo|openssl|mbstring|tokenizer|xml|ctype|json|bcmath"
```

Todas devem estar habilitadas.

### Verificar Ambiente

```bash
# PHP version
php -v

# Composer version
composer --version

# Laravel version
php artisan --version

# Redis disponível
redis-cli ping
```

## 🔧 Instalação Passo a Passo

### 1. Preparar Projeto Laravel

**Novo projeto**:
```bash
laravel new my-api --git --pest
cd my-api
```

**Projeto existente**:
```bash
cd existing-project
```

### 2. Configurar Repositório

**Via SSH (Recomendado)**:
```bash
composer config repositories.larakit vcs git@github.com:eduardoks98/larakit.git
```

**Via HTTPS**:
```bash
composer config repositories.larakit vcs https://github.com/eduardoks98/larakit.git
```

### 3. Instalar Packages

**Instalação completa**:
```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/helpers \
  eduardoks98/security \
  eduardoks98/rate-limiter \
  eduardoks98/recaptcha \
  eduardoks98/auth \
  eduardoks98/performance \
  eduardoks98/reverb \
  eduardoks98/api-docs \
  eduardoks98/health
```

**Instalação com versões específicas**:
```bash
composer require eduardoks98/base-api:^1.0
```

### 4. Publicar Assets

**Publicar tudo**:
```bash
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
php artisan vendor:publish --tag=views  # Se houver
```

**Publicar por package**:
```bash
# Base API
php artisan vendor:publish --provider="Eduardoks98\BaseApi\BaseApiServiceProvider"

# Security
php artisan vendor:publish --provider="Eduardoks98\Security\SecurityServiceProvider"

# Auth
php artisan vendor:publish --provider="Eduardoks98\Auth\AuthServiceProvider"
```

### 5. Executar Migrations

```bash
# Ver migrations pendentes
php artisan migrate:status

# Executar migrations
php artisan migrate

# Ou com seed (se houver)
php artisan migrate --seed
```

**Migrations criadas**:
- `banned_ips` (security)
- `api_request_logs` (rate-limiter)
- `api_request_stats` (rate-limiter)
- `ip_whitelist` (rate-limiter)
- `recaptcha_logs` (recaptcha)
- `user_sessions` (auth)
- `performance_logs` (performance)
- `personal_access_tokens` (modificada - auth)

### 6. Configurar Environment

**Copiar template**:
```bash
cp .env.example .env.production
```

**Editar .env**:
```env
# Application
APP_NAME="My API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_api
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SANCTUM_ACCESS_TOKEN_EXPIRATION=15
SANCTUM_REFRESH_TOKEN_EXPIRATION=10080

# Rate Limiter
RATE_LIMITER_ENABLED=true
RATE_LIMITER_TIME_DECAY_MINUTES=1
RATE_LIMITER_MAX_ATTEMPTS=60
THROTTLE_MAX_ATTEMPTS=30
MAX_ATTEMPTS_BEFORE_BAN_IP=100

# reCAPTCHA (opcional)
RECAPTCHA_V3_SECRET=
RECAPTCHA_V3_SITE_KEY=
RECAPTCHA_THRESHOLD=0.5

# Security
SECURITY_CSP_ENABLED=true
SECURITY_IP_BLOCKING_ENABLED=true

# Performance Monitoring
PERFORMANCE_MONITORING_ENABLED=true
SLOW_REQUEST_THRESHOLD=1000

# Laravel Reverb (opcional)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080

# Monitoring
TELESCOPE_ENABLED=true  # Dev only
PULSE_ENABLED=false     # Production only
```

### 7. Otimizar Autoloader

**Desenvolvimento**:
```bash
composer dump-autoload
```

**Produção**:
```bash
composer dump-autoload --optimize --classmap-authoritative
```

### 8. Cachear Configurações (Produção)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Limpar cache (desenvolvimento)**:
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## ✅ Verificação da Instalação

### 1. Verificar Packages Instalados

```bash
composer show | grep eduardoks98
```

**Esperado**:
```
eduardoks98/api-docs       1.0.0  Scramble OpenAPI documentation
eduardoks98/auth           1.0.0  Sanctum + JWT authentication
eduardoks98/base-api       1.0.0  Foundation package
eduardoks98/health         1.0.0  Health check endpoints
eduardoks98/helpers        1.0.0  Brazilian utilities
eduardoks98/performance    1.0.0  Performance monitoring
eduardoks98/rate-limiter   1.0.0  3-tier rate limiting
eduardoks98/recaptcha      1.0.0  Smart reCAPTCHA validation
eduardoks98/reverb         1.0.0  WebSocket wrapper
eduardoks98/security       1.0.0  Security headers & IP blocking
```

### 2. Verificar Configurações Publicadas

```bash
ls -la config/ | grep -E "(base-api|security|rate-limiter|recaptcha|auth|helpers|performance|reverb|health)"
```

### 3. Verificar Migrations

```bash
php artisan migrate:status
```

### 4. Testar Helper Functions

```bash
php artisan tinker
```

```php
>>> use function Eduardoks98\Helpers\checkCPF;
>>> checkCPF('12345678909')
=> true

>>> use function Eduardoks98\Helpers\formatPhoneNumber;
>>> formatPhoneNumber('11987654321')
=> "(11) 98765-4321"

>>> use function Eduardoks98\Helpers\moneyFormat;
>>> moneyFormat(1234.56)
=> "R$ 1.234,56"
```

### 5. Testar Endpoints

```bash
# Health check
curl http://localhost:8000/health

# API Docs
curl http://localhost:8000/docs/api.json
```

### 6. Executar Testes

```bash
php artisan test
```

## 🔄 Atualização de Packages

### Atualizar para Última Versão

```bash
composer update eduardoks98/*
```

### Atualizar Package Específico

```bash
composer update eduardoks98/base-api
```

### Ver Versões Disponíveis

```bash
composer show eduardoks98/base-api --all
```

## 🐛 Troubleshooting

### Erro: "Class not found"

**Solução**:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Erro: "Migration already exists"

**Solução**:
```bash
# Verificar migrations duplicadas
ls -la database/migrations/

# Deletar duplicadas manualmente ou
php artisan migrate:fresh  # ⚠️ CUIDADO: Apaga tudo!
```

### Erro: Redis connection refused

**Solução**:
```bash
# Verificar se Redis está rodando
redis-cli ping

# Iniciar Redis
# Linux/Mac
redis-server

# Docker
docker run -d -p 6379:6379 redis:alpine
```

### Erro: Composer memory limit

**Solução**:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer require eduardoks98/base-api
```

## 📚 Próximos Passos

Agora que a instalação está completa:

1. [Configurar Autenticação](./packages/auth.md)
2. [Configurar Segurança](./guides/security.md)
3. [Criar Primeiro Endpoint](./02-quick-start.md#primeiro-endpoint)
4. [Configurar Testes](./guides/testing.md)
5. [Deploy em Produção](./guides/deployment.md)

---

**Anterior**: [← Quick Start](./02-quick-start.md) | **Próximo**: [Packages →](./packages/)
