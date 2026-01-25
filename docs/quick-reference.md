# ⚡ Como Usar - Guia Rápido Visual

## 🎯 Opção 1: Uso Local (Recomendado para Testar)

```
┌─────────────────────────────────────────────────────────────┐
│  📁 Estrutura de Diretórios                                 │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  📂 E:\                                                      │
│  ├── 📁 larakit/              ← Seu monorepo (este)       │
│  │   └── 📁 packages/                                       │
│  │       ├── 📦 base-api/                                   │
│  │       ├── 📦 helpers/                                    │
│  │       └── ...                                            │
│  │                                                           │
│  └── 📁 meu-projeto/           ← Novo projeto Laravel       │
│      └── 📄 composer.json                                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Passo 1: Crie um Projeto Laravel

```bash
┌─────────────────────────────────────┐
│  Terminal                           │
├─────────────────────────────────────┤
│ $ cd E:\                            │
│ $ laravel new meu-projeto           │
│ $ cd meu-projeto                    │
└─────────────────────────────────────┘
```

### Passo 2: Configure o composer.json

```json
// E:\meu-projeto\composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "../larakit/packages/*",
            "options": {"symlink": true}
        }
    ],
    "require": {
        "eduardoks98/base-api": "@dev",
        "eduardoks98/helpers": "@dev",
        "eduardoks98/auth": "@dev"
    }
}
```

### Passo 3: Instale

```bash
┌─────────────────────────────────────┐
│  Terminal                           │
├─────────────────────────────────────┤
│ $ composer update                   │
│                                     │
│ ✓ Installing eduardoks98/base-api  │
│ ✓ Installing eduardoks98/helpers   │
│ ✓ Installing eduardoks98/auth      │
└─────────────────────────────────────┘
```

---

## 🎯 Opção 2: Uso Após Publicação no GitHub

```
┌──────────────────────────────────────────────────────┐
│  1️⃣ Push para GitHub                                 │
├──────────────────────────────────────────────────────┤
│  $ cd E:\larakit                                    │
│  $ git remote add origin git@github.com:...         │
│  $ git push -u origin main                          │
│  $ git push --tags                                  │
└──────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│  2️⃣ Uso em Qualquer Projeto                          │
├──────────────────────────────────────────────────────┤
│  $ composer config repositories.larakit vcs \       │
│    https://github.com/eduardoks98/larakit │
│                                                       │
│  $ composer require eduardoks98/base-api \           │
│    eduardoks98/auth eduardoks98/helpers              │
└──────────────────────────────────────────────────────┘
```

---

## 📝 Exemplo de Uso Rápido

### 1. Controller

```php
┌──────────────────────────────────────────────────────────┐
│  app/Http/Controllers/Api/UserController.php            │
├──────────────────────────────────────────────────────────┤
│                                                           │
│  use Eduardoks98\BaseApi\Http\Controllers\ApiController; │
│                                                           │
│  class UserController extends ApiController              │
│  {                                                        │
│      public function index()                             │
│      {                                                    │
│          $users = User::paginate(15);                    │
│          return UserResource::collection($users);        │
│      }                                                    │
│                                                           │
│      public function store(Request $request)             │
│      {                                                    │
│          // Validar CPF usando helper                    │
│          if (!checkCPF($request->cpf)) {                 │
│              return problemDetails(...);                 │
│          }                                                │
│                                                           │
│          return $this->created(new UserResource($user)); │
│      }                                                    │
│  }                                                        │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

### 2. Rotas

