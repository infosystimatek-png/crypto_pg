<?php

namespace App\Domain\Blockchain\Contracts;

use App\Models\Wallet;

interface WalletManagerInterface
{
    public function provisionNetworkWallet(int $networkId, string $label, string $custodyBackend = 'mock'): Wallet;

    public function nextDerivationIndex(Wallet $wallet): int;
}
