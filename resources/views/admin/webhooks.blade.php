<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Webhooks</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @include('admin.nav')
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-3 py-2">Event</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Attempts</th><th class="px-3 py-2">HTTP</th><th class="px-3 py-2">Error</th><th class="px-3 py-2"></th>
                </tr></thead>
                <tbody>
                @foreach ($deliveries as $delivery)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2">{{ $delivery->event->type }} {{ $delivery->event->public_id }}</td>
                        <td class="px-3 py-2">{{ $delivery->status }}</td>
                        <td class="px-3 py-2">{{ $delivery->attempts }}</td>
                        <td class="px-3 py-2">{{ $delivery->last_response_code }}</td>
                        <td class="px-3 py-2">{{ $delivery->last_error }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('admin.webhooks.retry', $delivery) }}">@csrf<button class="text-indigo-600">Retry</button></form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $deliveries->links() }}</div>
        </div>
    </div>
</x-app-layout>
