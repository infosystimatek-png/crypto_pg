<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $payment->public_id }}</h2></x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3 text-sm">
        @include('admin.nav')
        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow space-y-1">
            <div>Merchant {{ $payment->merchant->name }}</div>
            <div>Order {{ $payment->merchant_order_id }}</div>
            <div>Amount {{ $payment->expectedMoney()->toFixed() }} {{ $payment->asset->code }} on {{ $payment->network->code }}</div>
            <div>Address {{ $payment->paymentAddress?->address }}</div>
            <div>Status {{ $payment->status->value }} · conf {{ $payment->confirmations }}/{{ $payment->required_confirmations }}</div>
            <div>Hash {{ $payment->blockchainTransaction?->tx_hash }}</div>
        </div>
    </div>
</x-app-layout>
