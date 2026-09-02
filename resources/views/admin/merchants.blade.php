<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600">Admin</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">Merchants</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.nav')
            <x-panel title="Provision merchant">
                <form method="POST" action="{{ route('admin.merchants.store') }}" class="grid gap-3 px-5 py-5 md:grid-cols-4">
                    @csrf
                    <input name="name" placeholder="Merchant name" class="rounded-xl border-slate-200 text-sm" required>
                    <input name="owner_email" placeholder="Owner email" class="rounded-xl border-slate-200 text-sm" required>
                    <input name="callback_url" placeholder="Callback URL" class="rounded-xl border-slate-200 text-sm">
                    <button class="rounded-xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
                </form>
            </x-panel>
            <x-panel title="Directory">
                @if ($merchants->isEmpty())
                    <x-empty-state title="No merchants" body="Create the first merchant to issue API keys and a webhook secret." />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">ID</th>
                                    <th class="px-5 py-3">Name</th>
                                    <th class="px-5 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($merchants as $merchant)
                                    <tr>
                                        <td class="px-5 py-3"><a class="font-medium text-indigo-600" href="{{ route('admin.merchants.show', $merchant) }}">{{ $merchant->public_id }}</a></td>
                                        <td class="px-5 py-3">{{ $merchant->name }}</td>
                                        <td class="px-5 py-3"><x-status-badge :status="$merchant->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3">{{ $merchants->links() }}</div>
                @endif
            </x-panel>
        </div>
    </div>
</x-app-layout>
