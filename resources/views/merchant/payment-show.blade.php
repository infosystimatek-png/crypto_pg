<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $payment->public_id }}</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-2 text-sm">
            <div><strong>Order:</strong> {{ $payment->merchant_order_id }}</div>
            <div><strong>Amount:</strong> {{ $payment->expectedMoney()->toFixed() }} {{ $payment->asset->code }}</div>
            <div><strong>Received:</strong> {{ $payment->receivedMoney()->toFixed() }}</div>
            <div><strong>Network:</strong> {{ $payment->network->code }}</div>
            <div><strong>Address:</strong> <span class="font-mono">{{ $payment->paymentAddress?->address }}</span></div>
            <div><strong>QR:</strong> <span class="font-mono break-all">{{ $payment->qr_payload }}</span></div>
            <div><strong>Hash:</strong> {{ $payment->blockchainTransaction?->tx_hash }}</div>
            <div><strong>Confirmations:</strong> {{ $payment->confirmations }} / {{ $payment->required_confirmations }}</div>
            <div><strong>Status:</strong> {{ $payment->status->value }}</div>
            <div><strong>Created:</strong> {{ $payment->created_at }}</div>
            <div><strong>Confirmed:</strong> {{ $payment->confirmed_at }}</div>
        </div>
    </div>
</x-app-layout>
