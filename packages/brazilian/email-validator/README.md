# Email Validator Package

> Advanced email validation with syntax, DNS, MX records, disposable email detection, and quality scoring.

## Features

- Syntax validation (RFC 5322)
- DNS verification
- MX records check
- Disposable email detection (40+ domains)
- Role-based email detection
- Trusted domain recognition
- Quality scoring (0-100)
- SMTP verification (optional)
- Batch validation
- Response caching

## Installation

```bash
composer require eduardoks98/email-validator
```

## Configuration

### Publish Config

```bash
php artisan vendor:publish --provider="Eduardoks98\EmailValidator\EmailValidatorServiceProvider" --tag="config"
```

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

### Quick Syntax Check

```php
$isValid = $validator->isValidSyntax('user@example.com');
// Returns: true
```

### Disposable Email Check

```php
$isDisposable = $validator->isEmailDisposable('user@tempmail.com');
// Returns: true
```

### Role-Based Email Check

```php
$isRole = $validator->isEmailRoleBased('admin@example.com');
// Returns: true
```

### Check MX Records

```php
$hasMx = $validator->hasMxRecords('gmail.com');
// Returns: true
```

### Batch Validation

```php
$results = $validator->validateBatch([
    'user1@gmail.com',
    'user2@tempmail.com',
    'invalid-email',
]);
// Returns array of validation results
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

### Disposable Emails

40+ known disposable email providers:
- 10minutemail.com
- guerrillamail.com
- mailinator.com
- tempmail.com
- yopmail.com
- And more...

### Role-Based Prefixes

Emails starting with:
- admin, administrator
- info, contact, support
- sales, marketing
- noreply, no-reply
- webmaster, postmaster
- And more...

### Trusted Domains

Major email providers:
- gmail.com, googlemail.com
- outlook.com, hotmail.com
- yahoo.com, yahoo.com.br
- icloud.com
- protonmail.com
- uol.com.br, bol.com.br
- And more...

## SMTP Verification

Enable SMTP verification for deliverability check:

```env
EMAIL_CHECK_SMTP=true
EMAIL_SMTP_TIMEOUT=10
EMAIL_SMTP_FROM=verify@yourdomain.com
```

**Note**: SMTP verification is slow and may be blocked by some servers.

## Custom Validation

```php
// Override default checks
$result = $validator->validate('user@example.com', [
    'syntax' => true,
    'dns' => true,
    'mx' => true,
    'disposable' => false, // Skip disposable check
    'role' => false,       // Skip role check
    'smtp' => true,        // Enable SMTP check
]);
```

## Error Handling

```php
// Check if valid
if ($result['valid']) {
    // Email is valid
}

// Check quality
if ($result['quality'] === 'excellent' || $result['quality'] === 'good') {
    // High quality email
}

// Check score
if ($result['score'] >= 70) {
    // Acceptable quality
}

// Get suggestions
foreach ($result['suggestions'] as $suggestion) {
    echo $suggestion;
}
```

## Dependencies

- `guzzlehttp/guzzle` ^7.0
- `eduardoks98/base-api` ^1.0

## Related

- [RFC 5322](https://tools.ietf.org/html/rfc5322) - Email format specification

## License

MIT License
