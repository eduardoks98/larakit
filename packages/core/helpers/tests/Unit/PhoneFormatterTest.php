<?php

use Eduardoks98\Helpers\Formatters\PhoneFormatter;

test('formata celular com 11 dígitos', function () {
    $formatted = PhoneFormatter::format('11987654321');
    expect($formatted)->toBe('(11) 98765-4321');
});

test('formata telefone fixo com 10 dígitos', function () {
    $formatted = PhoneFormatter::format('1134567890');
    expect($formatted)->toBe('(11) 3456-7890');
});

test('remove código do país 55', function () {
    $formatted = PhoneFormatter::format('5511987654321');
    expect($formatted)->toBe('(11) 98765-4321');
});

test('limpa caracteres especiais', function () {
    $cleaned = PhoneFormatter::clean('(11) 98765-4321');
    expect($cleaned)->toBe('11987654321');
});
