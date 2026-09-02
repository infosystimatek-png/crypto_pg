<?php

namespace App\Domain\Blockchain\Contracts;

use App\Models\PaymentAddress;
use App\Models\PaymentRequest;

interface AddressManagerInterface
{
    public function allocateForPayment(PaymentRequest $payment): PaymentAddress;
}
