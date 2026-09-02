<?php

namespace App\Domain\Webhooks;

use App\Domain\Shared\PublicId;
use App\Jobs\DeliverWebhookJob;
use App\Models\PaymentRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;

final class WebhookDispatcher
{
    public function dispatch(PaymentRequest $payment, string $type): WebhookEvent
    {
        $payment->loadMissing(['merchant', 'asset', 'network', 'blockchainTransaction']);

        $payload = [
            'event' => $type,
            'event_id' => PublicId::make('EVT'),
            'payment_id' => $payment->public_id,
            'merchant_order_id' => $payment->merchant_order_id,
            'amount' => $payment->expectedMoney()->toFixed(),
            'received_amount' => $payment->receivedMoney()->toFixed(),
            'currency' => $payment->asset->code,
            'network' => $payment->network->code,
            'transaction_hash' => $payment->blockchainTransaction?->tx_hash,
            'confirmations' => $payment->confirmations,
            'status' => $payment->status->value,
        ];

        $event = WebhookEvent::query()->create([
            'public_id' => $payload['event_id'],
            'merchant_id' => $payment->merchant_id,
            'payment_request_id' => $payment->id,
            'type' => $type,
            'payload' => $payload,
            'occurred_at' => now(),
        ]);

        $endpoints = WebhookEndpoint::query()
            ->where('merchant_id', $payment->merchant_id)
            ->where('is_active', true)
            ->get();

        foreach ($endpoints as $endpoint) {
            if (! $this->subscribes($endpoint, $type)) {
                continue;
            }

            $delivery = WebhookDelivery::query()->firstOrCreate(
                [
                    'webhook_event_id' => $event->id,
                    'webhook_endpoint_id' => $endpoint->id,
                ],
                [
                    'status' => 'pending',
                    'attempts' => 0,
                    'next_retry_at' => now(),
                ],
            );

            DeliverWebhookJob::dispatch($delivery->id);
        }

        if ($payment->callback_url) {
            $matching = $endpoints->firstWhere('url', $payment->callback_url);
            if (! $matching) {
                Log::info('webhook.callback_url_without_endpoint', [
                    'payment_id' => $payment->public_id,
                    'callback_url' => $payment->callback_url,
                ]);
            }
        }

        return $event;
    }

    private function subscribes(WebhookEndpoint $endpoint, string $type): bool
    {
        $events = $endpoint->subscribed_events ?? ['*'];

        return in_array('*', $events, true) || in_array($type, $events, true);
    }
}
