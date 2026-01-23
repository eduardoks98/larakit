# ❤️ Health - Health Check Endpoints

Kubernetes and load balancer ready health check endpoints.

## Installation
```bash
composer require eduardoks98/health
```

## Features
- ✅ **Basic Health** - Simple liveness check
- ✅ **Database Health** - Connection and query test
- ✅ **Cache Health** - Read/write test
- ✅ **Queue Health** - Queue connectivity
- ✅ **Full Health** - All checks combined
- ✅ **K8s Ready** - Liveness and readiness probes

## Endpoints
```bash
GET /health          # Basic health
GET /health/db       # Database check
GET /health/cache    # Cache check
GET /health/queue    # Queue check
GET /health/full     # All checks
```

## Kubernetes
```yaml
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

## License
MIT - Eduardo Steffens (@eduardoks98)
