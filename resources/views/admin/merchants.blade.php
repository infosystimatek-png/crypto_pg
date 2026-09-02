<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Merchants</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @include('admin.nav')
        <form method="POST" action="{{ route('admin.merchants.store') }}" class="bg-white dark:bg-gray-800 p-4 rounded shadow grid md:grid-cols-4 gap-3">
            @csrf
            <input name="name" placeholder="Merchant name" class="border rounded px-2 py-1 dark:bg-gray-900" required>
            <input name="owner_email" placeholder="Owner email" class="border rounded px-2 py-1 dark:bg-gray-900" required>
            <input name="callback_url" placeholder="Callback URL" class="border rounded px-2 py-1 dark:bg-gray-900">
            <button class="bg-indigo-600 text-white rounded px-3">Create</button>
        </form>
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr><th class="px-4 py-2">ID</th><th class="px-4 py-2">Name</th><th class="px-4 py-2">Status</th></tr></thead>
                <tbody>
                @foreach ($merchants as $merchant)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-4 py-2"><a class="text-indigo-600" href="{{ route('admin.merchants.show', $merchant) }}">{{ $merchant->public_id }}</a></td>
                        <td class="px-4 py-2">{{ $merchant->name }}</td>
                        <td class="px-4 py-2">{{ $merchant->status }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $merchants->links() }}</div>
        </div>
    </div>
</x-app-layout>
