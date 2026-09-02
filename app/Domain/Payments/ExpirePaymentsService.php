<?php

namespace App\Domain\Payments;

use App\Domain\Audit\AuditLogger;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Log;

final class ExpirePaymentsService
{
    public function __construct(
        private readonly PaymentStateMachine $states,
        private readonly AuditLogger $audit,
    ) {}

    public function expireDue(): int
    {
        $count = 0;

        PaymentRequest::query()
            ->where('status', PaymentStatus::WaitingForPayment->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($payments) use (&$count) {
                foreach ($payments as $payment) {
                    try {
                        $this->states->transition($payment, PaymentStatus::Expired);
                        $this->audit->log('payment.expired', $payment, correlationId: $payment->correlation_id);
                        $count++;
                    } catch (InvalidPaymentTransition $e) {
                        Log::warning('payment.expire_skipped', [
                            'payment_id' => $payment->public_id,
                            'status' => $payment->status,
                        ]);
                    }
                }
            });

        return $count;
    }
}
