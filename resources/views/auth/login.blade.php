<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 space-y-1">
        <h2 class="font-display text-xl font-semibold tracking-tight">Welcome back</h2>
        <p class="text-sm text-muted-foreground">Log in to your Cutcost shop.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-input" name="remember">
            <label for="remember_me" class="text-sm text-muted-foreground">{{ __('Remember me') }}</label>
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            @if (Route::has('password.request'))
                <a class="btn-ghost px-0" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
            <x-primary-button>{{ __('Log in') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
