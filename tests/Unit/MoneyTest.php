<?php

use App\Domain\Shared\Money;

test('money uses integer minor units and rejects floats implicitly', function () {
    $a = Money::fromDecimal('100.00', 6, 'USDT');
    $b = Money::fromDecimal('50.25', 6, 'USDT');

    expect($a->amountMinor)->toBe('100000000');
    expect($a->plus($b)->toFixed())->toBe('150.250000');
    expect($a->minus($b)->toFixed())->toBe('49.750000');
    expect($a->greaterThan($b))->toBeTrue();
});

test('incompatible currencies cannot be added', function () {
    $a = Money::fromDecimal('1', 6, 'USDT');
    $b = Money::fromDecimal('1', 6, 'USDC');

    $a->plus($b);
})->throws(InvalidArgumentException::class);
