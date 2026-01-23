# 🤖 Package: recaptcha

**Package Name**: `eduardoks98/recaptcha`
**Propósito**: Smart reCAPTCHA validation com trust scoring e análise contextual

---

## 📋 Visão Geral

Validação inteligente de reCAPTCHA v3/Enterprise com:

- **Trust Score** - Algoritmo de pontuação 0.0-1.0
- **Context-Aware** - Análise de IP, user-agent, histórico
- **Auto-Decision** - Aprovação/rejeição automática baseada em confiança
- **Analytics** - Logs detalhados para auditoria

**Compliance**: OWASP API6:2023 - Unrestricted Access to Sensitive Business Flows

---

## 🎯 Trust Score System

| Score | Decisão | Ação |
|-------|---------|------|
| ≥ 0.7 | Auto-approve | ✅ Aprovado sem reCAPTCHA |
| 0.5-0.7 | Context-based | 🤔 Análise adicional |
| 0.3-0.5 | Require reCAPTCHA | 🔒 Validação obrigatória |
| < 0.3 | Auto-reject | ❌ Rejeitado automaticamente |

### Fatores de Trust Score

- IP reputation (histórico de sucesso/falhas)
- User history (login history se conhecido)
- Geolocation risk (países de risco)
- User-Agent analysis (detecção de bots)
- Time patterns (horário comercial vs madrugada)
- Request frequency (tentativas recentes)

---

## 📦 Instalação

```bash
composer require eduardoks98/recaptcha
php artisan vendor:publish --provider="Eduardoks98\Recaptcha\RecaptchaServiceProvider"
php artisan migrate
```

Table criada: `recaptcha_logs`

---

## ⚙️ Configuração

```php
return [
    // reCAPTCHA v3
    'v3_secret' => env('RECAPTCHA_V3_SECRET'),
    'v3_site_key' => env('RECAPTCHA_V3_SITE_KEY'),

    // reCAPTCHA Enterprise (opcional)
    'enterprise_api_key' => env('RECAPTCHA_ENTERPRISE_API_KEY'),
    'enterprise_project_id' => env('RECAPTCHA_PROJECT_ID'),

    // Thresholds
    'threshold' => env('RECAPTCHA_THRESHOLD', 0.5),
    'high_trust_threshold' => 0.7,
    'medium_trust_threshold' => 0.5,
    'low_trust_threshold' => 0.3,
    'suspicious_threshold' => 0.1,

    // Países de alto risco
    'high_risk_countries' => [],
];
```

### .env

```env
RECAPTCHA_V3_SECRET=your_secret_key
RECAPTCHA_V3_SITE_KEY=your_site_key
RECAPTCHA_THRESHOLD=0.5
```

---

## 🚀 Uso

### 1. Frontend (React/Vue)

```javascript
// Adicionar script do Google
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY"></script>

// Executar reCAPTCHA no submit
const handleLogin = async (e) => {
    e.preventDefault();

    const token = await grecaptcha.execute('YOUR_SITE_KEY', {
        action: 'login'
    });

    const response = await fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            username: email,
            password: password,
            recaptcha_token: token,
            device_name: 'Web Browser'
        })
    });
};
```

### 2. Backend - Middleware

```php
// Em routes/api.php
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['recaptcha']);

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware(['recaptcha']);
```

### 3. Backend - Helper Function

```php
use function Eduardoks98\Recaptcha\checkRecaptcha;

public function login(Request $request)
{
    $result = checkRecaptcha(
        token: $request->recaptcha_token,
        action: 'login',
        context: [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => null, // Ainda não autenticado
        ]
    );

    if (!$result['success']) {
        return problemDetails(
            type: 'https://api.example.com/errors/recaptcha-failed',
            title: 'reCAPTCHA Validation Failed',
            status: 403,
            detail: $result['reason']
        );
    }

    // Continuar com login
}
```

### 4. Smart Validation Service

