<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Reconciliation</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <form method="POST" action="{{ route('admin.reconciliation') }}">
                @csrf
                <button class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Run now</button>
            </form>
            <x-panel title="Runs">
                @if ($runs->isEmpty())
                    <x-empty-state title="No runs yet" body="Compare chain transactions, payments, and ledger projections. Exceptions show up as items on each run." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Run</th>
                                    <th class="px-5 py-3">Matched</th>
                                    <th class="px-5 py-3">Unmatched</th>
                                    <th class="px-5 py-3">Exceptions</th>
                                    <th class="px-5 py-3">Finished</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($runs as $run)
                                    <tr>
                                        <td class="px-5 py-3"><a class="font-medium text-indigo-600" href="{{ route('admin.reconciliation.show', $run) }}">{{ $run->public_id }}</a></td>
                                        <td class="px-5 py-3">{{ $run->matched_count }}</td>
                                        <td class="px-5 py-3">{{ $run->unmatched_count }}</td>
                                        <td class="px-5 py-3">{{ $run->exception_count }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $run->finished_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $runs->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
