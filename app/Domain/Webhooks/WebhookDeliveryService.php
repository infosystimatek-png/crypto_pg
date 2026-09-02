<?php

namespace App\Domain\Webhooks;

use App\Models\WebhookDelivery;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WebhookDeliveryService
{
    public function attempt(WebhookDelivery $delivery): void
    {
        $delivery->load(['event', 'endpoint']);

        if (in_array($delivery->status, ['delivered', 'dead_letter'], true)) {
            return;
        }

        $body = json_encode($delivery->event->payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $secret = $delivery->endpoint->signingSecret();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        $delivery->attempts++;

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Gateway-Signature' => $signature,
                    'X-Gateway-Timestamp' => $timestamp,
                    'X-Gateway-Event-Id' => $delivery->event->public_id,
                    'X-Gateway-Event' => $delivery->event->type,
                    'User-Agent' => 'CryptoGateway-Webhooks/1.0',
                ])
                ->withBody($body, 'application/json')
                ->post($delivery->endpoint->url);

            $this->recordResponse($delivery, $response);
        } catch (Throwable $e) {
            $this->recordFailure($delivery, $e->getMessage());
        }
    }

    public function retry(WebhookDelivery $delivery): void
    {
        if ($delivery->status === 'delivered') {
            return;
        }

        $delivery->status = 'pending';
        $delivery->next_retry_at = now();
        $delivery->dead_lettered_at = null;
        $delivery->save();

        $this->attempt($delivery->fresh());
    }

    public static function verifySignature(string $secret, string $timestamp, string $body, string $signature): bool
    {
        $expected = hash_hmac('sha256', $timestamp.'.'.$body, $secret);

        return hash_equals($expected, $signature);
    }

    private function recordResponse(WebhookDelivery $delivery, Response $response): void
    {
        $delivery->last_response_code = $response->status();
        $delivery->last_response_body = substr($response->body(), 0, 4000);

        if ($response->successful()) {
            $delivery->status = 'delivered';
            $delivery->delivered_at = now();
            $delivery->last_error = null;
            $delivery->next_retry_at = null;
            $delivery->save();

            return;
        }

        $this->recordFailure($delivery, 'HTTP '.$response->status());
    }

    private function recordFailure(WebhookDelivery $delivery, string $reason): void
    {
        $max = (int) config('gateway.webhook_max_attempts', 10);
        $delivery->last_error = $reason;

        if ($delivery->attempts >= $max) {
            $delivery->status = 'dead_letter';
            $delivery->dead_lettered_at = now();
            $delivery->next_retry_at = null;
        } else {
            $delivery->status = 'failed';
            $delay = min(3600, (int) (60 * (5 ** max(0, $delivery->attempts - 1))));
            $delivery->next_retry_at = now()->addSeconds($delay);
        }

        $delivery->save();

        Log::warning('webhook.delivery_failed', [
            'webhook_event_id' => $delivery->event->public_id,
            'attempt' => $delivery->attempts,
            'reason' => $reason,
        ]);
    }
}
