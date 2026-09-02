<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Reconciliation</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('admin.nav')
        <form method="POST" action="{{ route('admin.reconciliation') }}">@csrf<button class="bg-indigo-600 text-white rounded px-3 py-1">Run now</button></form>
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-3 py-2">Run</th><th class="px-3 py-2">Matched</th><th class="px-3 py-2">Unmatched</th><th class="px-3 py-2">Exceptions</th><th class="px-3 py-2">Finished</th>
                </tr></thead>
                <tbody>
                @foreach ($runs as $run)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2"><a class="text-indigo-600" href="{{ route('admin.reconciliation.show', $run) }}">{{ $run->public_id }}</a></td>
                        <td class="px-3 py-2">{{ $run->matched_count }}</td>
                        <td class="px-3 py-2">{{ $run->unmatched_count }}</td>
                        <td class="px-3 py-2">{{ $run->exception_count }}</td>
                        <td class="px-3 py-2">{{ $run->finished_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $runs->links() }}</div>
        </div>
    </div>
</x-app-layout>
