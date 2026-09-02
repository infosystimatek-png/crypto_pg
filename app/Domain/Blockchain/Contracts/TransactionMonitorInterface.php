<?php

namespace App\Domain\Blockchain\Contracts;

use App\Models\BlockchainNetwork;

interface TransactionMonitorInterface
{
    public function poll(BlockchainNetwork $network): int;
}
