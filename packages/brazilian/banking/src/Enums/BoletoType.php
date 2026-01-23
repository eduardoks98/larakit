<?php

namespace Eduardoks98\Banking\Enums;

/**
 * Boleto Types
 */
enum BoletoType: string
{
    case BANK = 'bank';
    case UTILITY = 'utility';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::BANK => 'Boleto Bancário',
            self::UTILITY => 'Boleto de Concessionária',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::BANK => 'Boleto emitido por bancos para pagamento de compras, empréstimos, etc.',
            self::UTILITY => 'Boleto de concessionárias para contas de luz, água, gás, telefone, impostos, etc.',
        };
    }

    /**
     * Get barcode length
     */
    public function barcodeLength(): int
    {
        return 44;
    }

    /**
     * Get digitable line length
     */
    public function digitableLength(): int
    {
        return match ($this) {
            self::BANK => 47,
            self::UTILITY => 48,
        };
    }
}
