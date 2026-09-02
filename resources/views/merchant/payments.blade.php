<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Payments</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-4 py-2">ID</th><th class="px-4 py-2">Order</th><th class="px-4 py-2">Amount</th><th class="px-4 py-2">Network</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Hash</th>
                </tr></thead>
                <tbody>
                @foreach ($payments as $payment)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-4 py-2"><a class="text-indigo-600" href="{{ route('merchant.payments.show', $payment) }}">{{ $payment->public_id }}</a></td>
                        <td class="px-4 py-2">{{ $payment->merchant_order_id }}</td>
                        <td class="px-4 py-2">{{ $payment->expectedMoney()->toFixed() }} {{ $payment->asset->code }}</td>
                        <td class="px-4 py-2">{{ $payment->network->code }}</td>
                        <td class="px-4 py-2">{{ $payment->status->value }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ $payment->blockchainTransaction?->tx_hash }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $payments->links() }}</div>
        </div>
    </div>
</x-app-layout>
