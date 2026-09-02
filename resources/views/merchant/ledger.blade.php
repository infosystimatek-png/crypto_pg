<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Ledger</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @foreach ($balances as $balance)
            <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
                {{ $balance->asset->code }} available {{ (new \App\Domain\Shared\Money($balance->available_minor, $balance->asset->decimals, $balance->asset->code))->toFixed() }}
                · pending {{ (new \App\Domain\Shared\Money($balance->pending_minor, $balance->asset->decimals, $balance->asset->code))->toFixed() }}
            </div>
        @endforeach
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4 text-sm">
            Journal is the source of truth. Projection balances above are cached and reconciled hourly.
            @foreach ($entries as $account)
                <div class="mt-2">{{ $account->code }} ({{ $account->type }})</div>
            @endforeach
        </div>
    </div>
</x-app-layout>
