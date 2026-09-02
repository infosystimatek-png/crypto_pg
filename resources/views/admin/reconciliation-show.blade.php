<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $run->public_id }}</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('admin.nav')
        <div class="text-sm">Matched {{ $run->matched_count }} · unmatched {{ $run->unmatched_count }} · exceptions {{ $run->exception_count }}</div>
        <div class="bg-white dark:bg-gray-800 shadow rounded overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-left"><tr>
                    <th class="px-3 py-2">Type</th><th class="px-3 py-2">Severity</th><th class="px-3 py-2">Payment</th><th class="px-3 py-2">Payload</th>
                </tr></thead>
                <tbody>
                @foreach ($run->items as $item)
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-3 py-2">{{ $item->type }}</td>
                        <td class="px-3 py-2">{{ $item->severity }}</td>
                        <td class="px-3 py-2">{{ $item->paymentRequest?->public_id }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ json_encode($item->payload) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
