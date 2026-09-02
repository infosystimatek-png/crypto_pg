<?php

namespace App\Http\Resources;

use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PaymentRequest */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment_id' => $this->public_id,
            'merchant_order_id' => $this->merchant_order_id,
            'amount' => $this->expectedMoney()->toFixed(),
            'received_amount' => $this->receivedMoney()->toFixed(),
            'currency' => $this->asset->code,
            'network' => $this->network->code,
            'payment_address' => $this->paymentAddress?->address,
            'status' => $this->status->value,
            'expires_at' => optional($this->expires_at)?->toIso8601String(),
            'qr_data' => $this->qr_payload,
            'transaction_hash' => $this->blockchainTransaction?->tx_hash,
            'confirmations' => $this->confirmations,
            'required_confirmations' => $this->required_confirmations,
            'created_at' => $this->created_at?->toIso8601String(),
            'confirmed_at' => optional($this->confirmed_at)?->toIso8601String(),
            'credited_at' => optional($this->credited_at)?->toIso8601String(),
        ];
    }
}
