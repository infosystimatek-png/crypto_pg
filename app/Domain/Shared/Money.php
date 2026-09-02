<?php

namespace App\Domain\Shared;

use Brick\Math\BigDecimal;
use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

/**
 * Integer minor-unit money. Never uses PHP floats.
 */
final class Money
{
    public function __construct(
        public readonly string $amountMinor,
        public readonly int $decimals,
        public readonly string $currency,
    ) {
        if (! preg_match('/^-?\d+$/', $this->amountMinor)) {
            throw new InvalidArgumentException('Amount must be an integer string of minor units.');
        }

        if ($this->decimals < 0 || $this->decimals > 18) {
            throw new InvalidArgumentException('Decimals must be between 0 and 18.');
        }
    }

    public static function fromDecimal(string $decimal, int $decimals, string $currency): self
    {
        if (! preg_match('/^-?\d+(\.\d+)?$/', $decimal)) {
            throw new InvalidArgumentException('Amount is not numeric.');
        }

        try {
            $moved = BigDecimal::of($decimal)->withPointMovedRight($decimals);
            if ($moved->getScale() > 0) {
                $moved = $moved->toScale(0, RoundingMode::Unnecessary);
            }
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Amount scale exceeds asset decimals or is not numeric.', 0, $e);
        }

        return new self((string) $moved->toBigInteger(), $decimals, $currency);
    }

    public static function zero(int $decimals, string $currency): self
    {
        return new self('0', $decimals, $currency);
    }

    public function toDecimal(): string
    {
        return (string) BigDecimal::ofUnscaledValue($this->amountMinor, $this->decimals)->stripTrailingZeros();
    }

    public function toFixed(): string
    {
        return (string) BigDecimal::ofUnscaledValue($this->amountMinor, $this->decimals)->toScale($this->decimals);
    }

    public function plus(self $other): self
    {
        $this->assertCompatible($other);

        return new self(
            (string) BigInteger::of($this->amountMinor)->plus($other->amountMinor),
            $this->decimals,
            $this->currency,
        );
    }

    public function minus(self $other): self
    {
        $this->assertCompatible($other);

        return new self(
            (string) BigInteger::of($this->amountMinor)->minus($other->amountMinor),
            $this->decimals,
            $this->currency,
        );
    }

    public function compare(self $other): int
    {
        $this->assertCompatible($other);

        return BigInteger::of($this->amountMinor)->compareTo($other->amountMinor);
    }

    public function equals(self $other): bool
    {
        return $this->compare($other) === 0;
    }

    public function greaterThan(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function lessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isZero(): bool
    {
        return BigInteger::of($this->amountMinor)->isZero();
    }

    public function isNegative(): bool
    {
        return BigInteger::of($this->amountMinor)->isNegative();
    }

    public function isPositive(): bool
    {
        return BigInteger::of($this->amountMinor)->isPositive();
    }

    private function assertCompatible(self $other): void
    {
        if ($this->currency !== $other->currency || $this->decimals !== $other->decimals) {
            throw new InvalidArgumentException('Incompatible money operands.');
        }
    }
}
