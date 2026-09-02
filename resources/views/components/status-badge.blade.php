@props([
    'status' => '',
])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $tone = match ($value) {
        'CREDITED', 'CONFIRMED', 'delivered', 'completed', 'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'WAITING_FOR_PAYMENT', 'CREATED', 'pending', 'CONFIRMING', 'TRANSACTION_DETECTED' => 'bg-amber-50 text-amber-800 ring-amber-100',
        'EXPIRED', 'CANCELLED' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'UNDERPAID', 'OVERPAID', 'failed', 'dead_letter' => 'bg-orange-50 text-orange-800 ring-orange-100',
        'WRONG_ASSET', 'WRONG_NETWORK', 'FAILED' => 'bg-rose-50 text-rose-700 ring-rose-100',
        default => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$tone]) }}>
    {{ $value !== '' ? $value : $slot }}
</span>
