<?php

namespace Eduardoks98\Banking\Services;

use Eduardoks98\Banking\Exceptions\BankingException;
use Eduardoks98\Banking\Enums\BoletoType;

/**
 * Brazilian Boleto (Bank Slip) Validation Service
 */
class BoletoService
{
    /**
     * Validate a boleto barcode or digitable line
     */
    public function validate(string $code): array
    {
        $code = preg_replace('/\D/', '', $code);

        $type = $this->detectType($code);
        if ($type === null) {
            return [
                'valid' => false,
                'code' => $code,
                'type' => null,
                'error' => 'Invalid boleto format',
            ];
        }

        $isValid = $this->validateChecksum($code, $type);

        $parsed = $isValid ? $this->parse($code, $type) : null;

        return [
            'valid' => $isValid,
            'code' => $code,
            'type' => $type->value,
            'type_label' => $type->label(),
            'parsed' => $parsed,
            'error' => $isValid ? null : 'Invalid checksum',
        ];
    }

    /**
     * Check if boleto is valid
     */
    public function isValid(string $code): bool
    {
        return $this->validate($code)['valid'];
    }

    /**
     * Detect boleto type from code
     */
    public function detectType(string $code): ?BoletoType
    {
        $code = preg_replace('/\D/', '', $code);

        // Barcode = 44 digits, Digitable line = 47 digits (bank) or 48 digits (concessionária)
        if (strlen($code) === 44 || strlen($code) === 47) {
            // Bank boleto starts with bank code (001-999)
            if (substr($code, 0, 1) !== '8') {
                return BoletoType::BANK;
            }
        }

        // Concessionária (utilities, taxes) starts with 8
        if (strlen($code) === 44 || strlen($code) === 48) {
            if (substr($code, 0, 1) === '8') {
                return BoletoType::UTILITY;
            }
        }

        return null;
    }

    /**
     * Convert digitable line to barcode
     */
    public function toBarcode(string $digitableLine): ?string
    {
        $code = preg_replace('/\D/', '', $digitableLine);
        $type = $this->detectType($code);

        if ($type === null) {
            return null;
        }

        if (strlen($code) === 44) {
            return $code; // Already a barcode
        }

        if ($type === BoletoType::BANK && strlen($code) === 47) {
            return $this->bankDigitableToBarcode($code);
        }

        if ($type === BoletoType::UTILITY && strlen($code) === 48) {
            return $this->utilityDigitableToBarcode($code);
        }

        return null;
    }

    /**
     * Convert barcode to digitable line
     */
    public function toDigitableLine(string $barcode): ?string
    {
        $code = preg_replace('/\D/', '', $barcode);
        $type = $this->detectType($code);

        if ($type === null || strlen($code) !== 44) {
            return null;
        }

        if ($type === BoletoType::BANK) {
            return $this->bankBarcodeToDigitable($code);
        }

        if ($type === BoletoType::UTILITY) {
            return $this->utilityBarcodeToDigitable($code);
        }

        return null;
    }

    /**
     * Parse boleto information
     */
    public function parse(string $code, ?BoletoType $type = null): ?array
    {
        $code = preg_replace('/\D/', '', $code);
        $type ??= $this->detectType($code);

        if ($type === null) {
            return null;
        }

        // Convert to barcode if digitable line
        $barcode = $this->toBarcode($code);
        if ($barcode === null) {
            return null;
        }

        if ($type === BoletoType::BANK) {
            return $this->parseBankBoleto($barcode);
        }

        return $this->parseUtilityBoleto($barcode);
    }

    /**
     * Get due date from boleto
     */
    public function getDueDate(string $code): ?\DateTime
    {
        $parsed = $this->parse($code);
        return $parsed['due_date'] ?? null;
    }

    /**
     * Get amount from boleto
     */
    public function getAmount(string $code): ?float
    {
        $parsed = $this->parse($code);
        return $parsed['amount'] ?? null;
    }

    /**
     * Format digitable line for display
     */
    public function formatDigitableLine(string $code): ?string
    {
        $code = preg_replace('/\D/', '', $code);
        $type = $this->detectType($code);

        // Convert to digitable if barcode
        if (strlen($code) === 44) {
            $code = $this->toDigitableLine($code);
            if ($code === null) return null;
        }

        if ($type === BoletoType::BANK && strlen($code) === 47) {
            // Format: AAAAA.BBBBB CCCCC.DDDDDD EEEEE.EEEEEE F GGGGGGGGGGGGGG
            return sprintf(
                '%s.%s %s.%s %s.%s %s %s',
                substr($code, 0, 5),
                substr($code, 5, 5),
                substr($code, 10, 5),
                substr($code, 15, 6),
                substr($code, 21, 5),
                substr($code, 26, 6),
                substr($code, 32, 1),
                substr($code, 33)
            );
        }

        if ($type === BoletoType::UTILITY && strlen($code) === 48) {
            // Format: AAAAAAAAAAA-B CCCCCCCCCCCC-D EEEEEEEEEEEE-F GGGGGGGGGGGG-H
            return sprintf(
                '%s-%s %s-%s %s-%s %s-%s',
                substr($code, 0, 11),
                substr($code, 11, 1),
                substr($code, 12, 11),
                substr($code, 23, 1),
                substr($code, 24, 11),
                substr($code, 35, 1),
                substr($code, 36, 11),
                substr($code, 47, 1)
            );
        }

        return $code;
    }

