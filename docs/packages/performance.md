# 📊 Package: performance

**Package Name**: `eduardoks98/performance`
**Propósito**: Performance monitoring com integração Laravel Pulse (Laravel 11+)

---

## 📋 Visão Geral

Monitoring e otimização de performance com:

- **Request Tracking** - Duration, query count, memory
- **N+1 Detection** - Alerta automático de lazy loading
- **Slow Request Detection** - Identifica endpoints lentos
- **Laravel Pulse Integration** - Dashboards em tempo real
- **Performance Reports** - Relatórios diários/semanais

---

## 📦 Instalação

```bash
composer require eduardoks98/performance
php artisan vendor:publish --provider="Eduardoks98\Performance\PerformanceServiceProvider"
php artisan migrate
```

Table criada: `performance_logs`

---

## ⚙️ Configuração

```php
return [
    'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
    'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 1000), // ms
    'log_queries' => env('LOG_QUERIES', false),
    'log_memory' => env('LOG_MEMORY', true),
    'sample_rate' => env('PERFORMANCE_SAMPLE_RATE', 1.0), // 0.0 to 1.0
    'pulse_enabled' => env('PULSE_ENABLED', true), // Laravel 11+
];
```

### .env

```env
PERFORMANCE_MONITORING_ENABLED=true
SLOW_REQUEST_THRESHOLD=1000
PULSE_ENABLED=true
```

---

## 🚀 Uso

### 1. Middleware PerformanceMonitor

```php
// No Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Eduardoks98\Performance\Http\Middleware\PerformanceMonitor::class,
        // ...
    ],
];
```

### 2. N+1 Query Prevention

```php
// No AppServiceProvider
use function Eduardoks98\BaseApi\preventN1Query;

public function boot()
{
    // Habilitar em desenvolvimento
    if (!app()->isProduction()) {
        preventN1Query();
    }
}
```

### 3. Performance Reports

```php
use Eduardoks98\Performance\Services\PerformanceReportService;

$service = app(PerformanceReportService::class);

// Relatório diário
$report = $service->dailyReport(today());

echo "Total Requests: " . $report['total_requests'];
echo "Avg Duration: " . $report['avg_duration'] . "ms";
echo "Slow Requests: " . $report['slow_requests'];

// Top 10 endpoints lentos
$slowest = $service->slowestEndpoints(limit: 10);
```

### 4. Laravel Pulse Integration

```php
// config/pulse.php
return [
    'recorders' => [
        // Recorders padrão...

        // Recorder customizado do package
        \Eduardoks98\Performance\Pulse\Recorders\ApiPerformanceRecorder::class => [
            'enabled' => true,
        ],
    ],
];
```

Acesse: `http://localhost:8000/pulse`

---

## 📝 Exemplos

### Exemplo 1: Dashboard de Performance

```php
namespace App\Http\Controllers\Admin;

use Eduardoks98\Performance\Models\PerformanceLog;

class PerformanceDashboardController extends Controller
{
    public function index()
    {
        $stats = PerformanceLog::whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as total_requests,
                AVG(duration_ms) as avg_duration,
                AVG(query_count) as avg_queries,
                AVG(memory_mb) as avg_memory,
                SUM(CASE WHEN is_slow = 1 THEN 1 ELSE 0 END) as slow_requests,
                MAX(duration_ms) as max_duration
            ')
            ->first();

        $slowestRoutes = PerformanceLog::where('is_slow', true)
            ->whereDate('created_at', today())
            ->select('route', 'method')
            ->selectRaw('AVG(duration_ms) as avg_duration, COUNT(*) as count')
            ->groupBy('route', 'method')
            ->orderByDesc('avg_duration')
            ->limit(10)
            ->get();

        return view('admin.performance', compact('stats', 'slowestRoutes'));
    }
}
```

### Exemplo 2: Alert de Performance

```php
namespace App\Console\Commands;

use Eduardoks98\Performance\Models\PerformanceLog;
use Illuminate\Support\Facades\Mail;

class AlertSlowEndpoints extends Command
{
    public function handle()
    {
        $slowEndpoints = PerformanceLog::where('created_at', '>=', now()->subHour())
            ->where('is_slow', true)
            ->select('route')
            ->selectRaw('AVG(duration_ms) as avg_duration, COUNT(*) as count')
            ->groupBy('route')
            ->having('count', '>', 10) // Mais de 10 requests lentos
            ->get();

        if ($slowEndpoints->isNotEmpty()) {
            Mail::to('dev@example.com')->send(
                new SlowEndpointAlert($slowEndpoints)
            );
        }
    }
}
```

---

## 📚 API Reference

### Services

| Service | Métodos |
|---------|---------|
| `PerformanceReportService` | `dailyReport()`, `weeklyReport()`, `slowestEndpoints()` |

### Models

| Model | Descrição |
|-------|-----------|
| `PerformanceLog` | Logs de performance |

---

## 🔗 Dependências

```json
{
  "eduardoks98/base-api": "^1.0",
  "laravel/pulse": "^1.0"
}
```

---

**Anterior**: [← Auth](./auth.md) | **Próximo**: [Reverb →](./reverb.md)
