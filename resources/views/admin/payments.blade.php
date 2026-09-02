<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Payments</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="All invoices">
                @if ($payments->isEmpty())
                    <x-empty-state title="No payments" body="Merchant API traffic will land here with address, asset, and confirmation state." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">ID</th>
                                    <th class="px-5 py-3">Merchant</th>
                                    <th class="px-5 py-3">Order</th>
                                    <th class="px-5 py-3">Amount</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Address</th>
                                    <th class="px-5 py-3">Hash</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td class="px-5 py-3"><a class="font-medium text-indigo-600" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->public_id }}</a></td>
                                        <td class="px-5 py-3">{{ $payment->merchant->name }}</td>
                                        <td class="px-5 py-3">{{ $payment->merchant_order_id }}</td>
                                        <td class="px-5 py-3"><x-money :minor="$payment->amount_minor" :asset="$payment->asset" /></td>
                                        <td class="px-5 py-3"><x-status-badge :status="$payment->status" /></td>
                                        <td class="px-5 py-3 font-mono text-xs">{{ $payment->paymentAddress?->address }}</td>
                                        <td class="px-5 py-3 font-mono text-xs">{{ $payment->blockchainTransaction?->tx_hash }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $payments->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
