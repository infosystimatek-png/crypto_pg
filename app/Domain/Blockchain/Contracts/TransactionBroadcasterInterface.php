<?php

namespace App\Domain\Blockchain\Contracts;

/**
 * V1 does not broadcast merchant payouts. This exists so V2 can add settlement
 * without changing the payment engine.
 */
interface TransactionBroadcasterInterface
{
    public function broadcast(string $networkCode, string $signedPayload): string;
}
