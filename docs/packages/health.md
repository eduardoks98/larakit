# ❤️ Package: health

**Package Name**: `eduardoks98/health`
**Propósito**: Health check endpoints para Kubernetes, load balancers e monitoring

---

## 📋 Visão Geral

Health checks para infraestrutura:

- **Basic Health** - Status geral da aplicação
- **Database Health** - Conexões MySQL/Redis
- **Cache Health** - Status do cache
- **Queue Health** - Status das filas
- **Full Report** - Todos os checks combinados
- **K8s Ready** - Liveness & Readiness probes

---

## 📦 Instalação

```bash
composer require eduardoks98/health
```

Nenhuma migration necessária!

---

## 🚀 Uso

### 1. Endpoints Disponíveis

| Endpoint | Descrição | HTTP Code |
|----------|-----------|-----------|
| `GET /health` | Basic health check | 200 / 503 |
| `GET /health/db` | Database connections | 200 / 503 |
| `GET /health/cache` | Cache status | 200 / 503 |
| `GET /health/queue` | Queue status | 200 / 503 |
| `GET /health/full` | All checks combined | 200 / 503 |

### 2. Basic Health

```bash
curl http://localhost:8000/health
```

**Response (200 OK)**:
```json
{
  "status": "healthy",
  "timestamp": "2026-01-23T10:30:00Z",
  "uptime": 86400
}
```

### 3. Database Health

```bash
curl http://localhost:8000/health/db
```

**Response (200 OK)**:
```json
{
  "status": "healthy",
  "connections": {
    "mysql": {
      "status": "up",
      "response_time_ms": 2
    },
    "redis": {
      "status": "up",
      "response_time_ms": 1
    }
  }
}
```

**Response (503 Service Unavailable)** se falhar:
```json
{
  "status": "unhealthy",
  "connections": {
    "mysql": {
      "status": "down",
      "error": "Connection refused"
    }
  }
}
```

### 4. Cache Health

```bash
curl http://localhost:8000/health/cache
```

**Response**:
```json
{
  "status": "healthy",
  "driver": "redis",
  "response_time_ms": 1,
  "test_write": true,
  "test_read": true
}
```

### 5. Queue Health

```bash
curl http://localhost:8000/health/queue
```

**Response**:
```json
{
  "status": "healthy",
  "driver": "redis",
  "pending_jobs": 42,
  "failed_jobs": 0
}
```

### 6. Full Report

```bash
curl http://localhost:8000/health/full
```

**Response**:
```json
{
  "status": "healthy",
  "checks": {
    "database": {
      "status": "healthy",
      "connections": {
        "mysql": { "status": "up" },
        "redis": { "status": "up" }
      }
    },
    "cache": {
      "status": "healthy",
      "driver": "redis"
    },
    "queue": {
      "status": "healthy",
      "pending_jobs": 42,
      "failed_jobs": 0
    }
  },
  "timestamp": "2026-01-23T10:30:00Z"
}
```

---

## 📝 Kubernetes Integration

### Liveness Probe

Verifica se a aplicação está rodando.

```yaml
apiVersion: v1
kind: Pod
metadata:
  name: api-pod
spec:
  containers:
  - name: api
    image: my-api:latest
    livenessProbe:
      httpGet:
        path: /health
        port: 8000
      initialDelaySeconds: 30
      periodSeconds: 10
      timeoutSeconds: 5
      failureThreshold: 3
```

### Readiness Probe

Verifica se a aplicação está pronta para receber tráfego.

```yaml
readinessProbe:
  httpGet:
    path: /health/full
    port: 8000
  initialDelaySeconds: 5
  periodSeconds: 5
  timeoutSeconds: 3
  failureThreshold: 2
```

### Startup Probe

Para aplicações que demoram a iniciar.

```yaml
startupProbe:
  httpGet:
    path: /health/db
    port: 8000
  initialDelaySeconds: 0
  periodSeconds: 2
  timeoutSeconds: 3
  failureThreshold: 30  # 60 seconds total
```

---

## 📝 Load Balancer Integration

### AWS ALB

```hcl
resource "aws_lb_target_group" "api" {
  health_check {
    enabled             = true
    path                = "/health/full"
    port                = "traffic-port"
    protocol            = "HTTP"
    timeout             = 5
    healthy_threshold   = 2
    unhealthy_threshold = 2
    interval            = 30
    matcher             = "200"
  }
}
```

### NGINX

```nginx
upstream api_backend {
    server api1.example.com:8000 max_fails=3 fail_timeout=30s;
    server api2.example.com:8000 max_fails=3 fail_timeout=30s;
}

location /health {
    access_log off;
    proxy_pass http://api_backend;
}
```

---

## 📝 Monitoring Integration

### Prometheus

```yaml
scrape_configs:
  - job_name: 'api-health'
    metrics_path: '/health/full'
    static_configs:
      - targets: ['localhost:8000']
```

### Uptime Robot

```
Monitor Type: HTTP(s)
Monitor URL: https://api.example.com/health
Monitoring Interval: 5 minutes
```

---

## 📚 API Reference

### Controllers

| Controller | Métodos |
|------------|---------|
| `HealthController` | `index()`, `database()`, `cache()`, `queue()`, `full()` |

---

## ⚠️ Troubleshooting

### Health check sempre retorna 503

**Problema**: Mesmo com aplicação funcionando, retorna unhealthy.

**Solução**: Verificar conexões:

```bash
# Testar MySQL
php artisan tinker
>>> DB::connection()->getPdo();

# Testar Redis
>>> Cache::get('test');
```

---

## 🔗 Dependências

```json
{
  "eduardoks98/base-api": "^1.0"
}
```

---

**Anterior**: [← API Docs](./api-docs.md)
