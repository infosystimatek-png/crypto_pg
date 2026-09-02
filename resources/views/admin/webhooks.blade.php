<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Webhooks</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="Deliveries">
                @if ($deliveries->isEmpty())
                    <x-empty-state title="No callbacks queued" body="Confirmed payments enqueue signed deliveries with retries and a dead-letter path." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Event</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Attempts</th>
                                    <th class="px-5 py-3">HTTP</th>
                                    <th class="px-5 py-3">Error</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($deliveries as $delivery)
                                    <tr>
                                        <td class="px-5 py-3">{{ $delivery->event->type }} <span class="font-mono text-xs text-slate-400">{{ $delivery->event->public_id }}</span></td>
                                        <td class="px-5 py-3"><x-status-badge :status="$delivery->status" /></td>
                                        <td class="px-5 py-3">{{ $delivery->attempts }}</td>
                                        <td class="px-5 py-3">{{ $delivery->last_response_code ?? '—' }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $delivery->last_error }}</td>
                                        <td class="px-5 py-3">
                                            <form method="POST" action="{{ route('admin.webhooks.retry', $delivery) }}">@csrf<button class="text-sm font-medium text-indigo-600">Retry</button></form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $deliveries->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
