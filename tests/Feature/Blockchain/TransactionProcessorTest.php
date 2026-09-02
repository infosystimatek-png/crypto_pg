<?php

use App\Domain\Blockchain\TransactionProcessor;
use App\Domain\Ledger\LedgerService;
use App\Domain\Payments\PaymentStatus;
use App\Models\BlockchainTransaction;
use App\Models\LedgerJournalEntry;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function processIncoming(PaymentRequest $payment, string $amount, int $confirmations = 1, string $asset = 'USDT', ?string $hash = null): string
{
    $hash ??= '0x'.Str::lower((string) Str::ulid());
    $payment->loadMissing(['paymentAddress', 'network']);

    app(TransactionProcessor::class)->process($payment->network_id, [
        'networkCode' => $payment->network->code,
        'assetCode' => $asset,
        'txHash' => $hash,
        'logIndex' => 0,
        'fromAddress' => 'TMOCKFROM000000000000000000000001',
        'toAddress' => $payment->paymentAddress->address,
        'amountDecimal' => $amount,
        'blockNumber' => 42,
        'confirmations' => $confirmations,
        'raw' => [],
    ]);

    return $hash;
}

function createOpenPayment(array $ctx, string $order, string $amount = '100.00'): PaymentRequest
{
    $response = test()->postJson('/api/v1/payments', [
        'merchant_order_id' => $order,
        'amount' => $amount,
        'currency' => 'USDT',
        'network' => 'TRON',
    ], authApi($ctx['api_key']));

    $response->assertCreated();

    return PaymentRequest::query()->where('public_id', $response->json('data.payment_id'))->firstOrFail();
}

beforeEach(function () {
    Http::fake(['merchant.test/*' => Http::response(['ok' => true], 200)]);
});

test('exact payment is credited once', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'EXACT-1');
    $hash = processIncoming($payment, '100.00');

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::Credited);
    expect($payment->blockchainTransaction->tx_hash)->toBe($hash);

    $check = app(LedgerService::class)->reconcileMerchantAsset($payment->merchant_id, $payment->asset_id);
    expect($check['ok'])->toBeTrue();
    expect($check['journal']['available'])->toBe($payment->amount_minor);
    expect($check['journal']['pending'])->toBe('0');
});

test('duplicate transaction does not double credit', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'DUP-TX');
    $hash = processIncoming($payment, '100.00');
    processIncoming($payment->fresh(), '100.00', 5, 'USDT', $hash);
    processIncoming($payment->fresh(), '100.00', 9, 'USDT', $hash);

    expect(BlockchainTransaction::query()->count())->toBe(1);
    expect(LedgerJournalEntry::query()->where('type', 'payment_available_credit')->count())->toBe(1);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Credited);
});

test('wrong address is unmatched', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'WRONG-ADDR');

    app(TransactionProcessor::class)->process($payment->network_id, [
        'networkCode' => 'TRON',
        'assetCode' => 'USDT',
        'txHash' => '0xorphan',
        'logIndex' => 0,
        'fromAddress' => 'TFROM',
        'toAddress' => 'TNOTOURS00000000000000000000000001',
        'amountDecimal' => '100.00',
        'blockNumber' => 1,
        'confirmations' => 1,
        'raw' => [],
    ]);

    expect($payment->fresh()->status)->toBe(PaymentStatus::WaitingForPayment);
    expect(BlockchainTransaction::query()->first()->processing_status)->toBe('unmatched');
});

test('wrong token marks payment wrong asset', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'WRONG-ASSET');
    processIncoming($payment, '100.00', 1, 'USDC');

    expect($payment->fresh()->status)->toBe(PaymentStatus::WrongAsset);
});

test('underpayment is not credited', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'UNDER');
    processIncoming($payment, '90.00');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Underpaid);
    expect(LedgerJournalEntry::query()->count())->toBe(0);
});

test('overpayment credits expected amount and records excess', function () {
    $ctx = provisionMerchant();
    $payment = createOpenPayment($ctx, 'OVER');
    processIncoming($payment, '150.00');

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::Credited);
    expect(LedgerJournalEntry::query()->where('type', 'payment_overpayment')->count())->toBe(1);
    $check = app(LedgerService::class)->reconcileMerchantAsset($payment->merchant_id, $payment->asset_id);
    expect($check['ok'])->toBeTrue();
    expect($check['journal']['available'])->toBe($payment->amount_minor);
});
