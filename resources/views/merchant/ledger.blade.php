<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Merchant</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Ledger</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                @forelse ($balances as $balance)
                    <x-stat-card :label="$balance->asset->code" :hint="'Pending '.(new \App\Domain\Shared\Money($balance->pending_minor, $balance->asset->decimals, $balance->asset->code))->toFixed()">
                        Available <x-money :minor="$balance->available_minor" :asset="$balance->asset" />
                    </x-stat-card>
                @empty
                    <x-stat-card label="USDT" hint="Projection fills after the first credit">0.000000</x-stat-card>
                @endforelse
            </div>
            <x-panel title="Accounts">
                @if ($entries->isEmpty())
                    <x-empty-state title="No ledger accounts yet" body="Accounts are opened automatically when a payment is credited. The journal is the source of truth; these balances are a projection." />
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($entries as $account)
                            <div class="flex items-center justify-between px-5 py-3 text-sm">
                                <div>
                                    <div class="font-medium">{{ $account->name }}</div>
                                    <div class="font-mono text-xs text-slate-500">{{ $account->code }}</div>
                                </div>
                                <x-status-badge :status="$account->type" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
