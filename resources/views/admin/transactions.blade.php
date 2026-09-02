<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Chain transactions</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="Observed transfers">
                @if ($transactions->isEmpty())
                    <x-empty-state title="No chain activity" body="The monitor writes every detected transfer here, including unmatched and duplicate-safe hashes." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Hash</th>
                                    <th class="px-5 py-3">Network</th>
                                    <th class="px-5 py-3">From</th>
                                    <th class="px-5 py-3">To</th>
                                    <th class="px-5 py-3">Amount</th>
                                    <th class="px-5 py-3">Block</th>
                                    <th class="px-5 py-3">Conf</th>
                                    <th class="px-5 py-3">Processing</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($transactions as $tx)
                                    <tr>
                                        <td class="px-5 py-3 font-mono text-xs">{{ $tx->tx_hash }}</td>
                                        <td class="px-5 py-3">{{ $tx->network->code }}</td>
                                        <td class="px-5 py-3 font-mono text-xs">{{ $tx->from_address }}</td>
                                        <td class="px-5 py-3 font-mono text-xs">{{ $tx->to_address }}</td>
                                        <td class="px-5 py-3">{{ $tx->amount_minor }}</td>
                                        <td class="px-5 py-3">{{ $tx->block_number }}</td>
                                        <td class="px-5 py-3">{{ $tx->confirmations }}</td>
                                        <td class="px-5 py-3"><x-status-badge :status="$tx->processing_status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $transactions->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
