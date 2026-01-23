# Banking Package

> Brazilian banking utilities - PIX validation, bank codes, boleto validation, and FEBRABAN integration.

## Overview

The `eduardoks98/banking` package provides comprehensive Brazilian banking utilities including PIX key validation and QR code generation, bank code lookup, and boleto validation.

## Installation

```bash
composer require eduardoks98/banking
```

## Configuration

### Environment Variables

```env
# Cache
BANKING_CACHE_ENABLED=true
BANKING_CACHE_TTL=86400

# Bank list source (static or api)
BANKING_LIST_SOURCE=static

# PIX validation options
PIX_VALIDATE_CPF_CNPJ=true
PIX_VALIDATE_EMAIL=true
PIX_VALIDATE_PHONE=true

# Boleto validation
BOLETO_VALIDATE_CHECKSUM=true
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\Banking\BankingServiceProvider" --tag="config"
```

## Usage

### PIX Key Validation

```php
use Eduardoks98\Banking\Services\PixService;

$pix = app(PixService::class);

// Validate PIX key and detect type
$result = $pix->validate('123.456.789-09');
// Returns:
// [
//     'valid' => true,
//     'key' => '12345678909',
//     'type' => 'cpf',
//     'type_label' => 'CPF',
//     'formatted' => '123.456.789-09',
// ]

// Quick validation
$isValid = $pix->isValid('email@example.com');

// Detect key type
$type = $pix->detectKeyType('11987654321'); // PixKeyType::PHONE

// Validate specific types
$pix->validateCpf('12345678909');
$pix->validateCnpj('12345678000195');
$pix->validatePhone('+5511987654321');
$pix->validateEmail('email@example.com');
$pix->validateEvp('123e4567-e89b-12d3-a456-426614174000');
```

### PIX Copy & Paste Generation

```php
// Generate PIX Copy & Paste (EMV format)
$payload = $pix->generatePixCopyPaste([
    'key' => '12345678909',
    'merchant_name' => 'João Silva',
    'merchant_city' => 'São Paulo',
    'amount' => 100.50,
    'transaction_id' => 'ORDER123',
    'description' => 'Pagamento',
]);
// Result: EMV format string for QR Code
```

### PIX Payload Parsing

```php
$data = $pix->parsePixCopyPaste($payload);
// Returns: pix_key, merchant_name, merchant_city, amount, transaction_id
```

### Bank Codes

```php
use Eduardoks98\Banking\Services\BankService;

$bank = app(BankService::class);

// Get all banks
$banks = $bank->getAll();

// Find by code
$bb = $bank->findByCode('001');

// Find by ISPB
$nubank = $bank->findByIspb('18236120');

// Find by name (partial match)
$results = $bank->findByName('itau');

// Check if exists
$exists = $bank->exists('341');

// Get specific info
$name = $bank->getName('237');
$shortName = $bank->getShortName('237');
$ispb = $bank->getIspb('237');

// Get major/digital banks
$majorBanks = $bank->getMajorBanks();
$digitalBanks = $bank->getDigitalBanks();

// Search
$results = $bank->search('nubank');
```

### Boleto Validation

```php
use Eduardoks98\Banking\Services\BoletoService;

$boleto = app(BoletoService::class);

// Validate boleto
$result = $boleto->validate($digitableLine);

// Quick validation
$isValid = $boleto->isValid($code);

// Detect type
$type = $boleto->detectType($code); // BoletoType::BANK or BoletoType::UTILITY

// Convert between formats
$barcode = $boleto->toBarcode($digitableLine);
$digitableLine = $boleto->toDigitableLine($barcode);

// Parse boleto info
$info = $boleto->parse($code);

// Get specific info
$dueDate = $boleto->getDueDate($code);
$amount = $boleto->getAmount($code);

// Format for display
$formatted = $boleto->formatDigitableLine($code);
```

## PIX Key Types

| Type | Description | Example |
|------|-------------|---------|
| `cpf` | CPF (11 digits) | 123.456.789-09 |
| `cnpj` | CNPJ (14 digits) | 12.345.678/0001-95 |
| `email` | E-mail address | email@example.com |
| `phone` | Phone with DDD | +55 11 98765-4321 |
| `evp` | Random key (UUID) | 123e4567-e89b-... |

## Boleto Types

| Type | Barcode | Digitable Line |
|------|---------|----------------|
| Bank | 44 digits | 47 digits |
| Utility | 44 digits | 48 digits |

## Major Banks

| Code | Bank |
|------|------|
| 001 | Banco do Brasil |
| 033 | Santander |
| 077 | Banco Inter |
| 104 | Caixa Econômica Federal |
| 237 | Bradesco |
| 260 | Nubank |
| 341 | Itaú |

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [PIX - Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix)
- [FEBRABAN](https://portal.febraban.org.br/)
- [Brasil API](https://brasilapi.com.br/)
