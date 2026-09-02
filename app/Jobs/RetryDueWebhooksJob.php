<?php

namespace App\Jobs;

use App\Domain\Webhooks\WebhookDeliveryService;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RetryDueWebhooksJob implements ShouldQueue
{
    use Queueable;

    public function handle(WebhookDeliveryService $service): void
    {
        WebhookDelivery::query()
            ->whereIn('status', ['pending', 'failed'])
            ->where(function ($q) {
                $q->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', now());
            })
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(fn (WebhookDelivery $delivery) => $service->attempt($delivery));
    }
}