    /**
     * Validate bank boleto checksum
     */
    protected function validateBankChecksum(string $code): bool
    {
        if (!config('banking.boleto.validate_checksum')) {
            return true;
        }

        // Convert digitable to barcode
        if (strlen($code) === 47) {
            $barcode = $this->bankDigitableToBarcode($code);
            if ($barcode === null) return false;

            // Also validate the field checksums
            if (!$this->validateBankFieldChecksums($code)) {
                return false;
            }

            $code = $barcode;
        }

        if (strlen($code) !== 44) {
            return false;
        }

        // General check digit (position 4)
        $checkDigit = (int) $code[4];
        $toVerify = substr($code, 0, 4) . substr($code, 5);

        return $this->calculateMod11($toVerify) === $checkDigit;
    }

    /**
     * Validate utility boleto checksum
     */
    protected function validateUtilityChecksum(string $code): bool
    {
        if (!config('banking.boleto.validate_checksum')) {
            return true;
        }

        // Convert digitable to barcode
        if (strlen($code) === 48) {
            // Validate field checksums first
            if (!$this->validateUtilityFieldChecksums($code)) {
                return false;
            }

            $barcode = $this->utilityDigitableToBarcode($code);
            if ($barcode === null) return false;
            $code = $barcode;
        }

        if (strlen($code) !== 44) {
            return false;
        }

        // Determine mod type (position 2)
        $modType = (int) $code[2];
        $checkDigit = (int) $code[3];
        $toVerify = substr($code, 0, 3) . substr($code, 4);

        if ($modType === 6 || $modType === 7) {
            return $this->calculateMod10($toVerify) === $checkDigit;
        }

        return $this->calculateMod11Utility($toVerify) === $checkDigit;
    }

    /**
     * Validate checksum based on type
     */
    protected function validateChecksum(string $code, BoletoType $type): bool
    {
        return match ($type) {
            BoletoType::BANK => $this->validateBankChecksum($code),
            BoletoType::UTILITY => $this->validateUtilityChecksum($code),
        };
    }

    /**
     * Parse bank boleto (barcode)
     */
    protected function parseBankBoleto(string $barcode): array
    {
        $bankCode = substr($barcode, 0, 3);
        $currencyCode = substr($barcode, 3, 1);
        $checkDigit = substr($barcode, 4, 1);
        $dueDateFactor = (int) substr($barcode, 5, 4);
        $amount = (int) substr($barcode, 9, 10);
        $freeField = substr($barcode, 19);

        $dueDate = null;
        if ($dueDateFactor > 0) {
            $baseDate = new \DateTime('1997-10-07');
            $dueDate = $baseDate->modify("+{$dueDateFactor} days");
        }

        return [
            'bank_code' => $bankCode,
            'currency_code' => $currencyCode,
            'check_digit' => $checkDigit,
            'due_date' => $dueDate,
            'due_date_factor' => $dueDateFactor,
            'amount' => $amount / 100,
            'free_field' => $freeField,
            'barcode' => $barcode,
        ];
    }

    /**
     * Parse utility boleto (barcode)
     */
    protected function parseUtilityBoleto(string $barcode): array
    {
        $productId = substr($barcode, 0, 1);
        $segmentId = substr($barcode, 1, 1);
        $valueType = substr($barcode, 2, 1);
        $checkDigit = substr($barcode, 3, 1);
        $amount = (int) substr($barcode, 4, 11);
        $companyId = substr($barcode, 15, 4);
        $freeField = substr($barcode, 19);

        return [
            'product_id' => $productId,
            'segment_id' => $segmentId,
            'segment_name' => $this->getSegmentName($segmentId),
            'value_type' => $valueType,
            'check_digit' => $checkDigit,
            'amount' => $amount / 100,
            'company_id' => $companyId,
            'free_field' => $freeField,
            'barcode' => $barcode,
            'due_date' => null, // Utility boletos don't have standard due date
        ];
    }

    /**
     * Convert bank digitable line to barcode
     */
    protected function bankDigitableToBarcode(string $digitable): string
    {
        // Field 1: positions 1-3 (bank) + position 4 (currency) + positions 5-9 (free field part 1)
        // Field 2: positions 10-19 (free field part 2)
        // Field 3: positions 20-29 (free field part 3)
        // Check digit: position 30
        // Due date factor + amount: positions 31-44

        $field1 = substr($digitable, 0, 4) . substr($digitable, 4, 5);
        $field2 = substr($digitable, 10, 10);
        $field3 = substr($digitable, 21, 10);
        $checkDigit = substr($digitable, 32, 1);
        $field4 = substr($digitable, 33, 14);

        return substr($field1, 0, 4) . $checkDigit . substr($field4, 0, 4) .
               substr($field4, 4, 10) . substr($field1, 4, 5) .
               $field2 . $field3;
    }

