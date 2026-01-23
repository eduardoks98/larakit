<?php

namespace Eduardoks98\Banking\Services;

use Eduardoks98\Banking\Exceptions\BankingException;
use Eduardoks98\Banking\Enums\PixKeyType;

/**
 * PIX Key Validation and QR Code Service
 */
class PixService
{
    /**
     * Validate a PIX key and detect its type
     */
    public function validate(string $key): array
    {
        $key = $this->normalizeKey($key);
        $type = $this->detectKeyType($key);

        if ($type === null) {
            return [
                'valid' => false,
                'key' => $key,
                'type' => null,
                'formatted' => null,
                'error' => 'Invalid PIX key format',
            ];
        }

        $isValid = $this->validateByType($key, $type);

        return [
            'valid' => $isValid,
            'key' => $key,
            'type' => $type->value,
            'type_label' => $type->label(),
            'formatted' => $isValid ? $this->formatKey($key, $type) : null,
            'error' => $isValid ? null : "Invalid {$type->label()} format",
        ];
    }

    /**
     * Validate a PIX key
     */
    public function isValid(string $key): bool
    {
        return $this->validate($key)['valid'];
    }

    /**
     * Detect PIX key type
     */
    public function detectKeyType(string $key): ?PixKeyType
    {
        $key = $this->normalizeKey($key);

        // EVP (Random Key) - UUID format
        if ($this->isEvpFormat($key)) {
            return PixKeyType::EVP;
        }

        // CPF - 11 digits
        if ($this->isCpfFormat($key)) {
            return PixKeyType::CPF;
        }

        // CNPJ - 14 digits
        if ($this->isCnpjFormat($key)) {
            return PixKeyType::CNPJ;
        }

        // Phone - starts with +55 or Brazilian format
        if ($this->isPhoneFormat($key)) {
            return PixKeyType::PHONE;
        }

        // Email
        if ($this->isEmailFormat($key)) {
            return PixKeyType::EMAIL;
        }

        return null;
    }

