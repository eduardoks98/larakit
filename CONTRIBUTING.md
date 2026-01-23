# Contribuindo para API Base Monorepo

Obrigado por considerar contribuir para o projeto! 🎉

## 🤝 Como Contribuir

### Reportar Bugs

1. Verifique se o bug já foi reportado em [Issues](https://github.com/eduardoks98/api-base-monorepo/issues)
2. Se não, abra uma nova issue com:
   - Título claro e descritivo
   - Passos para reproduzir
   - Comportamento esperado vs atual
   - Versões (PHP, Laravel, packages)
   - Screenshots se aplicável

### Sugerir Features

1. Abra uma [Discussion](https://github.com/eduardoks98/api-base-monorepo/discussions)
2. Descreva claramente:
   - O problema que a feature resolve
   - Sua solução proposta
   - Alternativas consideradas
   - Impacto em breaking changes

### Pull Requests

1. **Fork** o repositório
2. **Clone** seu fork localmente
3. **Crie uma branch** para sua feature/fix
   ```bash
   git checkout -b feature/minha-feature
   ```
4. **Faça suas mudanças** seguindo o estilo de código
5. **Adicione testes** Pest PHP
6. **Execute os testes**
   ```bash
   vendor/bin/pest
   ```
7. **Commit** suas mudanças
   ```bash
   git commit -m "feat: adiciona nova funcionalidade X"
   ```
8. **Push** para seu fork
   ```bash
   git push origin feature/minha-feature
   ```
9. **Abra um Pull Request** no repositório original

## 📋 Checklist para Pull Requests

- [ ] Código segue o estilo Laravel Pint
- [ ] Testes Pest PHP adicionados/atualizados
- [ ] Todos os testes passam
- [ ] Documentação atualizada (README, docs/)
- [ ] CHANGELOG.md atualizado
- [ ] Sem breaking changes (ou claramente documentado)
- [ ] Commit messages seguem convenção

## 🎨 Padrões de Código

### Laravel Pint

```bash
vendor/bin/pint
```

### Larastan (Static Analysis)

```bash
vendor/bin/phpstan analyse
```

### Pest PHP (Tests)

```bash
vendor/bin/pest
```

## 📝 Convenção de Commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: adiciona suporte a GraphQL
fix: corrige validação de CPF
docs: atualiza README com exemplos
refactor: melhora performance do rate limiter
test: adiciona testes para auth controller
chore: atualiza dependências
```

## 📦 Estrutura de Packages

Ao adicionar um novo package:

```
packages/novo-package/
├── composer.json          # Dependencies
├── config/               # Configuration files
├── src/
│   ├── ServiceProvider.php
│   ├── Http/            # Controllers, Middleware
│   ├── Services/        # Business logic
│   ├── Models/          # Eloquent models
│   └── helpers.php      # Global functions
├── database/migrations/ # Database migrations
├── tests/              # Pest PHP tests
└── README.md           # Package documentation
```

## 🧪 Testes

### Executar Todos os Testes

```bash
vendor/bin/pest
```

### Executar Testes com Coverage

```bash
vendor/bin/pest --coverage --min=80
```

### Executar Testes de um Package Específico

```bash
vendor/bin/pest packages/helpers/tests
```

## 📖 Documentação

### Atualizar Documentação

- `README.md` - Overview geral
- `docs/01-overview.md` - Decisões técnicas
- `docs/02-quick-start.md` - Guia rápido
- `docs/03-installation.md` - Instalação detalhada
- `docs/packages/{package}.md` - Documentação específica
- `packages/{package}/README.md` - README do package

### Estilo de Documentação

- Use exemplos práticos
- Inclua snippets de código funcionais
- Documente edge cases
- Mantenha conciso mas completo

## 🔒 Segurança

Se você descobrir uma vulnerabilidade de segurança:

1. **NÃO** abra uma issue pública
2. Envie email para: eduardo@example.com
3. Inclua:
   - Descrição da vulnerabilidade
   - Passos para reproduzir
   - Impacto potencial
   - Sugestão de fix (opcional)

## 📜 Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a [MIT License](LICENSE).

## 💬 Dúvidas?

- Abra uma [Discussion](https://github.com/eduardoks98/api-base-monorepo/discussions)
- Entre em contato: eduardo@example.com

## 🙏 Agradecimentos

Obrigado por tornar este projeto melhor! Toda contribuição, grande ou pequena, é valorizada.

---

**Happy Coding!** 🚀
