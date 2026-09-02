<?php

namespace App\Domain\Wallets;

use App\Domain\Blockchain\Contracts\AddressManagerInterface;
use App\Domain\Blockchain\Contracts\WalletManagerInterface;
use App\Domain\Shared\PublicId;
use App\Models\PaymentAddress;
use App\Models\PaymentRequest;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

final class AddressManager implements AddressManagerInterface
{
    public function __construct(private readonly WalletManagerInterface $wallets) {}

    public function allocateForPayment(PaymentRequest $payment): PaymentAddress
    {
        $wallet = $this->wallets->provisionNetworkWallet(
            $payment->network_id,
            'Platform '.$payment->network->code.' deposit wallet',
            'mock',
        );

        $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
        $index = $this->wallets->nextDerivationIndex($wallet);
        $address = $this->deriveAddress($wallet, $index);

        $record = PaymentAddress::query()->create([
            'public_id' => PublicId::make('ADDR'),
            'wallet_id' => $wallet->id,
            'network_id' => $payment->network_id,
            'merchant_id' => $payment->merchant_id,
            'payment_request_id' => $payment->id,
            'address' => $address,
            'derivation_index' => $index,
            'derivation_path' => "m/44'/195'/0'/0/{$index}",
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        Log::info('payment.address_assigned', [
            'payment_id' => $payment->public_id,
            'merchant_id' => $payment->merchant->public_id,
            'address' => $address,
            'derivation_index' => $index,
            'correlation_id' => $payment->correlation_id,
        ]);

        return $record;
    }

    /**
     * Public address only. Private key material never leaves the custody backend.
     * V1 mock uses a deterministic HMAC over an opaque key_ref, not a seed phrase.
     */
    private function deriveAddress(Wallet $wallet, int $index): string
    {
        $material = hash_hmac('sha256', $wallet->id.'|'.$index, (string) $wallet->key_ref);
        $body = strtoupper(substr($material, 0, 33));

        return 'T'.$body;
    }
}
