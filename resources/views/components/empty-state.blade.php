@props(['title', 'body' => null])

<div {{ $attributes->merge(['class' => 'px-6 py-16 text-center']) }}>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
    </div>
    <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">{{ $body ?? $slot }}</p>
</div>
