# 🔐 Package: auth

**Package Name**: `eduardoks98/auth`
**Propósito**: Laravel Sanctum com token abilities, device management, 2FA e LDAP opcional

---

## 📋 Visão Geral

Autenticação moderna para REST APIs com:

- **Laravel Sanctum** - Token-based authentication
- **Token Abilities** - Permissions granulares por token
- **Device Management** - Tracking de dispositivos
- **Refresh Tokens** - Access + Refresh token pattern
- **2FA** - Two-Factor Authentication (opcional)
- **LDAP** - Active Directory integration (opcional)

**Compliance**: OWASP API2:2023 - Broken Authentication

---

## 🎯 Authentication Flow

```
1. Login → Access Token (15 min) + Refresh Token (7 days)
2. API Calls → Header: Authorization: Bearer {access_token}
3. Token Expired → Refresh → New Access Token
4. Logout → Revoke Tokens
```

---

## 📦 Instalação

```bash
composer require eduardoks98/auth
php artisan vendor:publish --provider="Eduardoks98\Auth\AuthServiceProvider"
php artisan migrate
```

Tables criadas/modificadas:
- `user_sessions`
- `personal_access_tokens` (colunas adicionadas)

---

## ⚙️ Configuração

```php
return [
    'sanctum' => [
        'access_token_expiration' => env('SANCTUM_ACCESS_TOKEN_EXPIRATION', 15), // minutes
        'refresh_token_expiration' => env('SANCTUM_REFRESH_TOKEN_EXPIRATION', 10080), // 7 days
        'device_id_enabled' => true,
    ],

    'jwt' => [
        'enabled' => false, // Opcional
        'algorithm' => 'RS256',
        'ttl' => 60,
    ],

    'ldap' => [
        'enabled' => env('LDAP_ENABLED', false),
        'host' => env('LDAP_HOST'),
        'port' => env('LDAP_PORT', 389),
        'base_dn' => env('LDAP_BASE_DN'),
        'username' => env('LDAP_USERNAME'),
        'password' => env('LDAP_PASSWORD'),
    ],

    '2fa' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'issuer' => env('APP_NAME', 'Larakit'),
    ],
];
```

### .env

```env
SANCTUM_ACCESS_TOKEN_EXPIRATION=15
SANCTUM_REFRESH_TOKEN_EXPIRATION=10080
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

---

## 🚀 Uso

### 1. Login

```php
use Eduardoks98\Auth\Http\Controllers\AuthController;

// Em routes/api.php
Route::post('/auth/login', [AuthController::class, 'login']);
```

**Request**:
```json
POST /api/v1/auth/login
{
  "username": "user@example.com",
  "password": "secret",
  "device_name": "iPhone 14 Pro"
}
```

**Response**:
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "expires_in": 900,
  "refresh_token": "2|xyz789...",
  "refresh_expires_in": 604800
}
```

### 2. Protected Routes

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
});
```

### 3. Token Refresh

```php
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
```

**Request**:
```json
POST /api/v1/auth/refresh
{
  "refresh_token": "2|xyz789..."
}
```

**Response**:
```json
{
  "access_token": "3|def456...",
  "expires_in": 900
}
```

### 4. Logout

```php
// Logout do dispositivo atual
POST /api/v1/auth/logout

