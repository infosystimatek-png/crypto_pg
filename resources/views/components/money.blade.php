@props(['minor', 'asset', 'showCode' => true])

@php
    $formatted = (new \App\Domain\Shared\Money((string) $minor, $asset->decimals, $asset->code))->toFixed();
@endphp

<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>
    {{ $formatted }}
    @if ($showCode)
        <span class="text-slate-400">{{ $asset->code }}</span>
    @endif
</span>
