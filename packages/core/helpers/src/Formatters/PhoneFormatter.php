<?php

namespace Eduardoks98\Helpers\Formatters;

class PhoneFormatter
{
    /**
     * Formata um número de telefone brasileiro.
     *
     * @param string $phone
     * @return string
     */
    public static function format(string $phone): string
    {
        // Remove tudo exceto números
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove código do país se presente (55)
        if (strlen($phone) == 13 && substr($phone, 0, 2) == '55') {
            $phone = substr($phone, 2);
        }

        // Celular com 9 dígitos (11) 98765-4321
        if (strlen($phone) == 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        }

        // Telefone fixo com 8 dígitos (11) 3456-7890
        if (strlen($phone) == 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }

        // Telefone sem DDD
        if (strlen($phone) == 9) {
            return substr($phone, 0, 5) . '-' . substr($phone, 5);
        }

        if (strlen($phone) == 8) {
            return substr($phone, 0, 4) . '-' . substr($phone, 4);
        }

        // Retorna sem formatação se não se encaixar nos padrões
        return $phone;
    }

    /**
     * Remove formatação de telefone.
     *
     * @param string $phone
     * @return string
     */
    public static function clean(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
