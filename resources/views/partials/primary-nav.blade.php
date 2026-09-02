@php
    $nav = [
        ['dashboard', 'Dashboard'],
        ['merchant.payments', 'Payments'],
        ['merchant.ledger', 'Ledger'],
    ];
@endphp
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    @foreach ($nav as [$route, $label])
        <x-nav-link href="{{ route($route) }}" :active="request()->routeIs($route) || request()->routeIs($route.'.*')">
            {{ __($label) }}
        </x-nav-link>
    @endforeach
    @auth
        @if (auth()->user()->isAdmin())
            <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                {{ __('Admin') }}
            </x-nav-link>
        @endif
    @endauth
</div>
