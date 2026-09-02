<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Ledger</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('admin.nav')
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-3 py-2">Journal</th><th class="px-3 py-2">Merchant</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">Description</th><th class="px-3 py-2">Posted</th>
                </tr></thead>
                <tbody>
                @foreach ($entries as $entry)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2">{{ $entry->public_id }}</td>
                        <td class="px-3 py-2">{{ $entry->merchant?->name }}</td>
                        <td class="px-3 py-2">{{ $entry->type }}</td>
                        <td class="px-3 py-2">{{ $entry->description }}</td>
                        <td class="px-3 py-2">{{ $entry->posted_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $entries->links() }}</div>
        </div>
    </div>
</x-app-layout>
