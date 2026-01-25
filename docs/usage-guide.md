# 🚀 Guia de Uso - Larakit

## Como Usar os Packages Localmente

### Opção 1: Uso Local (Desenvolvimento)

Perfeito para testar antes de publicar no GitHub.

#### 1. Crie um novo projeto Laravel

```bash
laravel new meu-projeto-api
cd meu-projeto-api
```

#### 2. Adicione o monorepo como repositório local

Edite o `composer.json` do seu projeto:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../larakit/packages/*",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "php": "^8.1",
        "laravel/framework": "^11.0",
        "eduardoks98/base-api": "@dev",
        "eduardoks98/helpers": "@dev",
        "eduardoks98/security": "@dev",
        "eduardoks98/rate-limiter": "@dev",
        "eduardoks98/recaptcha": "@dev",
        "eduardoks98/auth": "@dev",
        "eduardoks98/performance": "@dev",
        "eduardoks98/health": "@dev"
    }
}
```

#### 3. Instale os packages

```bash
composer update
```

#### 4. Publique configurações e migrations

```bash
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=migrations
php artisan migrate
```

#### 5. Configure o .env

Copie as configurações do `E:\larakit\.env.example` para o `.env` do seu projeto.

---

## Opção 2: Uso Após Publicação no GitHub

Depois de fazer o push para o GitHub:

```bash
# 1. Adicione o repositório
composer config repositories.larakit vcs https://github.com/eduardoks98/larakit

# 2. Instale os packages
composer require eduardoks98/base-api eduardoks98/auth eduardoks98/helpers
```

---

## 📝 Exemplos Práticos

### Exemplo 1: API REST Simples com Autenticação

#### 1. Crie um Controller

```bash
php artisan make:controller Api/UserController
```

```php
<?php

namespace App\Http\Controllers\Api;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function index()
    {
        $users = User::paginate(15);
        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'cpf' => 'required|string',
        ]);

        // Validar CPF usando helper
        if (!checkCPF($request->cpf)) {
            return problemDetails(
                'https://api.example.com/errors/invalid-cpf',
                'CPF Inválido',
                422,
                'O CPF fornecido não é válido',
                $request->path()
            );
        }

        $user = User::create($request->all());
        return $this->created(new UserResource($user));
    }
}
```

#### 2. Crie um Resource

```bash
php artisan make:resource UserResource
```

```php
<?php

namespace App\Http\Resources;

use Eduardoks98\BaseApi\Http\Resources\ApiResource;

class UserResource extends ApiResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'cpf' => formatarCpfCnpj($this->cpf, 11),
            'phone' => formatPhoneNumber($this->phone),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

#### 3. Configure as Rotas

```php
// routes/api.php
use App\Http\Controllers\Api\UserController;
use Eduardoks98\Auth\Http\Controllers\AuthController;

// Rotas públicas
Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware(['throttle.login:5,15', 'recaptcha:login,login']);

Route::post('auth/refresh', [AuthController::class, 'refresh']);

// Rotas protegidas
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // CRUD de usuários
    Route::apiResource('users', UserController::class);
});

// Com permissões específicas
Route::delete('users/{user}', [UserController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'abilities:users:delete']);
```

#### 4. Aplique Middlewares Globais

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Eduardoks98\Security\Http\Middleware\SecurityHeaders::class,
        \Eduardoks98\BaseApi\Http\Middleware\ForceJsonResponse::class,
        \Eduardoks98\BaseApi\Http\Middleware\SetApiHeaders::class,
        \Eduardoks98\RateLimiter\Http\Middleware\GenericThrottle::class,
        \Eduardoks98\Performance\Http\Middleware\PerformanceMonitor::class,
    ],
];
```

---

### Exemplo 2: Login com reCAPTCHA e Device Management

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 14 Pro",
    "recaptcha_token": "03AGdBq24..."
  }'
```

Resposta:
```json
{
  "access_token": "1|abc123...",
  "refresh_token": "2|xyz789...",
  "token_type": "Bearer",
  "expires_in": 900,
  "refresh_expires_in": 604800,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

---

### Exemplo 3: Usando Helpers Brasileiros

```php
use function Eduardoks98\Helpers\{
    checkCPF,
    checkCNPJ,
    formatPhoneNumber,
    moneyFormat,
    removerCaracteres
};

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

// Limpar caracteres
$cpfLimpo = removerCaracteres('123.456.789-09');
// Output: 12345678909
```

---

### Exemplo 4: Rate Limiting e IP Blocking

```php
// Whitelist um IP
use function Eduardoks98\RateLimiter\whitelistIp;

whitelistIp('192.168.1.100', 'IP do escritório');
whitelistCidr('10.0.0.0/8', 'Rede interna');

// Banir IP manualmente
use function Eduardoks98\Security\banIp;

banIp('1.2.3.4', 'Tentativas de invasão detectadas');

// Verificar se IP está banido
if (isIpBanned($request->ip())) {
    // IP está banido
}

// Ver logs de requisições
$logs = getApiRequestLogs(['ip' => '1.2.3.4', 'failed' => true]);
```

---

### Exemplo 5: Health Checks para Kubernetes

```yaml
# kubernetes/deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: api-deployment
spec:
  template:
    spec:
      containers:
      - name: api
        image: my-api:latest
        ports:
        - containerPort: 8000
        livenessProbe:
          httpGet:
            path: /health
            port: 8000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /health/full
            port: 8000
          initialDelaySeconds: 5
          periodSeconds: 5
```

---

### Exemplo 6: Performance Monitoring

```php
// Ver requisições lentas
use function Eduardoks98\Performance\getSlowRequests;

$slowRequests = getSlowRequests(100);

// Estatísticas de performance
$stats = getPerformanceStats(7); // últimos 7 dias

// Habilitar detecção de N+1
preventN1Query(); // Já habilitado automaticamente em dev
```

---

### Exemplo 7: API Documentation (Scramble)

Acesse automaticamente:
- **Documentação interativa**: `http://localhost:8000/docs/api`
- **OpenAPI JSON**: `http://localhost:8000/docs/api.json`

Customizar documentação:

```php
// app/Providers/AppServiceProvider.php
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;

public function boot()
{
    Scramble::extendOpenApi(function (OpenApi $openApi) {
        $openApi->info->title = 'Minha API';
        $openApi->info->version = '1.0.0';
        $openApi->info->description = 'API REST com eduardoks98 packages';
    });
}
```

---

## 🔧 Comandos Úteis

### Desenvolvimento

```bash
# Iniciar servidor
php artisan serve

# Iniciar WebSocket (Reverb)
php artisan reverb:start

# Iniciar worker de filas
php artisan queue:work

# Limpar rate limits de um IP
php artisan tinker
>>> clearRateLimitForIp('192.168.1.100');

# Limpar tokens expirados
>>> cleanupExpiredTokens();

# Limpar sessões antigas
>>> cleanupOldSessions();
```

### Monitoramento

```bash
# Telescope (desenvolvimento)
http://localhost:8000/telescope

# Pulse (produção)
http://localhost:8000/pulse

# Horizon (filas)
http://localhost:8000/horizon

# Health checks
http://localhost:8000/health
http://localhost:8000/health/full
```

---

## 📱 Frontend Integration

### Laravel Echo (WebSocket)

```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: true,
});

// Escutar notificações
Echo.private(`notifications.${userId}`)
    .listen('NotificationSent', (e) => {
        console.log('Nova notificação:', e.message);
    });
```

### reCAPTCHA v3

```html
<!-- resources/views/auth/login.blade.php -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.v3_site_key') }}"></script>

<script>
async function login() {
    const token = await grecaptcha.execute('{{ config('recaptcha.v3_site_key') }}', {
        action: 'login'
    });

    const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: document.getElementById('email').value,
            password: document.getElementById('password').value,
            device_name: navigator.userAgent,
            recaptcha_token: token
        })
    });

    const data = await response.json();
    localStorage.setItem('access_token', data.access_token);
    localStorage.setItem('refresh_token', data.refresh_token);
}
</script>
```

---

## 🐛 Troubleshooting

### Erro: "Class not found"

```bash
# Limpe o cache
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Erro: "Migration already exists"

```bash
# Se já rodou as migrations antes
php artisan migrate:fresh
```

### Erro: "Token expired"

```bash
# Use o refresh token
curl -X POST http://localhost:8000/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refresh_token": "2|xyz789..."}'
```

### Erro: "Rate limit exceeded"

```php
// Limpe o rate limit manualmente
use function Eduardoks98\RateLimiter\clearRateLimitForIp;
clearRateLimitForIp($request->ip());
```

---

## 📚 Recursos Adicionais

- 📖 **Documentação completa**: [docs/README.md](./docs/README.md)
- 🚀 **Quick Start**: [docs/02-quick-start.md](./docs/02-quick-start.md)
- ⚙️ **Instalação**: [docs/03-installation.md](./docs/03-installation.md)
- 📦 **Packages**: [docs/packages/](./docs/packages/)

---

## 💡 Dicas

1. **Sempre use helpers globais** - São mais convenientes que instanciar classes
2. **Configure .env corretamente** - Cada package tem suas variáveis
3. **Use middlewares em grupo** - Aplique na API middleware group
4. **Monitore performance** - Use Pulse em produção
5. **Teste localmente primeiro** - Use symlinks antes de publicar

---

**Precisando de ajuda?** Veja [CONTRIBUTING.md](./CONTRIBUTING.md) ou abra uma [issue](https://github.com/eduardoks98/larakit/issues)
