<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $merchant->name }}</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 text-sm">
        @include('admin.nav')
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow space-y-1">
            <div>ID {{ $merchant->public_id }} · {{ $merchant->status }}</div>
            <div>API prefixes: {{ $merchant->apiCredentials->pluck('key_prefix')->join(', ') }}</div>
            <div>Webhooks: {{ $merchant->webhookEndpoints->pluck('url')->join(', ') }}</div>
        </div>
        <form method="POST" action="{{ route('admin.merchants.adjust', $merchant) }}" class="bg-white dark:bg-gray-800 p-4 rounded shadow grid md:grid-cols-5 gap-3">
            @csrf
            <select name="asset_id" class="border rounded px-2 py-1 dark:bg-gray-900">
                @foreach (\App\Models\BlockchainAsset::query()->get() as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->code }}</option>
                @endforeach
            </select>
            <select name="direction" class="border rounded px-2 py-1 dark:bg-gray-900"><option value="credit">credit</option><option value="debit">debit</option></select>
            <input name="amount" placeholder="Amount" class="border rounded px-2 py-1 dark:bg-gray-900" required>
            <input name="reason" placeholder="Reason" class="border rounded px-2 py-1 dark:bg-gray-900" required>
            <button class="bg-indigo-600 text-white rounded px-3">Post adjustment</button>
        </form>
        <p class="text-gray-500">Balances cannot be edited in place. Adjustments always create new journal entries.</p>
    </div>
</x-app-layout>
