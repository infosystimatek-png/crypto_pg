<?php

namespace App\Domain\Blockchain;

use App\Domain\Blockchain\Adapters\MockBlockchainAdapter;
use App\Domain\Blockchain\DTO\IncomingTransaction;
use App\Domain\Ledger\LedgerService;
use App\Domain\Payments\PaymentStateMachine;
use App\Domain\Payments\PaymentStatus;
use App\Domain\Shared\Money;
use App\Domain\Shared\PublicId;
use App\Domain\Webhooks\WebhookDispatcher;
use App\Models\BlockchainAsset;
use App\Models\BlockchainNetwork;
use App\Models\BlockchainTransaction;
use App\Models\BlockchainTransactionConfirmation;
use App\Models\PaymentAddress;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class TransactionProcessor
{
    public function __construct(
        private readonly PaymentStateMachine $states,
        private readonly LedgerService $ledger,
        private readonly WebhookDispatcher $webhooks,
        private readonly MockBlockchainAdapter $mock,
    ) {}

    public function process(int $networkId, array $payload): void
    {
        $incoming = new IncomingTransaction(
            networkCode: $payload['networkCode'],
            assetCode: $payload['assetCode'],
            txHash: $payload['txHash'],
            logIndex: (int) $payload['logIndex'],
            fromAddress: $payload['fromAddress'],
            toAddress: $payload['toAddress'],
            amountDecimal: $payload['amountDecimal'],
            blockNumber: $payload['blockNumber'] ?? null,
            confirmations: (int) $payload['confirmations'],
            contractAddress: $payload['contractAddress'] ?? null,
            raw: $payload['raw'] ?? [],
        );

        DB::transaction(function () use ($networkId, $incoming) {
            $network = BlockchainNetwork::query()->findOrFail($networkId);

            $existing = BlockchainTransaction::query()
                ->where('network_id', $network->id)
                ->where('tx_hash', $incoming->txHash)
                ->where('log_index', $incoming->logIndex)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $this->refreshConfirmations($existing, $incoming);
                $this->mock->markConsumed($incoming->txHash);

                return;
            }

            $asset = BlockchainAsset::query()
                ->where('network_id', $network->id)
                ->where('code', strtoupper($incoming->assetCode))
                ->first();

            $address = PaymentAddress::query()
                ->where('network_id', $network->id)
                ->where('address', $incoming->toAddress)
                ->lockForUpdate()
                ->first();

            $tx = BlockchainTransaction::query()->create([
                'public_id' => PublicId::make('BTX'),
                'network_id' => $network->id,
                'asset_id' => $asset?->id,
                'payment_request_id' => $address?->payment_request_id,
                'tx_hash' => $incoming->txHash,
                'log_index' => $incoming->logIndex,
                'from_address' => $incoming->fromAddress,
                'to_address' => $incoming->toAddress,
                'amount_minor' => $asset
                    ? Money::fromDecimal($incoming->amountDecimal, $asset->decimals, $asset->code)->amountMinor
                    : '0',
                'block_number' => $incoming->blockNumber,
                'confirmations' => $incoming->confirmations,
                'status' => 'detected',
                'processing_status' => 'pending',
                'raw_payload' => $incoming->raw,
                'first_seen_at' => now(),
            ]);

            BlockchainTransactionConfirmation::query()->create([
                'blockchain_transaction_id' => $tx->id,
                'confirmations' => $incoming->confirmations,
                'block_number' => $incoming->blockNumber,
                'observed_at' => now(),
            ]);

            if (! $address || ! $address->payment_request_id) {
                $tx->update(['processing_status' => 'unmatched']);
                $this->mock->markConsumed($incoming->txHash);

                return;
            }

            $payment = PaymentRequest::query()->whereKey($address->payment_request_id)->lockForUpdate()->firstOrFail();
            $this->matchAndAdvance($payment, $tx, $incoming, $network, $asset);
            $this->mock->markConsumed($incoming->txHash);
        });
    }

    private function refreshConfirmations(BlockchainTransaction $tx, IncomingTransaction $incoming): void
    {
        if ($incoming->confirmations <= $tx->confirmations && $incoming->blockNumber === $tx->block_number) {
            return;
        }

        $tx->confirmations = $incoming->confirmations;
        $tx->block_number = $incoming->blockNumber ?? $tx->block_number;
        $tx->save();

        BlockchainTransactionConfirmation::query()->create([
            'blockchain_transaction_id' => $tx->id,
            'confirmations' => $incoming->confirmations,
            'block_number' => $incoming->blockNumber,
            'observed_at' => now(),
        ]);

        if (! $tx->payment_request_id) {
            return;
        }

        $payment = PaymentRequest::query()->whereKey($tx->payment_request_id)->lockForUpdate()->first();
        if (! $payment) {
            return;
        }

        $payment->confirmations = $incoming->confirmations;
        $payment->save();

        if (in_array($payment->status, [PaymentStatus::TransactionDetected, PaymentStatus::Confirming], true)
            && $incoming->confirmations >= $payment->required_confirmations
            && in_array($tx->processing_status, ['matched_exact', 'matched_overpaid'], true)
        ) {
            $this->finalize($payment, $tx);
        }
    }

    private function matchAndAdvance(
        PaymentRequest $payment,
        BlockchainTransaction $tx,
        IncomingTransaction $incoming,
        BlockchainNetwork $network,
        ?BlockchainAsset $asset,
    ): void {
        if (strtoupper($incoming->networkCode) !== strtoupper($network->code)
            || $payment->network_id !== $network->id) {
            $this->failMatch($payment, $tx, PaymentStatus::WrongNetwork, 'unmatched_network');

            return;
        }

        if (! $asset || $asset->id !== $payment->asset_id) {
            $this->failMatch($payment, $tx, PaymentStatus::WrongAsset, 'unmatched_asset');

            return;
        }

        $received = new Money($tx->amount_minor, $asset->decimals, $asset->code);
        $expected = $payment->expectedMoney();

        $payment->received_amount_minor = $received->amountMinor;
        $payment->blockchain_transaction_id = $tx->id;
        $payment->detected_at = now();
        $payment->confirmations = $incoming->confirmations;
        $payment->save();

        $detectedFrom = [PaymentStatus::WaitingForPayment, PaymentStatus::Underpaid];
        if (in_array($payment->status, $detectedFrom, true)) {
            $this->states->transition($payment->fresh(), PaymentStatus::TransactionDetected);
            $payment->refresh();
        }

        if ($received->lessThan($expected)) {
            $tx->update(['processing_status' => 'matched_underpaid']);
            if ($payment->status->canTransitionTo(PaymentStatus::Underpaid)) {
                $this->states->transition($payment, PaymentStatus::Underpaid);
            }
            $this->webhooks->dispatch($payment->fresh(['merchant', 'asset', 'network', 'blockchainTransaction', 'paymentAddress']), 'payment.underpaid');

            return;
        }

        $this->ledger->creditPending($payment, $expected, 'payment:'.$payment->id.':pending');

        $overpaid = $received->greaterThan($expected);
        $tx->update(['processing_status' => $overpaid ? 'matched_overpaid' : 'matched_exact']);

        if ($payment->status === PaymentStatus::TransactionDetected) {
            $this->states->transition($payment, PaymentStatus::Confirming);
            $payment->refresh();
        }

        if ($overpaid) {
            $excess = $received->minus($expected);
            $this->ledger->recordOverpayment($payment, $excess, 'payment:'.$payment->id.':overpayment');
        }

        if ($incoming->confirmations >= $payment->required_confirmations) {
            $this->finalize($payment->fresh(), $tx);
        }
    }

    private function finalize(PaymentRequest $payment, BlockchainTransaction $tx): void
    {
        $payment->refresh();

        if ($payment->status === PaymentStatus::Confirming) {
            $next = $tx->processing_status === 'matched_overpaid' ? PaymentStatus::Overpaid : PaymentStatus::Confirmed;
            $this->states->transition($payment, $next, [
                'confirmed_at' => now(),
                'confirmations' => $tx->confirmations,
            ]);
            $payment->refresh();
            $tx->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        }

        if (in_array($payment->status, [PaymentStatus::Confirmed, PaymentStatus::Overpaid], true)
            && $payment->credited_at === null
        ) {
            $this->ledger->creditAvailable(
                $payment,
                $payment->expectedMoney(),
                'payment:'.$payment->id.':available',
            );

            if ($payment->status === PaymentStatus::Overpaid) {
                $this->states->transition($payment, PaymentStatus::Credited, ['credited_at' => now()]);
                $payment->refresh();
                $this->webhooks->dispatch($payment->fresh(['merchant', 'asset', 'network', 'blockchainTransaction', 'paymentAddress']), 'payment.overpaid');
            } else {
                $this->states->transition($payment, PaymentStatus::Credited, ['credited_at' => now()]);
                $payment->refresh();
            }

            $this->webhooks->dispatch($payment->fresh(['merchant', 'asset', 'network', 'blockchainTransaction', 'paymentAddress']), 'payment.confirmed');
            $tx->update(['processing_status' => 'processed']);
        }
    }

    private function failMatch(PaymentRequest $payment, BlockchainTransaction $tx, PaymentStatus $status, string $processing): void
    {
        $tx->update(['processing_status' => $processing]);
        if ($payment->status->canTransitionTo($status)) {
            $this->states->transition($payment, $status);
        }

        Log::warning('payment.match_failed', [
            'payment_id' => $payment->public_id,
            'tx_hash' => $tx->tx_hash,
            'reason' => $processing,
            'correlation_id' => $payment->correlation_id,
        ]);
    }
}
