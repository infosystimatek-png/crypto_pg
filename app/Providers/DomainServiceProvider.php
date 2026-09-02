<?php

namespace App\Providers;

use App\Domain\Blockchain\Adapters\DisabledTransactionBroadcaster;
use App\Domain\Blockchain\Adapters\MockBlockchainAdapter;
use App\Domain\Blockchain\Adapters\TronGridAdapter;
use App\Domain\Blockchain\BlockchainAdapterRegistry;
use App\Domain\Blockchain\Contracts\AddressManagerInterface;
use App\Domain\Blockchain\Contracts\TransactionBroadcasterInterface;
use App\Domain\Blockchain\Contracts\TransactionMonitorInterface;
use App\Domain\Blockchain\Contracts\WalletManagerInterface;
use App\Domain\Blockchain\TransactionMonitor;
use App\Domain\Wallets\AddressManager;
use App\Domain\Wallets\WalletManager;
use App\Models\Merchant;
use App\Models\PaymentRequest;
use App\Policies\MerchantPolicy;
use App\Policies\PaymentRequestPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WalletManagerInterface::class, WalletManager::class);
        $this->app->singleton(AddressManagerInterface::class, AddressManager::class);
        $this->app->singleton(TransactionMonitorInterface::class, TransactionMonitor::class);
        $this->app->singleton(TransactionBroadcasterInterface::class, DisabledTransactionBroadcaster::class);
        $this->app->singleton(MockBlockchainAdapter::class);
        $this->app->singleton(TronGridAdapter::class);

        $this->app->singleton(BlockchainAdapterRegistry::class, function ($app) {
            return new BlockchainAdapterRegistry(
                [
                    $app->make(MockBlockchainAdapter::class),
                    $app->make(TronGridAdapter::class),
                ],
                $app->make(TransactionBroadcasterInterface::class),
            );
        });
    }

    public function boot(): void
    {
        Gate::policy(PaymentRequest::class, PaymentRequestPolicy::class);
        Gate::policy(Merchant::class, MerchantPolicy::class);

        RateLimiter::for('merchant-api', function (Request $request) {
            $key = $request->attributes->get('api_credential')?->key_prefix ?: $request->ip();

            return Limit::perMinute(60)->by($key);
        });
    }
}
