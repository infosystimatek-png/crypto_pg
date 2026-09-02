<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Ledger</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="Journal entries">
                @if ($entries->isEmpty())
                    <x-empty-state title="Journal is empty" body="Payment credits and admin adjustments append immutable entries here. Historical rows are never edited." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Journal</th>
                                    <th class="px-5 py-3">Merchant</th>
                                    <th class="px-5 py-3">Type</th>
                                    <th class="px-5 py-3">Description</th>
                                    <th class="px-5 py-3">Posted</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($entries as $entry)
                                    <tr>
                                        <td class="px-5 py-3 font-medium">{{ $entry->public_id }}</td>
                                        <td class="px-5 py-3">{{ $entry->merchant?->name ?? 'System' }}</td>
                                        <td class="px-5 py-3"><x-status-badge :status="$entry->type" /></td>
                                        <td class="px-5 py-3 text-slate-600">{{ $entry->description }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $entry->posted_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $entries->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
