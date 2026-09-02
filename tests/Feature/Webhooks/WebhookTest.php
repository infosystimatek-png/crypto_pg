<?php

use App\Domain\Webhooks\WebhookDeliveryService;
use App\Domain\Webhooks\WebhookDispatcher;
use App\Models\PaymentRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    //
});

test('successful webhook delivery is signed and stored', function () {
    Http::fake(['merchant.test/*' => Http::response('ok', 200)]);
    $ctx = provisionMerchant();
    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'WH-1',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($ctx['api_key']))->assertCreated();

    $payment = PaymentRequest::query()->first();
    app(WebhookDispatcher::class)->dispatch($payment, 'payment.confirmed');

    $delivery = WebhookDelivery::query()->latest('id')->first();
    expect($delivery->status)->toBe('delivered');
    expect($delivery->last_response_code)->toBe(200);

    Http::assertSent(function ($request) use ($ctx) {
        $timestamp = $request->header('X-Gateway-Timestamp')[0];
        $signature = $request->header('X-Gateway-Signature')[0];
        $body = $request->body();

        return WebhookDeliveryService::verifySignature($ctx['webhook_secret'], $timestamp, $body, $signature);
    });
});

test('failed delivery is retried and duplicate dispatch is unique per endpoint', function () {
    Http::fake(['merchant.test/*' => Http::response('nope', 500)]);
    $ctx = provisionMerchant();
    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'WH-2',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($ctx['api_key']));

    $payment = PaymentRequest::query()->first();
    $dispatcher = app(WebhookDispatcher::class);
    $dispatcher->dispatch($payment, 'payment.confirmed');
    $dispatcher->dispatch($payment, 'payment.confirmed');

    expect(WebhookEvent::query()->count())->toBe(2);
    expect(WebhookDelivery::query()->count())->toBe(2);

    $delivery = WebhookDelivery::query()->first();
    expect($delivery->status)->toBe('failed');
    expect($delivery->next_retry_at)->not->toBeNull();

    app(WebhookDeliveryService::class)->retry($delivery->fresh());
    expect($delivery->fresh()->attempts)->toBeGreaterThan(1);
});

test('invalid signatures are rejected by verifier', function () {
    expect(WebhookDeliveryService::verifySignature('secret', '1', '{}', 'deadbeef'))->toBeFalse();
    $sig = hash_hmac('sha256', '1.{}', 'secret');
    expect(WebhookDeliveryService::verifySignature('secret', '1', '{}', $sig))->toBeTrue();
});
