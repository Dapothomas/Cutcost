<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h2 class="font-display text-xl font-semibold tracking-tight">Confirm password</h2>
        <p class="text-sm text-muted-foreground">
            {{ __('This is a secure area. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex justify-end pt-2">
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