```php
use Eduardoks98\Recaptcha\Services\SmartRecaptchaService;

$service = app(SmartRecaptchaService::class);

$result = $service->validateWithContext(
    token: $request->recaptcha_token,
    action: 'login',
    context: [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'user_id' => $user->id ?? null,
        'login_context' => 'login', // ou 'register', 'password_reset'
    ]
);

/*
Retorna:
[
    'success' => true,
    'score' => 0.85,
    'decision' => 'auto_approved',
    'reason' => 'High trust score from known user',
    'factors' => [
        'ip_reputation' => 0.9,
        'user_history' => 0.95,
        'time_pattern' => 0.7,
        'geolocation' => 1.0,
        'user_agent' => 0.8,
    ]
]
*/
```

### 5. Atualizar Resultado do Login

```php
use function Eduardoks98\Recaptcha\updateRecaptchaLoginResult;

// Após tentativa de login
$recaptchaLog = checkRecaptcha(...);

if ($loginSuccessful) {
    updateRecaptchaLoginResult($recaptchaLog['log_id'], true);
} else {
    updateRecaptchaLoginResult($recaptchaLog['log_id'], false, 'Invalid credentials');
}
```

---

## 📝 Exemplos

### Exemplo 1: Login com Smart Validation

```php
namespace App\Http\Controllers\Api;

use Eduardoks98\Recaptcha\Services\SmartRecaptchaService;

class AuthController extends ApiController
{
    public function login(Request $request, SmartRecaptchaService $recaptcha)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha_token' => 'required|string',
        ]);

        // Smart validation
        $validation = $recaptcha->validateWithContext(
            token: $request->recaptcha_token,
            action: 'login',
            context: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        if (!$validation['success']) {
            \Log::warning('reCAPTCHA failed', [
                'ip' => $request->ip(),
                'decision' => $validation['decision'],
                'score' => $validation['score'],
            ]);

            return problemDetails(
                type: 'https://api.example.com/errors/suspicious-activity',
                title: 'Suspicious Activity Detected',
                status: 403,
                detail: 'Please try again later'
            );
        }

        // Continuar com autenticação
        if (!Auth::attempt($request->only('email', 'password'))) {
            updateRecaptchaLoginResult($validation['log_id'], false, 'Invalid credentials');

            return problemDetails(
                type: 'https://api.example.com/errors/invalid-credentials',
                title: 'Invalid Credentials',
                status: 401
            );
        }

        updateRecaptchaLoginResult($validation['log_id'], true);

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'access_token' => $token,
            'user' => new UserResource($user),
        ];
    }
}
```

### Exemplo 2: Analytics Dashboard

```php
use Eduardoks98\Recaptcha\Models\RecaptchaLog;

// Estatísticas das últimas 24h
$stats = RecaptchaLog::where('created_at', '>=', now()->subDay())
    ->selectRaw('
        COUNT(*) as total,
        AVG(score) as avg_score,
        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN login_successful = 1 THEN 1 ELSE 0 END) as successful_logins
    ')
    ->first();

return [
    'total_validations' => $stats->total,
    'average_score' => round($stats->avg_score, 2),
    'approval_rate' => round(($stats->approved / $stats->total) * 100, 1) . '%',
    'login_success_rate' => round(($stats->successful_logins / $stats->total) * 100, 1) . '%',
];
```

---

## 📚 API Reference

### Global Helpers

| Função | Descrição |
|--------|-----------|
| `checkRecaptcha($token, $action, $context)` | Valida token |
| `updateRecaptchaLoginResult($logId, $success, $reason)` | Atualiza resultado |

### Services

| Service | Métodos |
|---------|---------|
| `SmartRecaptchaService` | `validateWithContext()` |
| `RecaptchaService` | `verify()` (v3), `verifyEnterprise()` |

### Models

| Model | Descrição |
|-------|-----------|
| `RecaptchaLog` | Logs de validação |

---

## ⚠️ Troubleshooting

### Score sempre baixo

**Problema**: Usuários legítimos recebendo score < 0.5.

**Solução**: Ajustar threshold:

```php
'threshold' => 0.3, // Era 0.5
```

### Falsos positivos em bots

**Problema**: Bots passando com score alto.

**Solução**: Habilitar detecção avançada:

```php
'high_risk_countries' => ['CN', 'RU', 'VN'],
```

---

## 🔗 Dependências

```json
{
  "eduardoks98/base-api": "^1.0",
  "eduardoks98/security": "^1.0"
}
```

---

**Anterior**: [← Rate Limiter](./rate-limiter.md) | **Próximo**: [Auth →](./auth.md)