```php
┌──────────────────────────────────────────────────────────┐
│  routes/api.php                                          │
├──────────────────────────────────────────────────────────┤
│                                                           │
│  // Autenticação                                         │
│  Route::post('auth/login', [AuthController::class, ...])│
│      ->middleware(['throttle.login:5,15']);              │
│                                                           │
│  // Rotas protegidas                                     │
│  Route::middleware(['auth:sanctum'])->group(function(){  │
│      Route::apiResource('users', UserController::class); │
│  });                                                      │
│                                                           │
│  // Com permissões específicas                           │
│  Route::delete('users/{user}', ...)                      │
│      ->middleware(['abilities:users:delete']);           │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

### 3. Helpers

```php
┌──────────────────────────────────────────────────────────┐
│  Usando Helpers Globais                                  │
├──────────────────────────────────────────────────────────┤
│                                                           │
│  ✅ checkCPF('12345678909')                              │
│  ✅ formatPhoneNumber('11987654321')                     │
│     → (11) 98765-4321                                    │
│                                                           │
│  ✅ moneyFormat(1234.56)                                 │
│     → R$ 1.234,56                                        │
│                                                           │
│  ✅ problemDetails($type, $title, $status, $detail)      │
│     → RFC 7807 response                                  │
│                                                           │
│  ✅ whitelistIp('192.168.1.100', 'Office')               │
│  ✅ banIp('1.2.3.4', 'Suspicious activity')              │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

---

## 🔥 Fluxo de Autenticação

```
┌─────────────┐
│  Frontend   │
└──────┬──────┘
       │ 1. POST /api/auth/login
       │    { username, password, device_name }
       ▼
┌─────────────────────────────────────────────┐
│  Middlewares                                 │
├─────────────────────────────────────────────┤
│  ✓ throttle.login (5 tentativas/15min)     │
│  ✓ recaptcha (validação inteligente)       │
└──────┬──────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────┐
│  AuthController::login                       │
├─────────────────────────────────────────────┤
│  • Valida credenciais                       │
│  • Cria access token (15 min)               │
│  • Cria refresh token (7 dias)              │
│  • Registra sessão/device                   │
└──────┬──────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────┐
│  Response                                    │
├─────────────────────────────────────────────┤
│  {                                           │
│    "access_token": "1|abc123...",           │
│    "refresh_token": "2|xyz789...",          │
│    "expires_in": 900                        │
│  }                                           │
└─────────────────────────────────────────────┘
       │
       ▼
┌─────────────┐
│  Frontend   │  Armazena tokens
│  localStorage.setItem('access_token', ...)  │
└─────────────┘
```

---

## 🛡️ Segurança em Camadas

```
┌────────────────────────────────────────────────────────────┐
│                                                             │
│  📱 Client Request                                         │
│       │                                                     │
│       ▼                                                     │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Layer 1: Security Headers                            │ │
│  │ • CSP, HSTS, X-Frame-Options                         │ │
│  └────────────┬──────────────────────────────────────────┘ │
│               ▼                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Layer 2: IP Blocking                                 │ │
│  │ • Banned IPs check                                   │ │
│  │ • Geolocation filtering                              │ │
│  └────────────┬──────────────────────────────────────────┘ │
│               ▼                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Layer 3: Rate Limiting (3-Tier)                      │ │
│  │ • Tier 1: Route global (60/min)                      │ │
│  │ • Tier 2: IP+Route (30/min)                          │ │
│  │ • Tier 3: Global IP (100/min)                        │ │
│  └────────────┬──────────────────────────────────────────┘ │
│               ▼                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Layer 4: reCAPTCHA                                   │ │
│  │ • Trust score analysis                               │ │
│  │ • Bot detection                                      │ │
│  └────────────┬──────────────────────────────────────────┘ │
│               ▼                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Layer 5: Authentication                              │ │
│  │ • Sanctum token validation                           │ │
│  │ • Token abilities check                              │ │
│  └────────────┬──────────────────────────────────────────┘ │
│               ▼                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Controller                                           │ │
│  │ • Business logic                                     │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
└────────────────────────────────────────────────────────────┘
```

---

## 📊 Monitoramento

