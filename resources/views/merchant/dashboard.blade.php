<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Merchant</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $merchant->name ?? 'Dashboard' }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @unless ($merchant)
                <x-panel>
                    <x-empty-state title="No merchant workspace yet" body="This login is not linked to a merchant. Ask an admin to provision one, or sign in as merchant@gateway.test after seeding." />
                </x-panel>
            @endunless

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($balances as $balance)
                    <x-stat-card :label="$balance->asset->code.' available'" :hint="'Pending '.(new \App\Domain\Shared\Money($balance->pending_minor, $balance->asset->decimals, $balance->asset->code))->toFixed()">
                        <x-money :minor="$balance->available_minor" :asset="$balance->asset" />
                    </x-stat-card>
                @empty
                    <x-stat-card label="Available balance" hint="Pending 0.000000">0.000000</x-stat-card>
                @endforelse
                <x-stat-card label="Today's payments">{{ $today }}</x-stat-card>
                <x-stat-card label="Successful">{{ $successful }}</x-stat-card>
                <x-stat-card label="Pending / expired" :hint="'Waiting on chain or timed out'">{{ $pending }} / {{ $expired }}</x-stat-card>
            </div>

            <x-panel title="Recent payments">
                <x-slot name="action">
                    <a href="{{ route('merchant.payments') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all</a>
                </x-slot>
                @if ($recent->isEmpty())
                    <x-empty-state title="No payments yet" body="Create an invoice with POST /api/v1/payments. Each call allocates a unique address and QR payload for that order." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/60">
                                <tr>
                                    <th class="px-5 py-3">Payment</th>
                                    <th class="px-5 py-3">Order</th>
                                    <th class="px-5 py-3">Amount</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                @foreach ($recent as $payment)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40">
                                        <td class="px-5 py-3 font-medium"><a class="text-indigo-600" href="{{ route('merchant.payments.show', $payment) }}">{{ $payment->public_id }}</a></td>
                                        <td class="px-5 py-3 text-slate-600">{{ $payment->merchant_order_id }}</td>
                                        <td class="px-5 py-3"><x-money :minor="$payment->amount_minor" :asset="$payment->asset" /></td>
                                        <td class="px-5 py-3"><x-status-badge :status="$payment->status" /></td>
                                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $payment->paymentAddress?->address }}</td>
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
