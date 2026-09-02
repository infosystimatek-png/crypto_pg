<?php

use App\Domain\Ledger\LedgerService;
use App\Domain\Shared\Money;
use App\Models\BlockchainAsset;
use App\Models\LedgerJournalEntry;
use App\Models\LedgerPosting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake(['merchant.test/*' => Http::response(['ok' => true], 200)]);
});

test('admin adjustment is an immutable journal and updates projection', function () {
    $ctx = provisionMerchant();
    $asset = BlockchainAsset::query()->first();
    $ledger = app(LedgerService::class);
    $amount = Money::fromDecimal('10.00', $asset->decimals, $asset->code);

    $ledger->adjustMerchant($ctx['merchant'], $asset, $amount, 'credit', 'promo', $ctx['owner']->id, 'adj-1');
    $ledger->adjustMerchant($ctx['merchant'], $asset, $amount, 'credit', 'promo', $ctx['owner']->id, 'adj-1');

    expect(LedgerJournalEntry::query()->count())->toBe(1);
    $check = $ledger->reconcileMerchantAsset($ctx['merchant']->id, $asset->id);
    expect($check['ok'])->toBeTrue();
    expect($check['journal']['available'])->toBe($amount->amountMinor);
});

test('unbalanced journal cannot be committed', function () {
    $ctx = provisionMerchant();
    $asset = BlockchainAsset::query()->first();
    $ledger = app(LedgerService::class);
    $amount = Money::fromDecimal('5.00', $asset->decimals, $asset->code);
    $ledger->adjustMerchant($ctx['merchant'], $asset, $amount, 'credit', 'seed', $ctx['owner']->id, 'adj-2');

    expect(fn () => DB::transaction(function () {
        // Historical postings must not be edited; a direct mutation would break reconciliation.
        LedgerPosting::query()->where('direction', 'credit')->update(['amount_minor' => '1']);
    }))->not->toThrow(Throwable::class);

    $check = app(LedgerService::class)->reconcileMerchantAsset($ctx['merchant']->id, $asset->id);
    expect($check['ok'])->toBeFalse();
});
