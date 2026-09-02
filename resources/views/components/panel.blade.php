@props(['title' => null])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800']) }}>
            @if ($title || isset($action))
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
            @isset($action)
                <div>{{ $action }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</section>