    /**
     * Validate CPF as PIX key
     */
    public function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Check for invalid patterns
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validate check digits
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$t] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CNPJ as PIX key
     */
    public function validateCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Check for invalid patterns
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validate check digits
        $size = strlen($cnpj) - 2;
        $numbers = substr($cnpj, 0, $size);
        $digits = substr($cnpj, $size);

        $sum = 0;
        $pos = $size - 7;
        for ($i = $size; $i >= 1; $i--) {
            $sum += $numbers[$size - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[0]) {
            return false;
        }

        $size++;
        $numbers = substr($cnpj, 0, $size);
        $sum = 0;
        $pos = $size - 7;
        for ($i = $size; $i >= 1; $i--) {
            $sum += $numbers[$size - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }
        $result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
        if ($result != $digits[1]) {
            return false;
        }

        return true;
    }

    /**
     * Validate phone as PIX key
     */
    public function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Remove country code if present
        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            $phone = substr($phone, 2);
        }

        // Brazilian phone: DDD (2 digits) + number (8 or 9 digits)
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return false;
        }

        // DDD validation (11-99)
        $ddd = (int) substr($phone, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            return false;
        }

        // Mobile phones start with 9
        if (strlen($phone) === 11 && $phone[2] !== '9') {
            return false;
        }

        return true;
    }

    /**
     * Validate email as PIX key
     */
    public function validateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Max 77 characters for PIX email
        if (strlen($email) > 77) {
            return false;
        }

        return true;
    }

    /**
     * Validate EVP (random key) as PIX key
     */
    public function validateEvp(string $evp): bool
    {
        // UUID v4 format
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return (bool) preg_match($pattern, $evp);
    }

    /**
     * Parse PIX Copy & Paste (EMV format)
     */
    public function parsePixCopyPaste(string $payload): array
    {
        $data = [];
        $pos = 0;
        $length = strlen($payload);

        while ($pos < $length) {
            if ($pos + 4 > $length) break;

            $id = substr($payload, $pos, 2);
            $size = (int) substr($payload, $pos + 2, 2);
            $value = substr($payload, $pos + 4, $size);

            $data[$id] = $value;

            // Parse nested merchant account info
            if ($id === '26' || $id === '27') {
                $data[$id . '_parsed'] = $this->parseNestedData($value);
            }

            $pos += 4 + $size;
        }

        return [
            'raw' => $payload,
            'parsed' => $data,
            'pix_key' => $data['26_parsed']['01'] ?? null,
            'merchant_name' => $data['59'] ?? null,
            'merchant_city' => $data['60'] ?? null,
            'amount' => isset($data['54']) ? (float) $data['54'] : null,
            'transaction_id' => $data['62_parsed']['05'] ?? ($data['05'] ?? null),
        ];
    }

    /**
     * Generate PIX Copy & Paste (EMV format)
     */
    public function generatePixCopyPaste(array $params): string
    {
        $key = $params['key'] ?? throw new BankingException('PIX key is required');
        $merchantName = $params['merchant_name'] ?? 'MERCHANT';
        $merchantCity = $params['merchant_city'] ?? 'CIDADE';
        $amount = $params['amount'] ?? null;
        $transactionId = $params['transaction_id'] ?? '***';
        $description = $params['description'] ?? null;

        // Validate key
        $validation = $this->validate($key);
        if (!$validation['valid']) {
            throw new BankingException('Invalid PIX key: ' . $validation['error']);
        }

        $payload = '';

        // 00 - Payload Format Indicator
        $payload .= $this->buildEmvField('00', '01');

        // 01 - Point of Initiation Method (12 = dynamic, allows only one use)
        if ($amount !== null || $transactionId !== '***') {
            $payload .= $this->buildEmvField('01', '12');
        }

        // 26 - Merchant Account Information (PIX)
        $merchantAccount = '';
        $merchantAccount .= $this->buildEmvField('00', 'br.gov.bcb.pix');
        $merchantAccount .= $this->buildEmvField('01', $key);
        if ($description) {
            $merchantAccount .= $this->buildEmvField('02', substr($description, 0, 72));
        }
        $payload .= $this->buildEmvField('26', $merchantAccount);

        // 52 - Merchant Category Code
        $payload .= $this->buildEmvField('52', '0000');

        // 53 - Transaction Currency (986 = BRL)
        $payload .= $this->buildEmvField('53', '986');

        // 54 - Transaction Amount (optional)
        if ($amount !== null && $amount > 0) {
            $payload .= $this->buildEmvField('54', number_format($amount, 2, '.', ''));
        }

        // 58 - Country Code
        $payload .= $this->buildEmvField('58', 'BR');

        // 59 - Merchant Name
        $payload .= $this->buildEmvField('59', strtoupper(substr($this->normalizeString($merchantName), 0, 25)));

        // 60 - Merchant City
        $payload .= $this->buildEmvField('60', strtoupper(substr($this->normalizeString($merchantCity), 0, 15)));

        // 62 - Additional Data Field Template
        if ($transactionId) {
            $additionalData = $this->buildEmvField('05', substr($transactionId, 0, 25));
            $payload .= $this->buildEmvField('62', $additionalData);
        }

        // 63 - CRC16 (calculated at the end)
        $payload .= '6304';
        $crc = $this->calculateCrc16($payload);
        $payload .= strtoupper(dechex($crc));

        return $payload;
    }

    /**
     * Normalize PIX key
     */
    protected function normalizeKey(string $key): string
    {
        $key = trim($key);

        // For CPF/CNPJ, remove formatting
        if (preg_match('/^[\d.\-\/]+$/', $key)) {
            return preg_replace('/\D/', '', $key);
        }

        // For phone, keep + if present
        if (str_starts_with($key, '+')) {
            return '+' . preg_replace('/\D/', '', $key);
        }

        // For email, lowercase
        if (str_contains($key, '@')) {
            return strtolower($key);
        }

        return $key;
    }

    /**
     * Format PIX key for display
     */
    protected function formatKey(string $key, PixKeyType $type): string
    {
        return match ($type) {
            PixKeyType::CPF => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $key),
            PixKeyType::CNPJ => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $key),
            PixKeyType::PHONE => $this->formatPhone($key),
            default => $key,
        };
    }

    /**
     * Format phone number
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 13 && str_starts_with($phone, '55')) {
            $phone = substr($phone, 2);
        }

        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '+55 ($1) $2-$3', $phone);
        }

        if (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '+55 ($1) $2-$3', $phone);
        }

        return $phone;
    }

    /**
     * Validate key by type
     */
    protected function validateByType(string $key, PixKeyType $type): bool
    {
        return match ($type) {
            PixKeyType::CPF => !config('banking.pix.validate_cpf_cnpj') || $this->validateCpf($key),
            PixKeyType::CNPJ => !config('banking.pix.validate_cpf_cnpj') || $this->validateCnpj($key),
            PixKeyType::PHONE => !config('banking.pix.validate_phone') || $this->validatePhone($key),
            PixKeyType::EMAIL => !config('banking.pix.validate_email') || $this->validateEmail($key),
            PixKeyType::EVP => $this->validateEvp($key),
        };
    }

    /**
     * Check if key is EVP format
     */
    protected function isEvpFormat(string $key): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key);
    }

    /**
     * Check if key is CPF format
     */
    protected function isCpfFormat(string $key): bool
    {
        $key = preg_replace('/\D/', '', $key);
        return strlen($key) === 11;
    }

    /**
     * Check if key is CNPJ format
     */
    protected function isCnpjFormat(string $key): bool
    {
        $key = preg_replace('/\D/', '', $key);
        return strlen($key) === 14;
    }

    /**
     * Check if key is phone format
     */
    protected function isPhoneFormat(string $key): bool
    {
        $key = preg_replace('/\D/', '', $key);

        // With country code
        if (strlen($key) === 13 && str_starts_with($key, '55')) {
            return true;
        }

        // Without country code
        return strlen($key) >= 10 && strlen($key) <= 11;
    }

    /**
     * Check if key is email format
     */
    protected function isEmailFormat(string $key): bool
    {
        return (bool) filter_var($key, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Build EMV field
     */
    protected function buildEmvField(string $id, string $value): string
    {
        $length = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $length . $value;
    }

    /**
     * Parse nested EMV data
     */
    protected function parseNestedData(string $data): array
    {
        $result = [];
        $pos = 0;
        $length = strlen($data);

        while ($pos < $length) {
            if ($pos + 4 > $length) break;

            $id = substr($data, $pos, 2);
            $size = (int) substr($data, $pos + 2, 2);
            $value = substr($data, $pos + 4, $size);

            $result[$id] = $value;
            $pos += 4 + $size;
        }

        return $result;
    }

    /**
     * Calculate CRC16 CCITT
     */
    protected function calculateCrc16(string $payload): int
    {
        $polynomial = 0x1021;
        $result = 0xFFFF;

        foreach (str_split($payload) as $char) {
            $result ^= (ord($char) << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($result & 0x8000) {
                    $result = ($result << 1) ^ $polynomial;
                } else {
                    $result <<= 1;
                }
            }
            $result &= 0xFFFF;
        }

        return $result;
    }

    /**
     * Normalize string (remove accents)
     */
    protected function normalizeString(string $string): string
    {
        $accents = ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ'];
        $replace = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n'];

        return str_replace($accents, $replace, strtolower($string));
    }
}
