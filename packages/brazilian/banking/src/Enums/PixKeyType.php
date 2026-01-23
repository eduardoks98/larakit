<?php

namespace Eduardoks98\Banking\Enums;

/**
 * PIX Key Types
 */
enum PixKeyType: string
{
    case CPF = 'cpf';
    case CNPJ = 'cnpj';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case EVP = 'evp';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::CPF => 'CPF',
            self::CNPJ => 'CNPJ',
            self::EMAIL => 'E-mail',
            self::PHONE => 'Telefone',
            self::EVP => 'Chave Aleatória',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::CPF => 'Cadastro de Pessoa Física (11 dígitos)',
            self::CNPJ => 'Cadastro Nacional de Pessoa Jurídica (14 dígitos)',
            self::EMAIL => 'Endereço de e-mail válido',
            self::PHONE => 'Número de telefone com DDD (+55 XX XXXXX-XXXX)',
            self::EVP => 'Chave aleatória no formato UUID',
        };
    }

    /**
     * Get example
     */
    public function example(): string
    {
        return match ($this) {
            self::CPF => '123.456.789-09',
            self::CNPJ => '12.345.678/0001-95',
            self::EMAIL => 'exemplo@email.com',
            self::PHONE => '+55 11 98765-4321',
            self::EVP => '123e4567-e89b-12d3-a456-426614174000',
        };
    }

    /**
     * Get max length
     */
    public function maxLength(): int
    {
        return match ($this) {
            self::CPF => 11,
            self::CNPJ => 14,
            self::EMAIL => 77,
            self::PHONE => 14, // +5511987654321
            self::EVP => 36,
        };
    }
}
