<?php

use Eduardoks98\Helpers\Formatters\MoneyFormatter;

test('formata valor em reais', function () {
    $formatted = MoneyFormatter::format(1234.56);
    expect($formatted)->toBe('R$ 1.234,56');
});

test('formata valor com decimais customizados', function () {
    $formatted = MoneyFormatter::format(1234.567, 'R$', 3);
    expect($formatted)->toBe('R$ 1.234,567');
});

test('parse valor formatado para float', function () {
    $value = MoneyFormatter::parse('R$ 1.234,56');
    expect($value)->toBe(1234.56);
});

test('parse valor com múltiplos pontos', function () {
    $value = MoneyFormatter::parse('R$ 1.234.567,89');
    expect($value)->toBe(1234567.89);
});
