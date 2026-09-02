<?php

namespace App\Domain\Wallets;

use App\Domain\Blockchain\Contracts\WalletManagerInterface;
use App\Domain\Shared\PublicId;
use App\Models\Wallet;
use Illuminate\Support\Str;

final class WalletManager implements WalletManagerInterface
{
    public function provisionNetworkWallet(int $networkId, string $label, string $custodyBackend = 'mock'): Wallet
    {
        return Wallet::query()->firstOrCreate(
            [
                'network_id' => $networkId,
                'merchant_id' => null,
                'custody_backend' => $custodyBackend,
            ],
            [
                'public_id' => PublicId::make('WAL'),
                'label' => $label,
                'key_ref' => 'vault:ref:'.Str::ulid(),
                'status' => 'active',
                'next_derivation_index' => 0,
            ],
        );
    }

    public function nextDerivationIndex(Wallet $wallet): int
    {
        $wallet->refresh();
        $index = $wallet->next_derivation_index;
        $wallet->next_derivation_index = $index + 1;
        $wallet->save();

        return $index;
    }
}
