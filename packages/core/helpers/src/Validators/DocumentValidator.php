<?php

namespace Eduardoks98\Helpers\Validators;

class DocumentValidator
{
    /**
     * Valida CPF ou CNPJ automaticamente.
     *
     * @param string $document
     * @return bool
     */
    public static function validate(string $document): bool
    {
        // Remove caracteres não numéricos
        $document = preg_replace('/[^0-9]/', '', $document);

        // Detecta tipo baseado no tamanho
        if (strlen($document) == 11) {
            return CpfValidator::validate($document);
        } elseif (strlen($document) == 14) {
            return CnpjValidator::validate($document);
        }

        return false;
    }

    /**
     * Identifica o tipo de documento.
     *
     * @param string $document
     * @return string|null 'cpf', 'cnpj' ou null
     */
    public static function identify(string $document): ?string
    {
        $document = preg_replace('/[^0-9]/', '', $document);

        if (strlen($document) == 11) {
            return 'cpf';
        } elseif (strlen($document) == 14) {
            return 'cnpj';
        }

        return null;
    }
}
