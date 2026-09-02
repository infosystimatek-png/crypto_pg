<?php

namespace App\Jobs;

use App\Domain\Blockchain\TransactionProcessor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBlockchainTransactionJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public int $networkId,
        public array $payload,
    ) {}

    public function uniqueId(): string
    {
        return $this->networkId.':'.$this->payload['txHash'].':'.$this->payload['logIndex'];
    }

    public function handle(TransactionProcessor $processor): void
    {
        $processor->process($this->networkId, $this->payload);
    }
}
