<?php

namespace Eduardoks98\Helpers\Formatters;

class MoneyFormatter
{
    /**
     * Formata um valor monetário em reais brasileiros.
     *
     * @param float $amount
     * @param string $symbol
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandSeparator
     * @return string
     */
    public static function format(
        float $amount,
        string $symbol = 'R$',
        int $decimals = 2,
        string $decimalSeparator = ',',
        string $thousandSeparator = '.'
    ): string {
        $formatted = number_format($amount, $decimals, $decimalSeparator, $thousandSeparator);
        return "{$symbol} {$formatted}";
    }

    /**
     * Remove formatação de valor monetário e retorna float.
     *
     * @param string $money
     * @return float
     */
    public static function parse(string $money): float
    {
        // Remove símbolo de moeda
        $money = preg_replace('/[^0-9,.-]/', '', $money);

        // Substitui vírgula por ponto
        $money = str_replace(',', '.', $money);

        // Remove pontos de milhares (mantém apenas o último ponto decimal)
        $parts = explode('.', $money);
        if (count($parts) > 2) {
            $decimal = array_pop($parts);
            $money = implode('', $parts) . '.' . $decimal;
        }

        return (float) $money;
    }
}
