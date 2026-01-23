<?php

use Eduardoks98\Helpers\Validators\CpfValidator;

test('valida CPF correto', function () {
    expect(CpfValidator::validate('12345678909'))->toBeTrue();
});

test('rejeita CPF inválido', function () {
    expect(CpfValidator::validate('11111111111'))->toBeFalse();
    expect(CpfValidator::validate('12345678900'))->toBeFalse();
});

test('rejeita CPF com caracteres especiais sem limpeza', function () {
    expect(CpfValidator::validate('123.456.789-09'))->toBeFalse();
});

test('rejeita CPF muito curto', function () {
    expect(CpfValidator::validate('123'))->toBeFalse();
});

test('rejeita CPF muito longo', function () {
    expect(CpfValidator::validate('123456789091234'))->toBeFalse();
});
