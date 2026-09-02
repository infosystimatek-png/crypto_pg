<?php

namespace App\Domain\Blockchain\DTO;

final class IncomingTransaction
{
    public function __construct(
        public readonly string $networkCode,
        public readonly string $assetCode,
        public readonly string $txHash,
        public readonly int $logIndex,
        public readonly string $fromAddress,
        public readonly string $toAddress,
        public readonly string $amountDecimal,
        public readonly ?int $blockNumber,
        public readonly int $confirmations,
        public readonly ?string $contractAddress = null,
        public readonly array $raw = [],
    ) {}
}
