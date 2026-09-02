<?php

namespace App\Domain\Blockchain;

use App\Domain\Blockchain\Contracts\BlockchainAdapterInterface;
use App\Domain\Blockchain\Contracts\TransactionBroadcasterInterface;
use App\Models\BlockchainNetwork;
use RuntimeException;

final class BlockchainAdapterRegistry
{
    /**
     * @param  iterable<BlockchainAdapterInterface>  $adapters
     */
    public function __construct(
        private readonly iterable $adapters,
        private readonly TransactionBroadcasterInterface $broadcaster,
    ) {}

    public function forNetwork(BlockchainNetwork $network): BlockchainAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($network)) {
                return $adapter;
            }
        }

        throw new RuntimeException("No blockchain adapter registered for {$network->code}.");
    }

    public function broadcaster(): TransactionBroadcasterInterface
    {
        return $this->broadcaster;
    }
}
