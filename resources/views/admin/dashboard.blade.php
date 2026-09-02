<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Admin</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @include('admin.nav')
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">Merchants {{ $merchants }}</div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">Payments {{ $payments }}</div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">Credited {{ $credited }}</div>
        </div>
    </div>
</x-app-layout>
