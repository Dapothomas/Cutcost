<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="mb-2 space-y-1">
            <h2 class="text-xl font-semibold tracking-tight">Create your shop</h2>
            <p class="text-sm text-muted-foreground">Register as a salon or barbershop owner.</p>
        </div>

        <div class="space-y-2">
            <x-input-label for="name" :value="__('Your name')" />
            <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="business_name" :value="__('Shop name')" />
            <x-text-input id="business_name" class="block w-full" type="text" name="business_name" :value="old('business_name')" required />
            <x-input-error :messages="$errors->get('business_name')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="city" :value="__('City')" />
            <x-text-input id="city" class="block w-full" type="text" name="city" :value="old('city')" autocomplete="address-level2" />
            <x-input-error :messages="$errors->get('city')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" class="block w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a class="btn-ghost px-0" href="{{ route('login') }}">Already registered?</a>
            <x-primary-button>Create shop</x-primary-button>
        </div>
    </form>
</x-guest-layout>
