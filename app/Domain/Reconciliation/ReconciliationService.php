<?php

namespace App\Domain\Reconciliation;

use App\Domain\Ledger\LedgerService;
use App\Domain\Payments\PaymentStatus;
use App\Domain\Shared\PublicId;
use App\Models\BlockchainTransaction;
use App\Models\MerchantBalanceProjection;
use App\Models\PaymentAddress;
use App\Models\PaymentRequest;
use App\Models\ReconciliationItem;
use App\Models\ReconciliationRun;

final class ReconciliationService
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function run(): ReconciliationRun
    {
        $run = ReconciliationRun::query()->create([
            'public_id' => PublicId::make('REC'),
            'status' => 'running',
            'started_at' => now(),
        ]);

        $matched = 0;
        $unmatched = 0;
        $exceptions = 0;

        foreach (BlockchainTransaction::query()->cursor() as $tx) {
            if ($tx->processing_status === 'unmatched' || $tx->payment_request_id === null) {
                $this->item($run, 'unmatched_blockchain_transaction', 'warning', blockchainTransactionId: $tx->id, payload: [
                    'tx_hash' => $tx->tx_hash,
                    'to' => $tx->to_address,
                ]);
                $unmatched++;

                continue;
            }

            $payment = $tx->paymentRequest;
            if (! $payment) {
                $this->item($run, 'orphaned_transaction_payment', 'error', blockchainTransactionId: $tx->id);
                $exceptions++;

                continue;
            }

            if ($payment->amount_minor !== $tx->amount_minor && $payment->status === PaymentStatus::Credited) {
                if ($tx->processing_status !== 'matched_overpaid' && $tx->processing_status !== 'matched_underpaid') {
                    $this->item($run, 'amount_mismatch', 'error', $payment->id, $tx->id, $payment->merchant_id, [
                        'expected' => $payment->amount_minor,
                        'observed' => $tx->amount_minor,
                    ]);
                    $exceptions++;

                    continue;
                }
            }

            $matched++;
        }

        PaymentRequest::query()
            ->where('status', PaymentStatus::Credited->value)
            ->whereNull('blockchain_transaction_id')
            ->each(function (PaymentRequest $payment) use ($run, &$exceptions) {
                $this->item($run, 'credited_without_chain_tx', 'error', $payment->id, merchantId: $payment->merchant_id);
                $exceptions++;
            });

        PaymentRequest::query()
            ->whereIn('status', [PaymentStatus::Confirmed->value, PaymentStatus::Confirming->value])
            ->whereNull('credited_at')
            ->where('confirmations', '>=', 1)
            ->each(function (PaymentRequest $payment) use ($run, &$exceptions) {
                if ($payment->confirmations >= $payment->required_confirmations) {
                    $this->item($run, 'uncredited_confirmed_payment', 'error', $payment->id, merchantId: $payment->merchant_id);
                    $exceptions++;
                }
            });

        PaymentRequest::query()
            ->whereNotNull('credited_at')
            ->whereColumn('confirmations', '<', 'required_confirmations')
            ->each(function (PaymentRequest $payment) use ($run, &$exceptions) {
                $this->item($run, 'credited_but_unconfirmed', 'error', $payment->id, merchantId: $payment->merchant_id);
                $exceptions++;
            });

        PaymentAddress::query()
            ->whereNull('payment_request_id')
            ->each(function (PaymentAddress $address) use ($run, &$unmatched) {
                $this->item($run, 'orphaned_address', 'info', payload: ['address' => $address->address]);
                $unmatched++;
            });

        foreach (MerchantBalanceProjection::query()->cursor() as $projection) {
            $check = $this->ledger->reconcileMerchantAsset($projection->merchant_id, $projection->asset_id);
            if (! $check['ok']) {
                $this->item($run, 'ledger_projection_mismatch', 'error', merchantId: $projection->merchant_id, payload: $check);
                $exceptions++;
            } else {
                $matched++;
            }
        }

        $run->update([
            'status' => 'completed',
            'matched_count' => $matched,
            'unmatched_count' => $unmatched,
            'exception_count' => $exceptions,
            'finished_at' => now(),
            'summary' => compact('matched', 'unmatched', 'exceptions'),
        ]);

        return $run->fresh('items');
    }

    private function item(
        ReconciliationRun $run,
        string $type,
        string $severity,
        ?int $paymentRequestId = null,
        ?int $blockchainTransactionId = null,
        ?int $merchantId = null,
        array $payload = [],
    ): void {
        ReconciliationItem::query()->create([
            'reconciliation_run_id' => $run->id,
            'type' => $type,
            'severity' => $severity,
            'payment_request_id' => $paymentRequestId,
            'blockchain_transaction_id' => $blockchainTransactionId,
            'merchant_id' => $merchantId,
            'payload' => $payload,
        ]);
    }
}
