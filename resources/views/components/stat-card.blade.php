@props(['label', 'hint' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800']) }}>
    <div class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</div>
    <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ $slot }}</div>
    @if ($hint)
        <div class="mt-1 text-sm text-slate-500">{{ $hint }}</div>
    @endif
</div>
