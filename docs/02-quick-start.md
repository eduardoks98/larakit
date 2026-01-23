# ⚡ Quick Start

Comece a usar o API Base em **menos de 5 minutos**!

## 🚀 Instalação Rápida

### Pré-requisitos
- PHP 8.1 ou 8.2+
- Composer 2.x
- Laravel 10, 11 ou 12

### 1. Criar Novo Projeto Laravel

```bash
laravel new my-api --git --pest
cd my-api
```

### 2. Adicionar Repositório

```bash
composer config repositories.api-base vcs git@github.com:eduardoks98/api-base-monorepo.git
```

### 3. Instalar Packages

**Opção A: Full Stack (Recomendado)**
```bash
composer require \
  eduardoks98/base-api \
  eduardoks98/helpers \
  eduardoks98/security \
  eduardoks98/auth
```

**Opção B: Minimal (Só API + Auth)**
```bash
composer require eduardoks98/base-api eduardoks98/auth
```

### 4. Publicar Configurações

```bash
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
php artisan migrate
```

### 5. Configurar .env

```env
# Sanctum
SANCTUM_ACCESS_TOKEN_EXPIRATION=15
SANCTUM_REFRESH_TOKEN_EXPIRATION=10080

# Rate Limiter (opcional)
RATE_LIMITER_MAX_ATTEMPTS=60
THROTTLE_MAX_ATTEMPTS=30
```

## 📝 Primeiro Endpoint

### 1. Criar Controller

```bash
php artisan make:controller Api/V1/UserController
```

```php
<?php

namespace App\Http\Controllers\Api\V1;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index()
    {
        $users = User::paginate(15);

        return apiResponse($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        beginTransaction();
        try {
            $user = User::create($validated);
            commit();

            return $this->created($user);
        } catch (\Throwable $e) {
            rollback();
            throw $e;
        }
    }
}
```

### 2. Criar Rotas

**routes/api/v1.php** (Laravel 12 style):

```php
<?php

use App\Http\Controllers\Api\V1\UserController;
use Eduardoks98\Auth\Http\Controllers\AuthController;

// Public
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::apiResource('users', UserController::class);
});
```

**Ou routes/api.php** (Laravel 10/11 style):

```php
Route::prefix('v1')->group(function () {
    // Suas rotas aqui
});
```

### 3. Testar

```bash
# Iniciar servidor
php artisan serve

# Testar endpoint
curl http://localhost:8000/api/v1/users
```

## 🔐 Autenticação

### Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin@example.com",
    "password": "password",
    "device_name": "curl"
  }'
```

**Response:**
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "expires_in": 900
}
```

### Usar Token

```bash
curl http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer 1|abc123..."
```

## 🎨 Response Format (RFC 7807)

### Success Response

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "code": 200
}
```

### Error Response (RFC 7807)

```json
{
  "type": "https://api.example.com/errors/validation-failed",
  "title": "Validation Failed",
  "status": 422,
  "detail": "The email field is required.",
  "instance": "/api/v1/users"
}
```

## 🇧🇷 Helpers Brasileiros

```php
use function Eduardoks98\Helpers\checkCPF;
use function Eduardoks98\Helpers\formatPhoneNumber;
use function Eduardoks98\Helpers\moneyFormat;

// Validar CPF
if (checkCPF('12345678909')) {
    echo "CPF válido!";
}

// Formatar telefone
$phone = formatPhoneNumber('11987654321');
// Output: (11) 98765-4321

// Formatar dinheiro
$price = moneyFormat(1234.56);
// Output: R$ 1.234,56
```

## 📊 Monitoring (Opcional)

### Instalar Telescope (Dev)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Acesse: `http://localhost:8000/telescope`

### Instalar Horizon (Queues)

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Acesse: `http://localhost:8000/horizon`

## 🧪 Testes com Pest

```bash
php artisan test
```

**Exemplo de teste** (tests/Feature/Api/V1/UserTest.php):

```php
<?php

use App\Models\User;
use function Pest\Laravel\{actingAs, getJson, postJson};

it('can list users', function () {
    $user = User::factory()->create();
    User::factory()->count(5)->create();

    $response = actingAs($user)->getJson('/api/v1/users');

    expect($response->status())->toBe(200)
        ->and($response->json('data'))->toHaveCount(6);
});

it('requires authentication', function () {
    $response = getJson('/api/v1/users');

    expect($response->status())->toBe(401);
});
```

## 🎯 Próximos Passos

1. **Adicionar mais packages**: [Instalação Completa →](./03-installation.md)
2. **Configurar segurança**: [Security Guide →](./guides/security.md)
3. **Otimizar performance**: [Performance Guide →](./guides/performance.md)
4. **Deploy em produção**: [Deployment Guide →](./guides/deployment.md)

## 🆘 Problemas?

- [Troubleshooting](./reference/troubleshooting.md)
- [GitHub Issues](https://github.com/eduardoks98/api-base-monorepo/issues)
- Email: eduardoks98@gmail.com

---

**Anterior**: [← Visão Geral](./01-overview.md) | **Próximo**: [Instalação Completa →](./03-installation.md)