// Logout de todos os dispositivos
POST /api/v1/auth/logout?all=true
```

### 5. Token Abilities (Permissions)

#### Criar Token com Abilities

```php
$token = $user->createToken('api-token', [
    'users:read',
    'users:create',
    'posts:read',
])->plainTextToken;
```

#### Middleware CheckTokenAbilities

```php
Route::middleware(['auth:sanctum', 'abilities:users:edit,users:delete'])->group(function () {
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

#### Verificar Abilities no Controller

```php
if ($request->user()->tokenCan('users:delete')) {
    // Permitido
}
```

### 6. Device Management

```php
use Eduardoks98\Auth\Models\UserSession;

// Listar dispositivos do usuário
$devices = UserSession::where('user_id', $user->id)->get();

// Revogar dispositivo específico
UserSession::where('device_id', $deviceId)->delete();
$user->tokens()->where('device_id', $deviceId)->delete();
```

### 7. Two-Factor Authentication (2FA)

```php
use Eduardoks98\Auth\Services\TwoFactorService;

$service = app(TwoFactorService::class);

// Gerar QR code
$qrCode = $service->generateQrCode($user);

// Validar código
$isValid = $service->validateCode($user, $request->code);
```

### 8. LDAP Authentication

```php
use Eduardoks98\Auth\Services\LdapService;

$service = app(LdapService::class);

$user = $service->authenticate($username, $password);

if ($user) {
    // Sincronizar com banco local
    $localUser = User::updateOrCreate(
        ['email' => $user['email']],
        ['name' => $user['name']]
    );
}
```

---

## 📝 Exemplos

### Exemplo 1: Login Completo com Device Tracking

```php
namespace App\Http\Controllers\Api;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Auth;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return problemDetails(
                type: 'https://api.example.com/errors/invalid-credentials',
                title: 'Invalid Credentials',
                status: 401,
                detail: 'The provided credentials are incorrect'
            );
        }

        $user = Auth::user();
        $deviceId = hash('sha256', $request->device_name . $request->ip());

        // Access token (15 min)
        $accessToken = $user->createToken('access-token', ['*'], now()->addMinutes(15));
        $accessToken->accessToken->update([
            'type' => 'access',
            'device_id' => $deviceId,
        ]);

        // Refresh token (7 days)
        $refreshToken = $user->createToken('refresh-token', ['refresh'], now()->addDays(7));
        $refreshToken->accessToken->update([
            'type' => 'refresh',
            'device_id' => $deviceId,
        ]);

        // Registrar sessão
        \Eduardoks98\Auth\Models\UserSession::create([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_id' => $deviceId,
            'last_activity' => now(),
        ]);

        return [
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 900,
            'refresh_token' => $refreshToken->plainTextToken,
            'refresh_expires_in' => 604800,
        ];
    }
}
```

### Exemplo 2: CRUD com Token Abilities

```php
// Criar usuário com diferentes abilities
$admin = User::find(1);
$viewer = User::find(2);

$adminToken = $admin->createToken('admin-token', [
    'users:read', 'users:create', 'users:edit', 'users:delete'
])->plainTextToken;

$viewerToken = $viewer->createToken('viewer-token', [
    'users:read'
])->plainTextToken;

// No Controller
public function update(Request $request, $id)
{
    if (!$request->user()->tokenCan('users:edit')) {
        return problemDetails(
            type: 'https://api.example.com/errors/forbidden',
            title: 'Forbidden',
            status: 403,
            detail: 'You do not have permission to edit users'
        );
    }

    // Continuar com update
}
```

---

## 📚 API Reference

### Controllers

| Controller | Endpoints |
|------------|-----------|
| `AuthController` | `login()`, `logout()`, `refresh()`, `me()` |

### Middleware

| Middleware | Descrição |
|------------|-----------|
| `auth:sanctum` | Valida Sanctum token |
| `abilities:perm1,perm2` | Verifica abilities |

### Services

| Service | Métodos |
|---------|---------|
| `TwoFactorService` | `generateQrCode()`, `validateCode()` |
| `LdapService` | `authenticate()`, `syncUser()` |
| `JwtService` | `generate()`, `validate()` |

### Models

| Model | Descrição |
|-------|-----------|
| `UserSession` | Sessões por dispositivo |

---

## ⚠️ Troubleshooting

### Token expirando muito rápido

**Solução**: Ajustar expiração:

```env
SANCTUM_ACCESS_TOKEN_EXPIRATION=60  # 1 hour
```

### LDAP connection refused

**Solução**: Verificar firewall e porta:

```bash
telnet ldap.example.com 389
```

---

## 🔗 Dependências

```json
{
  "laravel/sanctum": "^3.0|^4.0",
  "web-token/jwt-framework": "^4.1",
  "eduardoks98/base-api": "^1.0",
  "eduardoks98/security": "^1.0"
}
```

---

**Anterior**: [← Recaptcha](./recaptcha.md) | **Próximo**: [Performance →](./performance.md)
