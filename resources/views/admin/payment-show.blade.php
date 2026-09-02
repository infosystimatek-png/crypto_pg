<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Payment</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $payment->public_id }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="Settlement record">
                <dl class="grid grid-cols-1 gap-4 px-5 py-5 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Merchant</dt><dd class="mt-1 font-medium">{{ $payment->merchant->name }}</dd></div>
                    <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><x-status-badge :status="$payment->status" /></dd></div>
                    <div><dt class="text-slate-500">Order</dt><dd class="mt-1">{{ $payment->merchant_order_id }}</dd></div>
                    <div><dt class="text-slate-500">Amount</dt><dd class="mt-1"><x-money :minor="$payment->amount_minor" :asset="$payment->asset" /></dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Address</dt><dd class="mt-1 font-mono text-xs">{{ $payment->paymentAddress?->address }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">Hash</dt><dd class="mt-1 font-mono text-xs">{{ $payment->blockchainTransaction?->tx_hash ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">Confirmations</dt><dd class="mt-1">{{ $payment->confirmations }} / {{ $payment->required_confirmations }}</dd></div>
                    <div><dt class="text-slate-500">Network</dt><dd class="mt-1">{{ $payment->network->code }}</dd></div>
                </dl>
            </x-panel>
        </div>
    </div>
</x-app-layout>
