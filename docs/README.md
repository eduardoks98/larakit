# 🚀 API Base Monorepo - Documentação Completa

Bem-vindo à documentação oficial do **API Base Monorepo** - uma coleção de packages Composer reutilizáveis para construir APIs REST modernas com Laravel 11/12.

## 📚 Índice da Documentação

### Getting Started
- [Visão Geral](./01-overview.md) - Introdução ao projeto e decisões técnicas
- [Quick Start](./02-quick-start.md) - Comece em 5 minutos
- [Instalação](./03-installation.md) - Guia completo de instalação
- [Usage Guide](./usage-guide.md) - Guia completo com 7 exemplos práticos
- [Quick Reference](./quick-reference.md) - Guia visual rápido

### Packages Core (v1.0.0)
- [base-api](./packages/base-api.md) - Foundation package (RFC 7807, API Resources)
- [helpers](./packages/helpers.md) - Utilitários brasileiros (CPF/CNPJ, phone, money)
- [security](./packages/security.md) - Security headers, CSP, IP blocking
- [rate-limiter](./packages/rate-limiter.md) - 3-tier throttling system
- [recaptcha](./packages/recaptcha.md) - Smart reCAPTCHA validation
- [auth](./packages/auth.md) - Sanctum + JWT authentication
- [performance](./packages/performance.md) - Performance monitoring
- [reverb](./packages/reverb.md) - WebSocket (Laravel Reverb)
- [api-docs](./packages/api-docs.md) - Scramble OpenAPI documentation
- [health](./packages/health.md) - Health check endpoints

### Social Authentication (v1.1.0)
- [google-auth](./packages/google-auth.md) - Google OAuth 2.0 integration
- [facebook-auth](./packages/facebook-auth.md) - Facebook OAuth integration
- [microsoft-auth](./packages/microsoft-auth.md) - Microsoft/Azure AD OAuth

### Payment Gateways (v1.1.0)
- [payment-stripe](./packages/payment-stripe.md) - Stripe payment gateway
- [payment-mercadopago](./packages/payment-mercadopago.md) - MercadoPago (Brazil/LATAM)
- [payment-abacatepay](./packages/payment-abacatepay.md) - AbacatePay PIX (Brazil)

### SMS Packages (v1.2.0)
- [sms-twilio](./packages/sms-twilio.md) - Twilio SMS global integration
- [sms-comtele](./packages/sms-comtele.md) - Comtele SMS (Brazil)

### WhatsApp Packages (v1.2.0)
- [whatsapp-official](./packages/whatsapp-official.md) - WhatsApp Business Cloud API
- [whatsapp-converx](./packages/whatsapp-converx.md) - Converx WhatsApp (Brazil)

### Storage Packages (v1.3.0)
- [storage-s3](./packages/storage-s3.md) - AWS S3 wrapper with CloudFront CDN
- [media-library](./packages/media-library.md) - Media management and image processing

### Brazilian Market Utilities (v1.4.0)
- [geolocation](./packages/geolocation.md) - ViaCEP, geocoding, distance calculation
- [email-validator](./packages/email-validator.md) - Advanced email validation with quality scoring
- [banking](./packages/banking.md) - PIX validation, bank codes, boleto validation

### Guias Avançados
- [API Versioning](./guides/api-versioning.md) - Estratégias de versionamento
- [Security Best Practices](./guides/security.md) - OWASP API Security Top 10
- [Performance Optimization](./guides/performance.md) - Otimizações de performance
- [Testing Guide](./guides/testing.md) - Testes com Pest PHP
- [Deployment](./guides/deployment.md) - Deploy em produção
- [Monitoring](./guides/monitoring.md) - Observabilidade completa

### Referência
- [Architecture](./reference/architecture.md) - Arquitetura do monorepo
- [Dependencies](./reference/dependencies.md) - Grafo de dependências
- [Configuration](./reference/configuration.md) - Todas as configurações
- [Costs & API Keys](./reference/costs.md) - Custos, como obter keys, configuração
- [API Reference](./reference/api-reference.md) - Referência completa de APIs
- [Troubleshooting](./reference/troubleshooting.md) - Solução de problemas

### Project Management
- [Roadmap](../ROADMAP.md) - Versões futuras e features planejadas
- [Project Status](./project-status.md) - Status atual da implementação
- [Release Instructions](./release-instructions.md) - Como publicar novas versões
- [Implementation Status](./implementation-status.md) - Checklist completo

## 🎯 Stack Tecnológica

| Categoria | Tecnologia | Versão |
|-----------|-----------|--------|
| **Framework** | Laravel | 10/11/12 |
| **PHP** | PHP | 8.1/8.2+ |
| **Response Format** | RFC 7807 | Standard |
| **Documentation** | Scramble | ^0.10 |
| **Testing** | Pest PHP | ^2.0 |
| **Real-time** | Laravel Reverb | ^1.0 |
| **Authentication** | Laravel Sanctum | ^3.0/^4.0 |
| **Queues** | Redis + Horizon | Latest |
| **Caching** | Redis | Latest |

## 🚦 Status do Projeto

- **Versão Atual**: v1.4.0
- **Status**: Production Ready ✅
- **Última Atualização**: 2026-01-24
- **Total de Packages**: 25 (10 core + 3 auth + 3 payment + 2 SMS + 2 WhatsApp + 2 Storage + 3 Brazilian)
- **Owner**: Eduardo Steffens (@eduardoks98)
- **License**: MIT

### Versões Completadas
- ✅ **v1.0.0** (23/01/2026) - 10 core packages
- ✅ **v1.1.0** (24/01/2026) - Social auth + Payments (6 packages)
- ✅ **v1.2.0** (24/01/2026) - SMS + WhatsApp (4 packages)
- ✅ **v1.3.0** (24/01/2026) - Storage & Media (2 packages)
- ✅ **v1.4.0** (24/01/2026) - Brazilian Market Utilities (3 packages)

## 📖 Como Usar Esta Documentação

1. **Iniciantes**: Comece pelo [Quick Start](./02-quick-start.md)
2. **Desenvolvedores**: Veja os [Packages](./packages/) específicos que precisa
3. **DevOps**: Consulte o [Deployment Guide](./guides/deployment.md)
4. **Arquitetos**: Leia a [Architecture](./reference/architecture.md)

## 🤝 Contribuindo

Esta documentação está em constante evolução. Sugestões são bem-vindas!

## 🔗 Links Úteis

- [GitHub Repository](https://github.com/eduardoks98/api-base-monorepo)
- [Laravel Documentation](https://laravel.com/docs)
- [Pest PHP](https://pestphp.com)
- [RFC 7807](https://www.rfc-editor.org/rfc/rfc7807)
- [OWASP API Security](https://owasp.org/API-Security/)

---

**Feito com ❤️ para a comunidade Laravel brasileira**
