<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        <x-slot name="title">Choose a new password</x-slot>
        <x-slot name="subtitle">Use a unique password. Vaultgate never emails API secrets or wallet keys.</x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            </div>
            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-password-input id="password" name="password" required autocomplete="new-password" />
            </div>
            <div>
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-password-input id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
            </div>
            <x-button class="w-full">{{ __('Reset Password') }}</x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>
