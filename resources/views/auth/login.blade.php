<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 space-y-1">
        <h2 class="font-display text-lg font-semibold tracking-[-0.02em]">Welcome back</h2>
        <p class="text-[13px] text-muted-foreground">Log in to your Cutcost shop.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div class="space-y-1.5">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-1.5">
            <div class="flex items-baseline justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-[12px] font-medium text-primary hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label class="flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
            <span class="text-[13px] text-muted-foreground">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-full justify-center">{{ __('Log in') }}</x-primary-button>
    </form>

    <p class="mt-5 border-t border-border pt-5 text-center text-[13px] text-muted-foreground">
        New shop?
        <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">Create an account</a>
        <span class="px-1 text-border">·</span>
        <a href="{{ route('waitlist') }}" class="font-medium text-primary hover:underline">Join the waitlist</a>
    </p>
</x-guest-layout>
