<?php

namespace App\Jobs;

use App\Domain\Webhooks\WebhookDeliveryService;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverWebhookJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $deliveryId) {}

    public function uniqueId(): string
    {
        return 'webhook-delivery-'.$this->deliveryId;
    }

    public function handle(WebhookDeliveryService $service): void
    {
        $delivery = WebhookDelivery::query()->find($this->deliveryId);
        if ($delivery) {
            $service->attempt($delivery);
        }
    }
}