```
┌─────────────────────────────────────────────────────┐
│  Development (Local)                                 │
├─────────────────────────────────────────────────────┤
│  🔭 Telescope: http://localhost:8000/telescope       │
│     • Request details                               │
│     • Query logs                                    │
│     • Mail preview                                  │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│  Production                                          │
├─────────────────────────────────────────────────────┤
│  📊 Pulse: http://localhost:8000/pulse               │
│     • Real-time metrics                             │
│     • Slow queries                                  │
│     • Active users                                  │
│                                                      │
│  🚀 Horizon: http://localhost:8000/horizon           │
│     • Queue monitoring                              │
│     • Failed jobs                                   │
│     • Metrics                                       │
│                                                      │
│  ❤️  Health: http://localhost:8000/health           │
│     • /health       → Liveness                      │
│     • /health/full  → Readiness                     │
└─────────────────────────────────────────────────────┘
```

---

## 🎨 Arquitetura Visual

```
┌────────────────────────────────────────────────────────────────┐
│                         API BASE MONOREPO                       │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │ base-api │  │ helpers  │  │ security │  │  health  │      │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘      │
│       │             │              │             │             │
│       └─────────────┴──────────────┴─────────────┘             │
│                         │                                       │
│              ┌──────────┴──────────┐                           │
│              │                     │                           │
│       ┌──────▼─────┐        ┌─────▼──────┐                    │
│       │rate-limiter│        │ recaptcha  │                    │
│       └──────┬─────┘        └─────┬──────┘                    │
│              │                     │                           │
│              └──────────┬──────────┘                           │
│                         │                                       │
│                  ┌──────▼──────┐                               │
│                  │    auth     │                               │
│                  └──────┬──────┘                               │
│                         │                                       │
│       ┌─────────────────┼─────────────────┐                   │
│       │                 │                 │                   │
│  ┌────▼────┐      ┌────▼────┐      ┌────▼────┐              │
│  │performance│      │ reverb │      │api-docs │              │
│  └─────────┘      └─────────┘      └─────────┘              │
│                                                                 │
│                   🚀 Seu Projeto Laravel                       │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

---

## ✅ Checklist de Implementação

```
┌─────────────────────────────────────────────────┐
│  Setup Inicial                                   │
├─────────────────────────────────────────────────┤
│  ☐ Criar projeto Laravel                        │
│  ☐ Adicionar repositório no composer.json       │
│  ☐ composer update                              │
│  ☐ Publicar configs e migrations                │
│  ☐ Configurar .env                              │
│  ☐ php artisan migrate                          │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  Implementação                                   │
├─────────────────────────────────────────────────┤
│  ☐ Criar Controllers estendendo ApiController   │
│  ☐ Criar Resources estendendo ApiResource       │
│  ☐ Configurar rotas com middlewares             │
│  ☐ Aplicar middlewares globais no Kernel        │
│  ☐ Testar autenticação                          │
│  ☐ Testar rate limiting                         │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│  Produção                                        │
├─────────────────────────────────────────────────┤
│  ☐ Configurar Redis                             │
│  ☐ Configurar Horizon                           │
│  ☐ Configurar Pulse                             │
│  ☐ Configurar Reverb (se usar WebSocket)        │
│  ☐ Testar health checks                         │
│  ☐ Deploy!                                      │
└─────────────────────────────────────────────────┘
```

---

## 💡 Links Rápidos

- 📖 **Guia Detalhado**: [USAGE-GUIDE.md](./USAGE-GUIDE.md)
- 🚀 **Quick Start**: [docs/02-quick-start.md](./docs/02-quick-start.md)
- ⚙️  **Instalação**: [docs/03-installation.md](./docs/03-installation.md)
- 📦 **Docs Packages**: [docs/packages/](./docs/packages/)
- 🐛 **Troubleshooting**: [USAGE-GUIDE.md#troubleshooting](./USAGE-GUIDE.md#troubleshooting)

---

**🎯 TL;DR**: Adicione o repo no composer.json, rode `composer update`, publique configs, configure .env, e comece a usar os helpers e classes! 🚀
