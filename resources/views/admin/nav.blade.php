@php
    $links = [
        ['admin.dashboard', 'Overview'],
        ['admin.merchants', 'Merchants'],
        ['admin.payments', 'Payments'],
        ['admin.transactions', 'Chain txs'],
        ['admin.ledger', 'Ledger'],
        ['admin.webhooks', 'Webhooks'],
        ['admin.reconciliation', 'Reconciliation'],
    ];
@endphp
<div class="flex flex-wrap gap-2">
    @foreach ($links as [$route, $label])
        <a
            href="{{ route($route) }}"
            @class([
                'rounded-full px-3 py-1.5 text-sm font-medium transition',
                'bg-indigo-600 text-white shadow-sm' => request()->routeIs($route) || request()->routeIs($route.'.*') || request()->routeIs($route.'.show'),
                'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' => ! (request()->routeIs($route) || request()->routeIs($route.'.*') || request()->routeIs($route.'.show')),
            ])
        >{{ $label }}</a>
    @endforeach
</div>
@if (session('status'))
    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
@endif
