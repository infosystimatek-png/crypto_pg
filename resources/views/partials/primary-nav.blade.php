@php
    $isAdmin = auth()->user()?->isAdmin();
    $items = [
        [
            'label' => 'Dashboard',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => 'Payments',
            'href' => route($isAdmin ? 'admin.payments' : 'merchant.payments'),
            'active' => request()->routeIs('merchant.payments')
                || request()->routeIs('merchant.payments.*')
                || request()->routeIs('admin.payments')
                || request()->routeIs('admin.payments.show'),
        ],
        [
            'label' => 'Ledger',
            'href' => route($isAdmin ? 'admin.ledger' : 'merchant.ledger'),
            'active' => request()->routeIs('merchant.ledger') || request()->routeIs('admin.ledger'),
        ],
    ];
@endphp
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    @foreach ($items as $item)
        <x-nav-link href="{{ $item['href'] }}" :active="$item['active']">
            {{ __($item['label']) }}
        </x-nav-link>
    @endforeach
    @if ($isAdmin)
        <x-nav-link
            href="{{ route('admin.dashboard') }}"
            :active="request()->routeIs('admin.*') && ! request()->routeIs('admin.payments') && ! request()->routeIs('admin.payments.show') && ! request()->routeIs('admin.ledger')"
        >
            {{ __('Admin') }}
        </x-nav-link>
    @endif
</div>
