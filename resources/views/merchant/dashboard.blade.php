<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $merchant->name ?? 'Dashboard' }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @forelse ($balances as $balance)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="text-sm text-gray-500">{{ $balance->asset->code }} available</div>
                        <div class="text-2xl font-semibold">{{ \App\Domain\Shared\Money::fromDecimal('0', $balance->asset->decimals, $balance->asset->code)->amountMinor === 'x' ? '' : (new \App\Domain\Shared\Money($balance->available_minor, $balance->asset->decimals, $balance->asset->code))->toFixed() }}</div>
                        <div class="text-sm">Pending {{ (new \App\Domain\Shared\Money($balance->pending_minor, $balance->asset->decimals, $balance->asset->code))->toFixed() }}</div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                        <div class="text-sm text-gray-500">Available balance</div>
                        <div class="text-2xl font-semibold">0.000000</div>
                        <div class="text-sm">Pending 0.000000</div>
                    </div>
                @endforelse
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4"><div class="text-sm text-gray-500">Today</div><div class="text-2xl">{{ $today }}</div></div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4"><div class="text-sm text-gray-500">Successful</div><div class="text-2xl">{{ $successful }}</div></div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4"><div class="text-sm text-gray-500">Pending / Expired</div><div class="text-2xl">{{ $pending }} / {{ $expired }}</div></div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-left">
                    <tr>
                        <th class="px-4 py-2">Payment</th>
                        <th class="px-4 py-2">Order</th>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Address</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($recent as $payment)
                        <tr class="border-t dark:border-gray-700">
                            <td class="px-4 py-2"><a class="text-indigo-600" href="{{ route('merchant.payments.show', $payment) }}">{{ $payment->public_id }}</a></td>
                            <td class="px-4 py-2">{{ $payment->merchant_order_id }}</td>
                            <td class="px-4 py-2">{{ $payment->expectedMoney()->toFixed() }} {{ $payment->asset->code }}</td>
                            <td class="px-4 py-2">{{ $payment->status->value }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $payment->paymentAddress?->address }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
