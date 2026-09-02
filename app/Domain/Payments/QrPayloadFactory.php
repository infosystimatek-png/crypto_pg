<?php

namespace App\Domain\Payments;

use App\Domain\Shared\Money;
use App\Models\PaymentRequest;

final class QrPayloadFactory
{
    public function make(PaymentRequest $payment, string $address, Money $amount): string
    {
        $network = strtolower($payment->network->code);
        $asset = strtoupper($payment->asset->code);

        return sprintf(
            '%s:%s?amount=%s&asset=%s&order=%s',
            $network,
            $address,
            $amount->toFixed(),
            $asset,
            rawurlencode($payment->merchant_order_id),
        );
    }
}
