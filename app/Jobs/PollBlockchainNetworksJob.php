<?php

namespace App\Jobs;

use App\Domain\Blockchain\TransactionMonitor;
use App\Models\BlockchainNetwork;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PollBlockchainNetworksJob implements ShouldQueue
{
    use Queueable;

    public function handle(TransactionMonitor $monitor): void
    {
        BlockchainNetwork::query()->where('is_enabled', true)->each(function (BlockchainNetwork $network) use ($monitor) {
            $monitor->poll($network);
        });
    }
}
