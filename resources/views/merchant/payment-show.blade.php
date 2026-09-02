<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Payment</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $payment->public_id }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
            <div class="lg:col-span-3 space-y-4">
                <x-panel title="Invoice">
                    <dl class="grid grid-cols-1 gap-4 px-5 py-5 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Order</dt><dd class="mt-1 font-medium">{{ $payment->merchant_order_id }}</dd></div>
                        <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><x-status-badge :status="$payment->status" /></dd></div>
                        <div><dt class="text-slate-500">Amount</dt><dd class="mt-1"><x-money :minor="$payment->amount_minor" :asset="$payment->asset" /></dd></div>
                        <div><dt class="text-slate-500">Received</dt><dd class="mt-1"><x-money :minor="$payment->received_amount_minor" :asset="$payment->asset" /></dd></div>
                        <div><dt class="text-slate-500">Network</dt><dd class="mt-1">{{ $payment->network->code }}</dd></div>
                        <div><dt class="text-slate-500">Confirmations</dt><dd class="mt-1">{{ $payment->confirmations }} / {{ $payment->required_confirmations }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">Address</dt><dd class="mt-1 break-all font-mono text-xs">{{ $payment->paymentAddress?->address }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">Transaction hash</dt><dd class="mt-1 break-all font-mono text-xs">{{ $payment->blockchainTransaction?->tx_hash ?? 'Awaiting chain activity' }}</dd></div>
                        <div><dt class="text-slate-500">Created</dt><dd class="mt-1">{{ $payment->created_at }}</dd></div>
                        <div><dt class="text-slate-500">Confirmed</dt><dd class="mt-1">{{ $payment->confirmed_at ?? '—' }}</dd></div>
                    </dl>
                </x-panel>
            </div>
            <div class="lg:col-span-2">
                <x-panel title="Checkout QR">
                    <div class="flex flex-col items-center px-5 py-6">
                        @if ($payment->qr_payload)
                            <x-qr-code :data="$payment->qr_payload" :size="200" />
                            <p class="mt-4 break-all text-center font-mono text-[11px] leading-relaxed text-slate-500">{{ $payment->qr_payload }}</p>
                        @else
                            <p class="text-sm text-slate-500">QR payload is not available for this payment.</p>
                        @endif
                    </div>
                </x-panel>
            </div>
        </div>
    </div>
</x-app-layout>
