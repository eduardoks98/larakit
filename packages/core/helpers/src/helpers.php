<?php

use Eduardoks98\Helpers\Validators\{CpfValidator, CnpjValidator, DocumentValidator};
use Eduardoks98\Helpers\Formatters\{PhoneFormatter, MoneyFormatter, DateFormatter};

if (!function_exists('checkCPF')) {
    /**
     * Valida um CPF.
     *
     * @param string $cpf
     * @return bool
     */
    function checkCPF(string $cpf): bool
    {
        return CpfValidator::validate($cpf);
    }
}

if (!function_exists('checkCNPJ')) {
    /**
     * Valida um CNPJ.
     *
     * @param string $cnpj
     * @return bool
     */
    function checkCNPJ(string $cnpj): bool
    {
        return CnpjValidator::validate($cnpj);
    }
}

if (!function_exists('checkDocument')) {
    /**
     * Valida CPF ou CNPJ automaticamente.
     *
     * @param string $document
     * @return bool
     */
    function checkDocument(string $document): bool
    {
        return DocumentValidator::validate($document);
    }
}

if (!function_exists('formatPhoneNumber')) {
    /**
     * Formata um número de telefone brasileiro.
     *
     * @param string $phone
     * @return string
     */
    function formatPhoneNumber(string $phone): string
    {
        return PhoneFormatter::format($phone);
    }
}

if (!function_exists('moneyFormat')) {
    /**
     * Formata um valor monetário em reais.
     *
     * @param float $amount
     * @return string
     */
    function moneyFormat(float $amount): string
    {
        return MoneyFormatter::format($amount);
    }
}

if (!function_exists('formatarCpfCnpj')) {
    /**
     * Adiciona zeros à esquerda em CPF/CNPJ.
     *
     * @param string|int $number
     * @param int $length
     * @return string
     */
    function formatarCpfCnpj($number, int $length = 14): string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('removerCaracteres')) {
    /**
     * Remove caracteres especiais deixando apenas números.
     *
     * @param string $string
     * @return string
     */
    function removerCaracteres(string $string): string
    {
        return preg_replace('/[^0-9]/', '', $string);
    }
}

if (!function_exists('isEmpty')) {
    /**
     * Verifica se um valor está vazio (enhanced empty check).
     *
     * @param mixed $value
     * @return bool
     */
    function isEmpty($value): bool
    {
        if (is_string($value)) {
            return trim($value) === '';
        }

        return empty($value);
    }
}

if (!function_exists('generateRandomToken')) {
    /**
     * Gera um token aleatório seguro.
     *
     * @param int $length
     * @param array $options
     * @return string
     */
    function generateRandomToken(int $length = 32, array $options = []): string
    {
        $defaults = [
            'uppercase' => true,
            'lowercase' => true,
            'numbers' => true,
            'special' => false,
        ];

        $options = array_merge($defaults, $options);

        $characters = '';
        if ($options['uppercase']) $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($options['lowercase']) $characters .= 'abcdefghijklmnopqrstuvwxyz';
        if ($options['numbers']) $characters .= '0123456789';
        if ($options['special']) $characters .= '!@#$%^&*()-_=+[]{}|;:,.<>?';

        if (empty($characters)) {
            throw new \InvalidArgumentException('At least one character type must be enabled');
        }

        $token = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[random_int(0, $max)];
        }

        return $token;
    }
}

if (!function_exists('convertBytesTo')) {
    /**
     * Converte bytes para formato legível (KB, MB, GB).
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    function convertBytesTo(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
