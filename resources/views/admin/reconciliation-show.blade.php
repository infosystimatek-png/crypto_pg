<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Run</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $run->public_id }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <div class="grid gap-4 md:grid-cols-3">
                <x-stat-card label="Matched">{{ $run->matched_count }}</x-stat-card>
                <x-stat-card label="Unmatched">{{ $run->unmatched_count }}</x-stat-card>
                <x-stat-card label="Exceptions">{{ $run->exception_count }}</x-stat-card>
            </div>
            <x-panel title="Items">
                @if ($run->items->isEmpty())
                    <x-empty-state title="Clean run" body="No exceptions were recorded for this pass." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Type</th>
                                    <th class="px-5 py-3">Severity</th>
                                    <th class="px-5 py-3">Payment</th>
                                    <th class="px-5 py-3">Payload</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($run->items as $item)
                                    <tr>
                                        <td class="px-5 py-3">{{ $item->type }}</td>
                                        <td class="px-5 py-3"><x-status-badge :status="$item->severity" /></td>
                                        <td class="px-5 py-3">{{ $item->paymentRequest?->public_id }}</td>
                                        <td class="px-5 py-3 font-mono text-xs">{{ json_encode($item->payload) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
