<?php

namespace Database\Seeders;

use App\Models\BlockchainAsset;
use App\Models\BlockchainNetwork;
use Illuminate\Database\Seeder;

class BlockchainCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $tron = BlockchainNetwork::query()->updateOrCreate(
            ['code' => 'TRON'],
            [
                'name' => 'TRON Nile Testnet',
                'chain_id' => 'nile',
                'is_testnet' => true,
                'is_enabled' => true,
                'confirmation_threshold' => 19,
                'adapter' => 'mock',
                'explorer_url' => 'https://nile.tronscan.org',
                'native_symbol' => 'TRX',
            ],
        );

        BlockchainAsset::query()->updateOrCreate(
            [
                'network_id' => $tron->id,
                'code' => 'USDT',
            ],
            [
                'name' => 'Tether USD (TRC-20)',
                'contract_address' => 'TXLAQ63Xg1NAzckPwKHvzw7CSEmLMEqcdj',
                'decimals' => 6,
                'is_enabled' => true,
            ],
        );
    }
}
