<?php

namespace App\Domain\Blockchain\Adapters;

use App\Domain\Blockchain\Contracts\TransactionBroadcasterInterface;
use RuntimeException;

final class DisabledTransactionBroadcaster implements TransactionBroadcasterInterface
{
    public function broadcast(string $networkCode, string $signedPayload): string
    {
        throw new RuntimeException('V1 does not broadcast transactions. Payouts are a V2 concern.');
    }
}