    /**
     * Convert utility digitable line to barcode
     */
    protected function utilityDigitableToBarcode(string $digitable): string
    {
        // 4 fields of 12 characters each (11 + check digit)
        return substr($digitable, 0, 11) .
               substr($digitable, 12, 11) .
               substr($digitable, 24, 11) .
               substr($digitable, 36, 11);
    }

    /**
     * Convert bank barcode to digitable line
     */
    protected function bankBarcodeToDigitable(string $barcode): string
    {
        // Build field 1: bank (3) + currency (1) + free field (5)
        $field1 = substr($barcode, 0, 4) . substr($barcode, 19, 5);
        $field1 .= $this->calculateMod10($field1);

        // Build field 2: free field (10)
        $field2 = substr($barcode, 24, 10);
        $field2 .= $this->calculateMod10($field2);

        // Build field 3: free field (10)
        $field3 = substr($barcode, 34, 10);
        $field3 .= $this->calculateMod10($field3);

        // Field 4: check digit
        $field4 = substr($barcode, 4, 1);

        // Field 5: due date factor (4) + amount (10)
        $field5 = substr($barcode, 5, 14);

        return $field1 . $field2 . $field3 . $field4 . $field5;
    }

    /**
     * Convert utility barcode to digitable line
     */
    protected function utilityBarcodeToDigitable(string $barcode): string
    {
        $modType = (int) $barcode[2];
        $calcMethod = ($modType === 6 || $modType === 7) ? 'calculateMod10' : 'calculateMod11Utility';

        $result = '';
        for ($i = 0; $i < 4; $i++) {
            $field = substr($barcode, $i * 11, 11);
            $result .= $field . $this->$calcMethod($field);
        }

        return $result;
    }

    /**
     * Validate bank field checksums
     */
    protected function validateBankFieldChecksums(string $digitable): bool
    {
        // Field 1 (positions 0-9, check at 9)
        $field1 = substr($digitable, 0, 9);
        if ($this->calculateMod10($field1) !== (int) $digitable[9]) {
            return false;
        }

        // Field 2 (positions 10-20, check at 20)
        $field2 = substr($digitable, 10, 10);
        if ($this->calculateMod10($field2) !== (int) $digitable[20]) {
            return false;
        }

        // Field 3 (positions 21-31, check at 31)
        $field3 = substr($digitable, 21, 10);
        if ($this->calculateMod10($field3) !== (int) $digitable[31]) {
            return false;
        }

        return true;
    }

    /**
     * Validate utility field checksums
     */
    protected function validateUtilityFieldChecksums(string $digitable): bool
    {
        $modType = (int) $digitable[2];
        $calcMethod = ($modType === 6 || $modType === 7) ? 'calculateMod10' : 'calculateMod11Utility';

        for ($i = 0; $i < 4; $i++) {
            $start = $i * 12;
            $field = substr($digitable, $start, 11);
            $checkDigit = (int) $digitable[$start + 11];

            if ($this->$calcMethod($field) !== $checkDigit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate MOD 10 check digit
     */
    protected function calculateMod10(string $number): int
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $result = (int) $number[$i] * $multiplier;
            $sum += ($result > 9) ? (int) ($result / 10) + ($result % 10) : $result;
            $multiplier = ($multiplier === 2) ? 1 : 2;
        }

        $remainder = $sum % 10;
        return ($remainder === 0) ? 0 : 10 - $remainder;
    }

    /**
     * Calculate MOD 11 check digit (for bank boleto)
     */
    protected function calculateMod11(string $number): int
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += (int) $number[$i] * $multiplier;
            $multiplier = ($multiplier === 9) ? 2 : $multiplier + 1;
        }

        $remainder = $sum % 11;
        $result = 11 - $remainder;

        if ($result === 0 || $result === 10 || $result === 11) {
            return 1;
        }

        return $result;
    }

    /**
     * Calculate MOD 11 for utility boleto
     */
    protected function calculateMod11Utility(string $number): int
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += (int) $number[$i] * $multiplier;
            $multiplier = ($multiplier === 9) ? 2 : $multiplier + 1;
        }

        $remainder = $sum % 11;

        if ($remainder === 0 || $remainder === 1) {
            return 0;
        }

        if ($remainder === 10) {
            return 1;
        }

        return 11 - $remainder;
    }

    /**
     * Get segment name
     */
    protected function getSegmentName(string $segmentId): string
    {
        return match ($segmentId) {
            '1' => 'Prefeituras',
            '2' => 'Saneamento',
            '3' => 'Energia Elétrica e Gás',
            '4' => 'Telecomunicações',
            '5' => 'Órgãos Governamentais',
            '6' => 'Carnes e Assemelhados',
            '7' => 'Multas de Trânsito',
            '9' => 'Uso Exclusivo do Banco',
            default => 'Outros',
        };
    }
}
