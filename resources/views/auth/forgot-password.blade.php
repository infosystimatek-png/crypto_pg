<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        <x-slot name="title">Reset your password</x-slot>
        <x-slot name="subtitle">Enter your email and we will send a reset link. Secrets are never stored in logs.</x-slot>

        @session('status')
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $value }}</div>
        @endsession

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>
            <x-button class="w-full">{{ __('Email Password Reset Link') }}</x-button>
            <p class="text-center text-sm text-slate-500">
                <a class="font-medium text-indigo-600" href="{{ route('login') }}">Back to log in</a>
            </p>
        </form>
    </x-authentication-card>
</x-guest-layout>
