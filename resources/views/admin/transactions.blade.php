<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Blockchain transactions</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('admin.nav')
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-3 py-2">Hash</th><th class="px-3 py-2">Network</th><th class="px-3 py-2">From</th><th class="px-3 py-2">To</th><th class="px-3 py-2">Amount</th><th class="px-3 py-2">Block</th><th class="px-3 py-2">Conf</th><th class="px-3 py-2">Processing</th>
                </tr></thead>
                <tbody>
                @foreach ($transactions as $tx)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2 font-mono text-xs">{{ $tx->tx_hash }}</td>
                        <td class="px-3 py-2">{{ $tx->network->code }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $tx->from_address }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $tx->to_address }}</td>
                        <td class="px-3 py-2">{{ $tx->amount_minor }}</td>
                        <td class="px-3 py-2">{{ $tx->block_number }}</td>
                        <td class="px-3 py-2">{{ $tx->confirmations }}</td>
                        <td class="px-3 py-2">{{ $tx->processing_status }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $transactions->links() }}</div>
        </div>
    </div>
</x-app-layout>
