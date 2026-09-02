<?php

namespace App\Domain\Payments;

use RuntimeException;

final class InvalidPaymentTransition extends RuntimeException
{
    public static function from(PaymentStatus $from, PaymentStatus $to): self
    {
        return new self("Invalid payment transition {$from->value} → {$to->value}.");
    }
}
