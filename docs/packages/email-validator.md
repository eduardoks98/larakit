# Email Validator Package

> Advanced email validation with syntax, DNS, MX records, disposable email detection, and quality scoring.

## Overview

The `eduardoks98/email-validator` package provides comprehensive email validation including RFC 5322 syntax validation, DNS checks, disposable email detection, and quality scoring.

## Installation

```bash
composer require eduardoks98/email-validator
```

## Configuration

### Environment Variables

```env
# Enable/disable checks
EMAIL_CHECK_SYNTAX=true
EMAIL_CHECK_DNS=true
EMAIL_CHECK_MX=true
EMAIL_CHECK_DISPOSABLE=true
EMAIL_CHECK_ROLE=true
EMAIL_CHECK_SMTP=false

# Cache
EMAIL_VALIDATOR_CACHE_ENABLED=true
EMAIL_VALIDATOR_CACHE_TTL=3600
```

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\EmailValidator\EmailValidatorServiceProvider" --tag="config"
```

## Usage

### Full Validation

```php
use Eduardoks98\EmailValidator\Services\EmailValidatorService;

$validator = app(EmailValidatorService::class);

$result = $validator->validate('user@example.com');
// Returns:
// [
//     'email' => 'user@example.com',
//     'valid' => true,
//     'score' => 85,
//     'quality' => 'good',
//     'local_part' => 'user',
//     'domain' => 'example.com',
//     'checks' => [
//         'syntax' => true,
//         'dns' => true,
//         'mx' => true,
//         'disposable' => false,
//         'role' => false,
//     ],
//     'is_disposable' => false,
//     'is_role' => false,
//     'is_trusted' => false,
//     'mx_records' => [...],
//     'suggestions' => [],
// ]
```

### Quick Checks

```php
// Syntax only
$isValid = $validator->isValidSyntax('user@example.com');

// Disposable email check
$isDisposable = $validator->isEmailDisposable('user@tempmail.com');

// Role-based email check
$isRole = $validator->isEmailRoleBased('admin@example.com');

// MX records check
$hasMx = $validator->hasMxRecords('gmail.com');
```

### Batch Validation

```php
$results = $validator->validateBatch([
    'user1@gmail.com',
    'user2@tempmail.com',
    'invalid-email',
]);
```

### Custom Options

```php
$result = $validator->validate('user@example.com', [
    'syntax' => true,
    'dns' => true,
    'mx' => true,
    'disposable' => false, // Skip disposable check
    'role' => false,       // Skip role check
    'smtp' => true,        // Enable SMTP check
]);
```

## Quality Scores

| Score | Quality | Description |
|-------|---------|-------------|
| 90-100 | Excellent | All checks passed, trusted domain |
| 70-89 | Good | Most checks passed |
| 50-69 | Acceptable | Some concerns |
| 30-49 | Poor | Multiple issues |
| 0-29 | Invalid | Major problems |

## Detected Issues

### Disposable Emails (40+ domains)
- 10minutemail.com
- guerrillamail.com
- mailinator.com
- tempmail.com
- yopmail.com
- And more...

### Role-Based Prefixes
- admin, administrator
- info, contact, support
- sales, marketing
- noreply, no-reply
- webmaster, postmaster

### Trusted Domains
- gmail.com, googlemail.com
- outlook.com, hotmail.com
- yahoo.com, yahoo.com.br
- icloud.com
- protonmail.com
- uol.com.br, bol.com.br

## SMTP Verification

```env
EMAIL_CHECK_SMTP=true
EMAIL_SMTP_TIMEOUT=10
EMAIL_SMTP_FROM=verify@yourdomain.com
```

**Note**: SMTP verification is slow and may be blocked by some servers.

## Error Handling

```php
use Eduardoks98\EmailValidator\Exceptions\EmailValidationException;

try {
    $result = $validator->validate($email);
} catch (EmailValidationException $e) {
    echo $e->getMessage();
    echo $e->getEmail();
    echo $e->getFailedCheck();
}
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [RFC 5322](https://tools.ietf.org/html/rfc5322) - Email format specification
