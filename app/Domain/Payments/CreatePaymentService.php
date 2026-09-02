<?php

namespace App\Domain\Payments;

use App\Domain\Audit\AuditLogger;
use App\Domain\Blockchain\Contracts\AddressManagerInterface;
use App\Domain\Shared\Money;
use App\Domain\Shared\PublicId;
use App\Models\BlockchainAsset;
use App\Models\IdempotencyKey;
use App\Models\Merchant;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreatePaymentService
{
    public function __construct(
        private readonly AddressManagerInterface $addresses,
        private readonly PaymentStateMachine $states,
        private readonly AuditLogger $audit,
        private readonly QrPayloadFactory $qr,
    ) {}

    /**
     * @param  array{merchant_order_id: string, amount: string, currency: string, network: string, callback_url?: string}  $input
     */
    public function create(Merchant $merchant, array $input, ?string $idempotencyKey, string $requestHash): PaymentRequest
    {
        if (! $merchant->isActive()) {
            throw ValidationException::withMessages(['merchant' => 'Merchant is not active.']);
        }

        return DB::transaction(function () use ($merchant, $input, $idempotencyKey, $requestHash) {
            if ($idempotencyKey) {
                $existing = $this->claimIdempotency($merchant, $idempotencyKey, $requestHash);
                if ($existing instanceof PaymentRequest) {
                    return $existing;
                }
            }

            $asset = BlockchainAsset::query()
                ->where('code', strtoupper($input['currency']))
                ->where('is_enabled', true)
                ->whereHas('network', function ($q) use ($input) {
                    $q->where('code', strtoupper($input['network']))->where('is_enabled', true);
                })
                ->with('network')
                ->first();

            if (! $asset) {
                throw ValidationException::withMessages([
                    'currency' => 'Asset/network combination is not enabled.',
                ]);
            }

            $duplicateOrder = PaymentRequest::query()
                ->where('merchant_id', $merchant->id)
                ->where('merchant_order_id', $input['merchant_order_id'])
                ->first();

            if ($duplicateOrder) {
                throw ValidationException::withMessages([
                    'merchant_order_id' => 'This merchant_order_id already exists.',
                ]);
            }

            $money = Money::fromDecimal($input['amount'], $asset->decimals, $asset->code);
            if (! $money->isPositive()) {
                throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
            }

            $payment = PaymentRequest::query()->create([
                'public_id' => PublicId::make('PAY'),
                'merchant_id' => $merchant->id,
                'merchant_order_id' => $input['merchant_order_id'],
                'network_id' => $asset->network_id,
                'asset_id' => $asset->id,
                'amount_minor' => $money->amountMinor,
                'received_amount_minor' => '0',
                'status' => PaymentStatus::Created,
                'callback_url' => $input['callback_url'] ?? $merchant->default_callback_url,
                'required_confirmations' => $asset->network->confirmation_threshold,
                'confirmations' => 0,
                'correlation_id' => (string) Str::ulid(),
                'expires_at' => now()->addMinutes((int) config('gateway.payment_ttl_minutes', 30)),
            ]);

            $address = $this->addresses->allocateForPayment($payment->load('network', 'merchant'));
            $qr = $this->qr->make($payment->fresh(['network', 'asset']), $address->address, $money);

            $this->states->transition($payment, PaymentStatus::WaitingForPayment, [
                'payment_address_id' => $address->id,
                'qr_payload' => $qr,
            ]);

            if ($idempotencyKey) {
                IdempotencyKey::query()
                    ->where('merchant_id', $merchant->id)
                    ->where('key', $idempotencyKey)
                    ->update(['payment_request_id' => $payment->id]);
            }

            $this->audit->log('payment.created', $payment, [
                'merchant_id' => $merchant->public_id,
                'amount' => $money->toFixed(),
                'asset' => $asset->code,
                'network' => $asset->network->code,
            ], 'merchant', correlationId: $payment->correlation_id);

            return $payment->fresh(['paymentAddress', 'asset', 'network', 'merchant']);
        });
    }

    private function claimIdempotency(Merchant $merchant, string $key, string $requestHash): ?PaymentRequest
    {
        $record = IdempotencyKey::query()
            ->where('merchant_id', $merchant->id)
            ->where('key', $key)
            ->lockForUpdate()
            ->first();

        if ($record) {
            if ($record->request_hash !== $requestHash) {
                throw ValidationException::withMessages([
                    'Idempotency-Key' => 'Idempotency key reused with a different payload.',
                ]);
            }

            if ($record->payment_request_id) {
                return PaymentRequest::query()
                    ->with(['paymentAddress', 'asset', 'network', 'merchant'])
                    ->findOrFail($record->payment_request_id);
            }

            return null;
        }

        IdempotencyKey::query()->create([
            'merchant_id' => $merchant->id,
            'key' => $key,
            'request_hash' => $requestHash,
            'locked_at' => now(),
        ]);

        return null;
    }
}
