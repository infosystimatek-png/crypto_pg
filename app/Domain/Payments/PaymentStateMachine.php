<?php

namespace App\Domain\Payments;

use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Log;

final class PaymentStateMachine
{
    public function transition(PaymentRequest $payment, PaymentStatus $to, array $attributes = []): PaymentRequest
    {
        $from = $payment->status instanceof PaymentStatus
            ? $payment->status
            : PaymentStatus::from((string) $payment->status);

        if (! $from->canTransitionTo($to)) {
            throw InvalidPaymentTransition::from($from, $to);
        }

        $payment->fill($attributes);
        $payment->status = $to;
        $payment->save();

        Log::info('payment.status_changed', [
            'payment_id' => $payment->public_id,
            'merchant_id' => $payment->merchant?->public_id,
            'from' => $from->value,
            'to' => $to->value,
            'correlation_id' => $payment->correlation_id,
        ]);

        return $payment;
    }
}
