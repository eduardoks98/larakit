# Banking Package

> Brazilian banking utilities - PIX validation, bank codes, boleto validation, and FEBRABAN integration.

## Features

- PIX key validation (CPF, CNPJ, email, phone, EVP)
- PIX QR Code/Copy & Paste generation (EMV format)
- PIX payload parsing
- Brazilian bank codes database (FEBRABAN)
- Bank lookup by code, ISPB, or name
- Boleto barcode validation
- Boleto digitable line parsing
- Support for bank and utility boletos
- Brasil API integration (optional)
- Response caching

## Installation

```bash
composer require eduardoks98/banking
```

## Configuration

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\Banking\BankingServiceProvider" --tag="config"
```

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
//     'error' => null,
// ]

// Quick validation
$isValid = $pix->isValid('email@example.com'); // true

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
    'key' => '12345678909',           // PIX key (required)
    'merchant_name' => 'João Silva',   // Merchant name
    'merchant_city' => 'São Paulo',    // City
    'amount' => 100.50,                // Amount (optional)
    'transaction_id' => 'ORDER123',    // Transaction ID (optional)
    'description' => 'Pagamento',      // Description (optional)
]);

// Result: EMV format string for QR Code
// 00020126580014br.gov.bcb.pix0111123456789095204000053039865406100.505802BR5910JOAO SILVA6009SAO PAULO62130509ORDER1236304XXXX
```

### PIX Payload Parsing

```php
// Parse PIX Copy & Paste
$data = $pix->parsePixCopyPaste($payload);
// Returns:
// [
//     'pix_key' => '12345678909',
//     'merchant_name' => 'JOAO SILVA',
//     'merchant_city' => 'SAO PAULO',
//     'amount' => 100.50,
//     'transaction_id' => 'ORDER123',
// ]
```

### Bank Codes

```php
use Eduardoks98\Banking\Services\BankService;

$bank = app(BankService::class);

// Get all banks
$banks = $bank->getAll();

// Find by code
$bb = $bank->findByCode('001');
// Returns:
// [
//     'name' => 'Banco do Brasil S.A.',
//     'short_name' => 'BB',
//     'ispb' => '00000000',
// ]

// Find by ISPB
$nubank = $bank->findByIspb('18236120');

// Find by name (partial match)
$results = $bank->findByName('itau');

// Check if exists
$exists = $bank->exists('341'); // true

// Get specific info
$name = $bank->getName('237'); // "Banco Bradesco S.A."
$shortName = $bank->getShortName('237'); // "Bradesco"
$ispb = $bank->getIspb('237'); // "60746948"

// Get major banks
$majorBanks = $bank->getMajorBanks();

// Get digital banks
$digitalBanks = $bank->getDigitalBanks();

// Search (by code, ISPB, or name)
$results = $bank->search('nubank');
```

### Boleto Validation

```php
use Eduardoks98\Banking\Services\BoletoService;

$boleto = app(BoletoService::class);

// Validate boleto (barcode or digitable line)
$result = $boleto->validate('23793.38128 60000.000003 00000.000400 1 84340000001000');
// Returns:
// [
//     'valid' => true,
//     'type' => 'bank',
//     'type_label' => 'Boleto Bancário',
//     'parsed' => [
//         'bank_code' => '237',
//         'due_date' => DateTime,
//         'amount' => 10.00,
//         ...
//     ],
// ]

// Quick validation
$isValid = $boleto->isValid('23793381286000000000300000000400184340000001000');

// Detect type
$type = $boleto->detectType($code); // BoletoType::BANK or BoletoType::UTILITY

// Convert between formats
$barcode = $boleto->toBarcode($digitableLine);
$digitableLine = $boleto->toDigitableLine($barcode);

// Parse boleto info
$info = $boleto->parse($code);
// Returns: bank_code, due_date, amount, etc.

// Get specific info
$dueDate = $boleto->getDueDate($code);
$amount = $boleto->getAmount($code);

// Format for display
$formatted = $boleto->formatDigitableLine($code);
// Returns: "23793.38128 60000.000003 00000.000400 1 84340000001000"
```

## PIX Key Types

| Type | Description | Example |
|------|-------------|---------|
| `cpf` | CPF (11 digits) | 123.456.789-09 |
| `cnpj` | CNPJ (14 digits) | 12.345.678/0001-95 |
| `email` | E-mail address | email@example.com |
| `phone` | Phone with DDD | +55 11 98765-4321 |
| `evp` | Random key (UUID) | 123e4567-e89b-12d3-a456-426614174000 |

## Boleto Types

| Type | Description | Barcode | Digitable |
|------|-------------|---------|-----------|
| `bank` | Bank boleto | 44 digits | 47 digits |
| `utility` | Utility boleto | 44 digits | 48 digits |

## Major Banks Included

| Code | Bank |
|------|------|
| 001 | Banco do Brasil |
| 033 | Santander |
| 077 | Banco Inter |
| 104 | Caixa Econômica Federal |
| 237 | Bradesco |
| 260 | Nubank |
| 290 | PagSeguro |
| 323 | Mercado Pago |
| 336 | C6 Bank |
| 341 | Itaú |
| 380 | PicPay |

## API Integration

To fetch bank list from Brasil API instead of static list:

```env
BANKING_LIST_SOURCE=api
```

This uses [Brasil API](https://brasilapi.com.br/) as the data source.

## Error Handling

```php
use Eduardoks98\Banking\Exceptions\BankingException;

try {
    $payload = $pix->generatePixCopyPaste([
        'key' => 'invalid-key',
        'merchant_name' => 'Test',
        'merchant_city' => 'City',
    ]);
} catch (BankingException $e) {
    echo $e->getMessage(); // "Invalid PIX key: Invalid PIX key format"
    echo $e->getOperation(); // "pix_generation"
    print_r($e->getContext()); // ['key' => 'invalid-key', ...]
}
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [PIX - Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix)
- [FEBRABAN](https://portal.febraban.org.br/)
- [Brasil API](https://brasilapi.com.br/)

## License

MIT License
