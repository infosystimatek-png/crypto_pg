<?php

namespace Database\Seeders;

use App\Domain\Blockchain\Contracts\WalletManagerInterface;
use App\Domain\Merchants\MerchantProvisioningService;
use App\Models\BlockchainNetwork;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BlockchainCatalogSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@gateway.test'],
            [
                'name' => 'Gateway Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        $owner = User::query()->firstOrCreate(
            ['email' => 'merchant@gateway.test'],
            [
                'name' => 'Demo Merchant',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        $provisioning = app(MerchantProvisioningService::class);
        $created = $provisioning->create(
            'Demo Merchant',
            $owner,
            'https://merchant.example.test/webhooks/payment',
        );

        app(WalletManagerInterface::class)->provisionNetworkWallet(
            BlockchainNetwork::query()->where('code', 'TRON')->value('id'),
            'Platform TRON deposit wallet',
        );

        $this->command?->info('Admin: admin@gateway.test / password');
        $this->command?->info('Merchant user: merchant@gateway.test / password');
        $this->command?->info('Merchant ID: '.$created['merchant']->public_id);
        $this->command?->warn('API key (store now, shown once): '.$created['api_key']);
        $this->command?->warn('Webhook secret (store now, shown once): '.$created['webhook_secret']);
    }
}
