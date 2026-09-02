<div class="flex flex-wrap gap-3 text-sm mb-4">
    <a class="text-indigo-600" href="{{ route('admin.dashboard') }}">Overview</a>
    <a class="text-indigo-600" href="{{ route('admin.merchants') }}">Merchants</a>
    <a class="text-indigo-600" href="{{ route('admin.payments') }}">Payments</a>
    <a class="text-indigo-600" href="{{ route('admin.transactions') }}">Chain txs</a>
    <a class="text-indigo-600" href="{{ route('admin.ledger') }}">Ledger</a>
    <a class="text-indigo-600" href="{{ route('admin.webhooks') }}">Webhooks</a>
    <a class="text-indigo-600" href="{{ route('admin.reconciliation') }}">Reconciliation</a>
</div>
@if (session('status'))
    <div class="mb-4 p-3 bg-green-50 text-green-800 text-sm rounded">{{ session('status') }}</div>
@endif
