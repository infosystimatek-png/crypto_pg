<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    public function view(User $user, PaymentRequest $payment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->merchants()->where('merchants.id', $payment->merchant_id)->exists();
    }
}
