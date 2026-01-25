# 📋 Visão Geral

## O que é o Larakit?

O **Larakit** é uma coleção de 10 packages Composer reutilizáveis criados para acelerar o desenvolvimento de APIs REST modernas com Laravel 11/12, seguindo as **melhores práticas de mercado 2024-2026**.

## 🎯 Objetivos

### Principais
- ✅ **DRY (Don't Repeat Yourself)**: Escrever código uma vez, usar em todos os projetos
- ✅ **Production-Ready**: Código testado, seguro e performático desde o dia 1
- ✅ **Standards-Based**: Seguir padrões da indústria (RFC 7807, OpenAPI 3.1, OWASP)
- ✅ **Developer Experience**: Instalação rápida, zero config, documentação completa

### Secundários
- 🇧🇷 **Mercado Brasileiro**: Utilitários específicos (CPF/CNPJ, phone, currency)
- 🔒 **Security First**: OWASP API Security Top 10 compliance out-of-the-box
- 📊 **Observability**: Monitoring integrado (Telescope, Pulse, Horizon)
- 🚀 **Modern Stack**: Usar os recursos mais recentes do Laravel 11/12

## 🏗️ Arquitetura

### Estratégia: Híbrido Monorepo

**Monorepo (este repositório)**:
- 10 packages core inter-relacionados
- Desenvolvimento sincronizado
- Testes integrados
- Versionamento unificado

**Multi-repo (futuro)**:
- Integrações específicas (Google, Facebook, MercadoPago, etc.)
- Evolução independente
- Instalação opcional

### Por que Monorepo?

**Vantagens**:
- ✅ Facilita desenvolvimento simultâneo
- ✅ Compartilhamento de código entre packages
- ✅ Refactoring cross-package simplificado
- ✅ Testes de integração facilitados
- ✅ Versionamento sincronizado

**Desvantagens gerenciadas**:
- ⚠️ Repositório maior → Mitigado com .gitignore apropriado
- ⚠️ Build time → Mitigado com caching e CI/CD otimizado

## 📦 Packages Incluídos

### Tier 1: Foundation
1. **base-api** - RFC 7807, API Resources, HTTP Client
2. **helpers** - Utilitários brasileiros (framework-agnostic)

### Tier 2: Core Services
3. **security** - CSP, IP blocking, OWASP compliance
4. **health** - Health checks (K8s/Load Balancer ready)
5. **api-docs** - Scramble + OpenAPI 3.1

### Tier 3: Advanced Features
6. **rate-limiter** - 3-tier throttling + geolocation
7. **recaptcha** - Smart validation com trust scoring
8. **auth** - Sanctum + Token Abilities + Device Management
9. **performance** - Monitoring com Laravel Pulse
10. **reverb** - WebSocket (Laravel Reverb wrapper)

## 🔑 Decisões Técnicas

Todas as decisões foram baseadas em **pesquisa de mercado** (artigos, documentação oficial, GitHub):

### Response Format: RFC 7807
**Escolhido**: RFC 7807 (Problem Details for HTTP APIs)

**Alternativas consideradas**:
- JSON:API (muito verboso)
- Custom format (sem padrão)

**Justificativa**: Padrão moderno adotado por grandes empresas (Microsoft, Google), flexível, focado em erros.

### Documentation: Scramble
**Escolhido**: Scramble (auto-geração)

**Alternativas consideradas**:
- L5-Swagger (requer anotações manuais)

**Justificativa**: Zero maintenance, sempre sincronizado, OpenAPI 3.1, Stoplight UI moderno.

### Testing: Pest PHP
**Escolhido**: Pest PHP

**Alternativas consideradas**:
- PHPUnit (tradicional)

**Justificativa**: Sintaxe moderna, architecture testing built-in, futuro do Laravel.

### Authentication: Sanctum
**Escolhido**: Laravel Sanctum

**Alternativas consideradas**:
- Laravel Passport (OAuth2 complexo)

**Justificativa**: First-party apps, device management, simple token auth, recommended by Laravel.

### Real-time: Laravel Reverb
**Escolhido**: Laravel Reverb

**Alternativas consideradas**:
- Pusher (pago)
- Socket.io (Node.js)

**Justificativa**: Nativo Laravel 11+, blazing fast, Pusher compatible, open source.

### Queues: Redis + Horizon
**Escolhido**: Redis + Horizon

**Alternativas consideradas**:
- Database queue (mais lento)
- Beanstalkd (menos features)

**Justificativa**: 50-70% faster, auto-balancing, production-ready dashboard.

## 🎨 Princípios de Design

### 1. Convention over Configuration
Funciona out-of-the-box com defaults sensatos, mas 100% customizável.

### 2. Fail Fast
Erros claros e imediatos durante desenvolvimento, silent e logged em produção.

### 3. Zero Breaking Changes
Mudanças breaking só em major versions, com migration guide detalhado.

### 4. Developer Happiness
API intuitiva, documentação clara, exemplos abundantes.

### 5. Performance by Default
Eager loading automático, N+1 detection, response caching, etc.

## 🔄 Ciclo de Vida

### Development
```bash
composer require eduardoks98/base-api --dev
TELESCOPE_ENABLED=true  # Debugging detalhado
```

### Staging
```bash
composer require eduardoks98/base-api
PULSE_ENABLED=true      # Monitoring leve
```

### Production
```bash
composer require eduardoks98/base-api --optimize-autoloader
TELESCOPE_ENABLED=false
PULSE_ENABLED=true
HORIZON_ENABLED=true
```

## 📊 Casos de Uso

### ✅ Ideal Para
- APIs REST para mobile apps
- Backend para SPAs (React, Vue, Angular)
- Microserviços
- APIs públicas
- APIs internas corporativas

### ⚠️ Não Recomendado Para
- Aplicações monolíticas tradicionais (use Laravel completo)
- GraphQL-first APIs (use Lighthouse diretamente)
- gRPC APIs (use framework específico)

## 🔮 Roadmap

### v1.0.0 (COMPLETO ✅)
- [x] Pesquisa de mercado e decisões técnicas
- [x] 10 packages core implementados
- [x] Documentação completa
- [x] Service Providers com auto-discovery
- [x] 9 migrations para banco de dados
- [x] 50+ helper functions globais
- [ ] Testes Pest PHP (em andamento)
- [ ] CI/CD configurado (próximo)

### v1.1.0 (Futuro)
- [ ] Package `eduardoks98/google-auth`
- [ ] Package `eduardoks98/facebook-auth`
- [ ] Package `eduardoks98/payment-stripe`

### v2.0.0 (Futuro)
- [ ] Suporte a Laravel 13
- [ ] GraphQL package opcional
- [ ] Service mesh integration

## 🤔 FAQ

**P: Posso usar apenas 1 package?**
R: Sim! Cada package é independente (respeitando dependências).

**P: Funciona com Laravel 10?**
R: Sim, suporta Laravel 10/11/12.

**P: É grátis?**
R: Sim, licença MIT.

**P: Posso contribuir?**
R: Sim! Pull requests são bem-vindos.

**P: Tem suporte comercial?**
R: Contato: eduardoks98@gmail.com

---

**Próximo**: [Quick Start →](./02-quick-start.md)
