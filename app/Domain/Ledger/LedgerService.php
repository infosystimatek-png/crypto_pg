<?php

namespace App\Domain\Ledger;

use App\Domain\Shared\Money;
use App\Domain\Shared\PublicId;
use App\Models\BlockchainAsset;
use App\Models\LedgerAccount;
use App\Models\LedgerJournalEntry;
use App\Models\LedgerPosting;
use App\Models\Merchant;
use App\Models\MerchantBalanceProjection;
use App\Models\PaymentRequest;
use Brick\Math\BigInteger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class LedgerService
{
    public const TYPE_MERCHANT_AVAILABLE = 'merchant_available';

    public const TYPE_MERCHANT_PENDING = 'merchant_pending';

    public const TYPE_MERCHANT_RESERVED = 'merchant_reserved';

    public const TYPE_SYSTEM_CLEARING = 'system_clearing';

    public const TYPE_OVERPAYMENT_SUSPENSE = 'overpayment_suspense';

    /**
     * Credit pending on first detection (idempotent).
     */
    public function creditPending(PaymentRequest $payment, Money $amount, string $idempotencyKey): ?LedgerJournalEntry
    {
        return $this->postPaymentMove(
            $payment,
            $amount,
            from: self::TYPE_SYSTEM_CLEARING,
            to: self::TYPE_MERCHANT_PENDING,
            type: 'payment_pending_credit',
            description: "Pending credit for {$payment->public_id}",
            idempotencyKey: $idempotencyKey,
            projection: ['pending' => $amount->amountMinor],
        );
    }

    /**
     * Move pending → available on confirmation (idempotent).
     */
    public function creditAvailable(PaymentRequest $payment, Money $amount, string $idempotencyKey): ?LedgerJournalEntry
    {
        return $this->postPaymentMove(
            $payment,
            $amount,
            from: self::TYPE_MERCHANT_PENDING,
            to: self::TYPE_MERCHANT_AVAILABLE,
            type: 'payment_available_credit',
            description: "Available credit for {$payment->public_id}",
            idempotencyKey: $idempotencyKey,
            projection: [
                'pending' => BigInteger::of($amount->amountMinor)->negated()->__toString(),
                'available' => $amount->amountMinor,
            ],
        );
    }

    public function recordOverpayment(PaymentRequest $payment, Money $excess, string $idempotencyKey): ?LedgerJournalEntry
    {
        return $this->postPaymentMove(
            $payment,
            $excess,
            from: self::TYPE_SYSTEM_CLEARING,
            to: self::TYPE_OVERPAYMENT_SUSPENSE,
            type: 'payment_overpayment',
            description: "Overpayment suspense for {$payment->public_id}",
            idempotencyKey: $idempotencyKey,
            projection: [],
        );
    }

    /**
     * Admin adjustment: never mutates history. Creates a new balanced journal.
     *
     * @param  'credit'|'debit'  $direction  relative to merchant available
     */
    public function adjustMerchant(
        Merchant $merchant,
        BlockchainAsset $asset,
        Money $amount,
        string $direction,
        string $reason,
        int $adminUserId,
        string $idempotencyKey,
    ): LedgerJournalEntry {
        if ($amount->isZero() || $amount->isNegative()) {
            throw new RuntimeException('Adjustment amount must be positive.');
        }

        return DB::transaction(function () use ($merchant, $asset, $amount, $direction, $reason, $adminUserId, $idempotencyKey) {
            $existing = LedgerJournalEntry::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $available = $this->account($merchant->id, $asset->id, self::TYPE_MERCHANT_AVAILABLE);
            $clearing = $this->systemAccount($asset->id, self::TYPE_SYSTEM_CLEARING);

            $from = $direction === 'credit' ? $clearing : $available;
            $to = $direction === 'credit' ? $available : $clearing;

            $entry = $this->writeEntry(
                merchantId: $merchant->id,
                type: 'admin_adjustment',
                description: $reason,
                idempotencyKey: $idempotencyKey,
                from: $from,
                to: $to,
                amount: $amount,
                payment: null,
                createdBy: 'admin',
                createdByUserId: $adminUserId,
            );

            $delta = $direction === 'credit' ? $amount->amountMinor : BigInteger::of($amount->amountMinor)->negated()->__toString();
            $this->bumpProjection($merchant->id, $asset->id, ['available' => $delta]);

            return $entry;
        });
    }

    /**
     * Recalculate projection from postings and compare.
     *
     * @return array{ok: bool, journal: array, projection: array}
     */
    public function reconcileMerchantAsset(int $merchantId, int $assetId): array
    {
        $available = $this->account($merchantId, $assetId, self::TYPE_MERCHANT_AVAILABLE);
        $pending = $this->account($merchantId, $assetId, self::TYPE_MERCHANT_PENDING);
        $reserved = $this->account($merchantId, $assetId, self::TYPE_MERCHANT_RESERVED);

        $journal = [
            'available' => $this->accountNet($available->id),
            'pending' => $this->accountNet($pending->id),
            'reserved' => $this->accountNet($reserved->id),
        ];

        $projection = MerchantBalanceProjection::query()
            ->where('merchant_id', $merchantId)
            ->where('asset_id', $assetId)
            ->first();

        $proj = [
            'available' => $projection?->available_minor ?? '0',
            'pending' => $projection?->pending_minor ?? '0',
            'reserved' => $projection?->reserved_minor ?? '0',
        ];

        return [
            'ok' => $journal === $proj,
            'journal' => $journal,
            'projection' => $proj,
        ];
    }

    /**
     * @param  array{pending?: string, available?: string, reserved?: string}  $projection
     */
    private function postPaymentMove(
        PaymentRequest $payment,
        Money $amount,
        string $from,
        string $to,
        string $type,
        string $description,
        string $idempotencyKey,
        array $projection,
    ): ?LedgerJournalEntry {
        return DB::transaction(function () use ($payment, $amount, $from, $to, $type, $description, $idempotencyKey, $projection) {
            $existing = LedgerJournalEntry::query()->where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $fromAccount = $from === self::TYPE_SYSTEM_CLEARING || $from === self::TYPE_OVERPAYMENT_SUSPENSE
                ? $this->systemAccount($payment->asset_id, $from)
                : $this->account($payment->merchant_id, $payment->asset_id, $from);

            $toAccount = $to === self::TYPE_SYSTEM_CLEARING || $to === self::TYPE_OVERPAYMENT_SUSPENSE
                ? $this->systemAccount($payment->asset_id, $to)
                : $this->account($payment->merchant_id, $payment->asset_id, $to);

            $entry = $this->writeEntry(
                merchantId: $payment->merchant_id,
                type: $type,
                description: $description,
                idempotencyKey: $idempotencyKey,
                from: $fromAccount,
                to: $toAccount,
                amount: $amount,
                payment: $payment,
                createdBy: 'system',
                createdByUserId: null,
            );

            if ($projection !== []) {
                $this->bumpProjection($payment->merchant_id, $payment->asset_id, $projection);
            }

            return $entry;
        });
    }

    private function writeEntry(
        ?int $merchantId,
        string $type,
        string $description,
        string $idempotencyKey,
        LedgerAccount $from,
        LedgerAccount $to,
        Money $amount,
        ?PaymentRequest $payment,
        string $createdBy,
        ?int $createdByUserId,
    ): LedgerJournalEntry {
        $entry = LedgerJournalEntry::query()->create([
            'public_id' => PublicId::make('JRN'),
            'merchant_id' => $merchantId,
            'type' => $type,
            'status' => 'posted',
            'description' => $description,
            'payment_request_id' => $payment?->id,
            'blockchain_transaction_id' => $payment?->blockchain_transaction_id,
            'idempotency_key' => $idempotencyKey,
            'created_by_user_id' => $createdByUserId,
            'created_by' => $createdBy,
            'posted_at' => now(),
        ]);

        $fromBalance = $this->accountNet($from->id);
        $toBalance = $this->accountNet($to->id);
        $fromAfter = (string) BigInteger::of($fromBalance)->minus($amount->amountMinor);
        $toAfter = (string) BigInteger::of($toBalance)->plus($amount->amountMinor);

        LedgerPosting::query()->insert([
            [
                'journal_entry_id' => $entry->id,
                'account_id' => $from->id,
                'asset_id' => $from->asset_id,
                'direction' => 'debit',
                'amount_minor' => $amount->amountMinor,
                'balance_after_minor' => $fromAfter,
                'created_at' => now(),
            ],
            [
                'journal_entry_id' => $entry->id,
                'account_id' => $to->id,
                'asset_id' => $to->asset_id,
                'direction' => 'credit',
                'amount_minor' => $amount->amountMinor,
                'balance_after_minor' => $toAfter,
                'created_at' => now(),
            ],
        ]);

        $debits = LedgerPosting::query()->where('journal_entry_id', $entry->id)->where('direction', 'debit')->get()->sum(fn ($p) => (int) $p->amount_minor);
        // Don't use int sum for large amounts - verify with BigInteger
        $debitTotal = '0';
        $creditTotal = '0';
        foreach (LedgerPosting::query()->where('journal_entry_id', $entry->id)->get() as $posting) {
            if ($posting->direction === 'debit') {
                $debitTotal = (string) BigInteger::of($debitTotal)->plus($posting->amount_minor);
            } else {
                $creditTotal = (string) BigInteger::of($creditTotal)->plus($posting->amount_minor);
            }
        }

        if ($debitTotal !== $creditTotal) {
            throw new RuntimeException('Unbalanced journal entry.');
        }

        return $entry;
    }

    public function account(?int $merchantId, int $assetId, string $type): LedgerAccount
    {
        $asset = BlockchainAsset::query()->findOrFail($assetId);
        $code = $merchantId
            ? "M{$merchantId}:{$asset->code}:{$type}"
            : "SYS:{$asset->code}:{$type}";

        return LedgerAccount::query()->firstOrCreate(
            [
                'merchant_id' => $merchantId,
                'asset_id' => $assetId,
                'type' => $type,
            ],
            [
                'public_id' => PublicId::make('ACC'),
                'code' => $code,
                'name' => str_replace('_', ' ', $type).' '.$asset->code,
            ],
        );
    }

    public function systemAccount(int $assetId, string $type): LedgerAccount
    {
        return $this->account(null, $assetId, $type);
    }

    public function accountNet(int $accountId): string
    {
        $credits = '0';
        $debits = '0';

        foreach (LedgerPosting::query()->where('account_id', $accountId)->cursor() as $posting) {
            if ($posting->direction === 'credit') {
                $credits = (string) BigInteger::of($credits)->plus($posting->amount_minor);
            } else {
                $debits = (string) BigInteger::of($debits)->plus($posting->amount_minor);
            }
        }

        return (string) BigInteger::of($credits)->minus($debits);
    }

    /**
     * @param  array{pending?: string, available?: string, reserved?: string}  $deltas
     */
    private function bumpProjection(int $merchantId, int $assetId, array $deltas): void
    {
        $row = MerchantBalanceProjection::query()
            ->where('merchant_id', $merchantId)
            ->where('asset_id', $assetId)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            $row = MerchantBalanceProjection::query()->create([
                'merchant_id' => $merchantId,
                'asset_id' => $assetId,
                'available_minor' => '0',
                'pending_minor' => '0',
                'reserved_minor' => '0',
                'version' => 0,
            ]);
            $row = MerchantBalanceProjection::query()->whereKey($row->id)->lockForUpdate()->firstOrFail();
        }

        foreach (['available' => 'available_minor', 'pending' => 'pending_minor', 'reserved' => 'reserved_minor'] as $key => $column) {
            if (isset($deltas[$key])) {
                $row->{$column} = (string) BigInteger::of($row->{$column})->plus($deltas[$key]);
            }
        }

        $row->version++;
        $row->save();
    }
}
