<?php

namespace App\Jobs;

use App\Domain\Payments\ExpirePaymentsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpirePaymentsJob implements ShouldQueue
{
    use Queueable;

    public function handle(ExpirePaymentsService $service): void
    {
        $service->expireDue();
    }
}
