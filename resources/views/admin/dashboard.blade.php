<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Operations</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Admin overview</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-stat-card label="Merchants">{{ $merchants }}</x-stat-card>
                <x-stat-card label="Payments">{{ $payments }}</x-stat-card>
                <x-stat-card label="Credited" :hint="$openPayments.' still open'">{{ $credited }}</x-stat-card>
                <x-stat-card label="Webhook failures">{{ $failedWebhooks }}</x-stat-card>
            </div>
            <x-panel title="Latest payments">
                <x-slot name="action"><a href="{{ route('admin.payments') }}" class="text-sm font-medium text-indigo-600">Explorer</a></x-slot>
                @if ($recent->isEmpty())
                    <x-empty-state title="No gateway activity yet" body="When merchants create invoices, they appear here with status, asset, and merchant." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Payment</th>
                                    <th class="px-5 py-3">Merchant</th>
                                    <th class="px-5 py-3">Amount</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recent as $payment)
                                    <tr>
                                        <td class="px-5 py-3"><a class="font-medium text-indigo-600" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->public_id }}</a></td>
                                        <td class="px-5 py-3">{{ $payment->merchant->name }}</td>
                                        <td class="px-5 py-3"><x-money :minor="$payment->amount_minor" :asset="$payment->asset" /></td>
                                        <td class="px-5 py-3"><x-status-badge :status="$payment->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
