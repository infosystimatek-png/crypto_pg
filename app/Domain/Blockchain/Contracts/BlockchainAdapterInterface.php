<?php

namespace App\Domain\Blockchain\Contracts;

use App\Domain\Blockchain\DTO\IncomingTransaction;
use App\Models\BlockchainNetwork;

interface BlockchainAdapterInterface
{
    public function networkCode(): string;

    public function supports(BlockchainNetwork $network): bool;

    /**
     * @return list<IncomingTransaction>
     */
    public function fetchIncoming(BlockchainNetwork $network, array $addresses, mixed $cursor): array;

    public function fetchTransaction(BlockchainNetwork $network, string $txHash): ?IncomingTransaction;

    public function healthCheck(): bool;
}
