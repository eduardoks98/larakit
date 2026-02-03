# Contribuindo para Larakit

Obrigado por considerar contribuir para o projeto! 🎉

## 🤝 Como Contribuir

### Reportar Bugs

1. Verifique se o bug já foi reportado em [Issues](https://github.com/eduardoks98/larakit/issues)
2. Se não, abra uma nova issue com:
   - Título claro e descritivo
   - Passos para reproduzir
   - Comportamento esperado vs atual
   - Versões (PHP, Laravel, packages)
   - Screenshots se aplicável

### Sugerir Features

1. Abra uma [Discussion](https://github.com/eduardoks98/larakit/discussions)
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

Usamos [Conventional Commits](https://www.conventionalcommits.org/) com **semantic-release** para versionamento automatico dos pacotes NPM.

### Tipos e Releases

| Tipo | Release NPM | Descricao |
|------|-------------|-----------|
| `fix:` | Patch (1.0.X) | Correcao de bug |
| `feat:` | Minor (1.X.0) | Nova funcionalidade |
| `BREAKING CHANGE:` | Major (X.0.0) | Mudanca incompativel |
| `docs:` | Nenhum | Documentacao |
| `refactor:` | Nenhum | Refatoracao |
| `test:` | Nenhum | Testes |
| `chore:` | Nenhum | Manutencao |

### Exemplos

```bash
# Patch release (1.0.0 -> 1.0.1)
git commit -m "fix: corrigir tracking duplicado no GA4"

# Minor release (1.0.0 -> 1.1.0)
git commit -m "feat: adicionar hook useMatchTracking"

# Major release (1.0.0 -> 2.0.0)
git commit -m "feat: redesenhar API de eventos

BREAKING CHANGE: O metodo trackEvent foi renomeado para event()"

# Sem release
git commit -m "docs: atualizar exemplos de uso"
git commit -m "chore: atualizar dependencias"
```

### Escopo (Opcional)

```bash
git commit -m "fix(analytics): corrigir inicializacao do GA4"
git commit -m "feat(adsense): adicionar componente StickyAd"
git commit -m "docs(facebook): documentar eventos de conversao"
```

### Dicas para o Claude

1. **Sempre use prefixos**: `fix:`, `feat:`, `docs:`, etc.
2. **Seja especifico**: "corrigir tracking duplicado" > "corrigir bug"
3. **Use escopo** quando modificar um pacote especifico
4. **Documente BREAKING CHANGE** quando mudar API publica

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

- Abra uma [Discussion](https://github.com/eduardoks98/larakit/discussions)
- Entre em contato: eduardo@example.com

## 🙏 Agradecimentos

Obrigado por tornar este projeto melhor! Toda contribuição, grande ou pequena, é valorizada.

---

**Happy Coding!** 🚀
