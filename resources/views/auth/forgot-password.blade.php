<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h2 class="font-display text-xl font-semibold tracking-tight">Forgot password</h2>
        <p class="text-sm text-muted-foreground">
            {{ __('No problem. Enter your email and we’ll send a reset link.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a class="btn-ghost px-0" href="{{ route('login') }}">Back to login</a>
            <x-primary-button>{{ __('Email reset link') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
