<?php

namespace App\Jobs;

use App\Domain\Reconciliation\ReconciliationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunReconciliationJob implements ShouldQueue
{
    use Queueable;

    public function handle(ReconciliationService $service): void
    {
        $service->run();
    }
}
