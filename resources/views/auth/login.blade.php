<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        <x-slot name="title">Welcome back</x-slot>
        <x-slot name="subtitle">Sign in to the merchant console or the admin operations desk.</x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            </div>

            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <x-button class="w-full">
                {{ __('Log in') }}
            </x-button>

            <p class="text-center text-sm text-slate-500">
                New to Vaultgate?
                <a class="font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('register') }}">Create an account</a>
            </p>
        </form>
    </x-authentication-card>
</x-guest-layout>
