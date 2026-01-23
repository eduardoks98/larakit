# ⏱️ Package: rate-limiter

**Package Name**: `eduardoks98/rate-limiter`
**Propósito**: Sistema de rate limiting em 3 camadas com geolocation e fail2ban

---

## 📋 Visão Geral

Sistema avançado de rate limiting com:

- **3-Tier Throttling** - Per-route global, Per-IP+Route, Global IP ban
- **Geolocation** - Bloqueio por país
- **Injection Detection** - Detecta SQL injection e XSS
- **Volume Anomaly** - Detecta picos de tráfego anormais
- **IP Whitelist** - Suporte a ranges e CIDR

**Compliance**: OWASP API4:2023 - Unrestricted Resource Consumption

---

## 🎯 3-Tier System

### Tier 1: Per-Route Global
- Limita requisições por rota globalmente
- Default: 60 requests/minute
- Exemplo: `/api/v1/users` max 60 req/min total

### Tier 2: Per-IP + Route
- Limita requisições do mesmo IP para rota específica
- Default: 30 requests/minute
- Exemplo: IP `1.2.3.4` → `/api/v1/users` max 30 req/min

### Tier 3: Global IP Ban
- Ban global após threshold
- Default: 100 requests/minute across all routes
- Duração configurável (default 1 hora)

---

## 📦 Instalação

```bash
composer require eduardoks98/rate-limiter
php artisan vendor:publish --provider="Eduardoks98\RateLimiter\RateLimiterServiceProvider"
php artisan migrate
```

Tables criadas:
- `api_request_logs`
- `api_request_stats`
- `ip_whitelist`

---

## ⚙️ Configuração

```php
return [
    'enabled' => true,
    'decay_minutes' => env('RATE_LIMITER_TIME_DECAY_MINUTES', 1),
    'max_attempts_before_ban' => env('MAX_ATTEMPTS_BEFORE_BAN_IP', 100),
    'route_max_attempts' => env('RATE_LIMITER_MAX_ATTEMPTS', 60),
    'ip_route_max_attempts' => env('THROTTLE_MAX_ATTEMPTS', 30),

    // Features avançadas
    'whitelist_enabled' => true,
    'geolocation_enabled' => true,
    'injection_detection' => true,
    'volume_anomaly_detection' => true,
    'anomaly_threshold_multiplier' => 3,

    // Países de alto risco (opcional)
    'high_risk_countries' => [], // ['CN', 'RU']
];
```

---

## 🚀 Uso

### 1. Middleware GenericThrottle

```php
// No Kernel.php
protected $routeMiddleware = [
    'throttle.generic' => \Eduardoks98\RateLimiter\Http\Middleware\GenericThrottle::class,
];

// Em routes/api.php
Route::middleware(['throttle.generic'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

### 2. LoginThrottle (Endpoints de Auth)

```php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['throttle.login']); // 5 attempts/minute
```

### 3. IP Whitelist

```php
use Eduardoks98\RateLimiter\Services\IpWhitelistService;

$service = app(IpWhitelistService::class);

// Adicionar IP único
$service->addRange('192.168.1.100', '192.168.1.100', 'Office IP');

// Adicionar range
$service->addRange('10.0.0.1', '10.0.0.255', 'Internal network');

// Verificar whitelist
$isWhitelisted = $service->isWhitelisted('192.168.1.100');
```

### 4. Logs e Estatísticas

```php
use Eduardoks98\RateLimiter\Models\{ApiRequestLog, ApiRequestStat};

// Logs detalhados
$logs = ApiRequestLog::where('ip', '192.168.1.100')
    ->where('success', false)
    ->get();

// Estatísticas agregadas
$stats = ApiRequestStat::where('ip_address', '192.168.1.100')
    ->where('date', today())
    ->first();

echo "Total requests: " . $stats->total_requests;
```

---

## 📝 Exemplos

### Exemplo 1: Configurar Rate Limits Diferentes por Rota

```php
Route::prefix('api/v1')->group(function () {
    // Rotas públicas - limite mais restritivo
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware(['throttle.login']); // 5/min

    // Rotas autenticadas - limite padrão
    Route::middleware(['auth:sanctum', 'throttle.generic'])->group(function () {
        Route::get('/users', [UserController::class, 'index']); // 30/min
        Route::get('/posts', [PostController::class, 'index']); // 30/min
    });

    // Rotas administrativas - sem limite (whitelist)
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/admin/logs', [AdminController::class, 'logs']);
    });
});
```

### Exemplo 2: Monitorar e Alertar sobre IPs Suspeitos

```php
namespace App\Console\Commands;

use Eduardoks98\RateLimiter\Models\ApiRequestLog;

class MonitorSuspiciousIps extends Command
{
    public function handle()
    {
        // IPs com alta taxa de erros nas últimas 24h
        $suspiciousIps = ApiRequestLog::selectRaw('ip, COUNT(*) as total, SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failures')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('ip')
            ->havingRaw('failures / total > 0.5') // > 50% de falhas
            ->havingRaw('total > 100') // Mínimo 100 requests
            ->get();

        foreach ($suspiciousIps as $ip) {
            \Log::warning("Suspicious IP detected: {$ip->ip} ({$ip->failures}/{$ip->total} failures)");
        }
    }
}
```

---

## 📚 API Reference

### Middleware

| Middleware | Limite | Uso |
|------------|--------|-----|
| `throttle.generic` | 30 req/min | Rotas gerais |
| `throttle.login` | 5 req/min | Endpoints de auth |

### Services

| Service | Métodos |
|---------|---------|
| `ThrottleService` | `checkRateLimit()`, `recordAttempt()`, `isIpBanned()`, `analyzeVolumeAnomaly()` |
| `IpWhitelistService` | `isWhitelisted()`, `addRange()` |

### Models

| Model | Descrição |
|-------|-----------|
| `ApiRequestLog` | Logs detalhados de requests |
| `ApiRequestStat` | Estatísticas agregadas |
| `IpWhitelist` | Whitelist de IPs |

---

## ⚠️ Troubleshooting

### IPs legítimos sendo banidos

**Problema**: IPs de usuários reais estão sendo bloqueados.

**Solução**: Adicione à whitelist ou aumente os limites:

```php
// config/rate-limiter.php
'ip_route_max_attempts' => 50, // Era 30
'max_attempts_before_ban' => 200, // Era 100
```

### Injection detection com falsos positivos

**Problema**: Requests legítimos sendo marcados como injection.

**Solução**: Desabilite ou ajuste:

```php
'injection_detection' => false,
```

---

## 🔗 Dependências

```json
{
  "eduardoks98/base-api": "^1.0",
  "eduardoks98/security": "^1.0",
  "stevebauman/location": "^7.4"
}
```

---

**Anterior**: [← Security](./security.md) | **Próximo**: [Recaptcha →](./recaptcha.md)
