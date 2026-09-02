<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Merchant</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $merchant->name }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <div class="grid gap-4 lg:grid-cols-3">
                <x-stat-card label="Public ID">{{ $merchant->public_id }}</x-stat-card>
                <x-stat-card label="Status"><x-status-badge :status="$merchant->status" /></x-stat-card>
                <x-stat-card label="API keys">{{ $merchant->apiCredentials->count() }}</x-stat-card>
            </div>
            <x-panel title="Credentials">
                <div class="space-y-2 px-5 py-5 text-sm">
                    <div>Key prefixes: <span class="font-mono">{{ $merchant->apiCredentials->pluck('key_prefix')->join(', ') ?: '—' }}</span></div>
                    <div>Webhooks: {{ $merchant->webhookEndpoints->pluck('url')->join(', ') ?: '—' }}</div>
                </div>
            </x-panel>
            <x-panel title="Auditable adjustment">
                <form method="POST" action="{{ route('admin.merchants.adjust', $merchant) }}" class="grid gap-3 px-5 py-5 md:grid-cols-5">
                    @csrf
                    <select name="asset_id" class="rounded-xl border-slate-200 text-sm">
                        @foreach (\App\Models\BlockchainAsset::query()->get() as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->code }}</option>
                        @endforeach
                    </select>
                    <select name="direction" class="rounded-xl border-slate-200 text-sm"><option value="credit">credit</option><option value="debit">debit</option></select>
                    <input name="amount" placeholder="Amount" class="rounded-xl border-slate-200 text-sm" required>
                    <input name="reason" placeholder="Reason" class="rounded-xl border-slate-200 text-sm" required>
                    <button class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">Post journal</button>
                </form>
                <p class="px-5 pb-5 text-xs text-slate-500">Balances cannot be edited in place. This always appends a new balanced journal entry.</p>
            </x-panel>
        </div>
    </div>
</x-app-layout>
