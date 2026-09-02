<?php

use App\Domain\Payments\ExpirePaymentsService;
use App\Domain\Payments\PaymentStatus;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake(['merchant.test/*' => Http::response(['ok' => true], 200)]);
});

test('merchant can create a payment', function () {
    $ctx = provisionMerchant();

    $response = $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-12345',
        'amount' => '100.00',
        'currency' => 'USDT',
        'network' => 'TRON',
        'callback_url' => 'https://merchant.test/webhooks/payment',
    ], authApi($ctx['api_key']));

    $response->assertCreated()
        ->assertJsonPath('data.status', 'WAITING_FOR_PAYMENT')
        ->assertJsonPath('data.merchant_order_id', 'ORDER-12345')
        ->assertJsonMissingPath('data.private_key');

    expect($response->json('data.payment_address'))->toStartWith('T');
    expect($response->json('data.qr_data'))->toContain($response->json('data.payment_address'));
});

test('invalid api key is rejected', function () {
    seedGatewayCatalog();

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-1',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi('gw_live_invalid'))->assertUnauthorized();
});

test('invalid asset is rejected', function () {
    $ctx = provisionMerchant();

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-1',
        'amount' => '10.00',
        'currency' => 'BTC',
        'network' => 'TRON',
    ], authApi($ctx['api_key']))->assertUnprocessable();
});

test('invalid network is rejected', function () {
    $ctx = provisionMerchant();

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-1',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'ETHEREUM',
    ], authApi($ctx['api_key']))->assertUnprocessable();
});

test('duplicate idempotency key returns original payment', function () {
    $ctx = provisionMerchant();
    $headers = authApi($ctx['api_key']) + ['Idempotency-Key' => 'idem-1'];
    $payload = [
        'merchant_order_id' => 'ORDER-DUP',
        'amount' => '25.50',
        'currency' => 'USDT',
        'network' => 'TRON',
    ];

    $first = $this->postJson('/api/v1/payments', $payload, $headers);
    $second = $this->postJson('/api/v1/payments', $payload, $headers);

    $first->assertCreated();
    $second->assertCreated();
    expect($second->json('data.payment_id'))->toBe($first->json('data.payment_id'));
    expect(PaymentRequest::query()->count())->toBe(1);
});

test('reused idempotency key with different payload is rejected', function () {
    $ctx = provisionMerchant();
    $headers = authApi($ctx['api_key']) + ['Idempotency-Key' => 'idem-2'];

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-A',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], $headers)->assertCreated();

    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-B',
        'amount' => '11.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], $headers)->assertUnprocessable();
});

test('expired waiting payments transition to expired', function () {
    $ctx = provisionMerchant();
    $this->postJson('/api/v1/payments', [
        'merchant_order_id' => 'ORDER-EXP',
        'amount' => '10.00',
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($ctx['api_key']))->assertCreated();

    $payment = PaymentRequest::query()->first();
    $payment->update(['expires_at' => now()->subMinute()]);

    app(ExpirePaymentsService::class)->expireDue();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Expired);
});
