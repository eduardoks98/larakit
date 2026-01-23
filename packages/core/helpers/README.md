# 🇧🇷 Helpers - Brazilian Utilities

Utilitários para o mercado brasileiro - 100% framework-agnostic!

## 📦 Instalação

```bash
composer require eduardoks98/helpers
```

## 🚀 Features

- ✅ **Validadores** - CPF, CNPJ
- ✅ **Formatadores** - Telefone, moeda, data
- ✅ **Helpers Gerais** - Strings, arrays, números
- ✅ **Framework Agnostic** - Funciona sem Laravel!

## 📖 Documentação Completa

Veja a [documentação completa](../../docs/packages/helpers.md) para exemplos detalhados.

## 🔧 Quick Start

### Validar CPF

```php
use function Eduardoks98\Helpers\checkCPF;

if (checkCPF('123.456.789-09')) {
    echo "CPF válido!";
}
```

### Formatar Telefone

```php
use function Eduardoks98\Helpers\formatPhoneNumber;

$phone = formatPhoneNumber('11987654321');
// Resultado: "(11) 98765-4321"
```

### Formatar Moeda

```php
use function Eduardoks98\Helpers\moneyFormat;

$price = moneyFormat(1234.56);
// Resultado: "R$ 1.234,56"
```

## 📄 License

MIT

## 👤 Author

Eduardo Steffens - [@eduardoks98](https://github.com/eduardoks98)
