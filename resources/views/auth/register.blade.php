<x-guest-layout>
    @if (session('status'))
        <div class="flash-ok mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="mb-2 space-y-1">
            <h2 class="font-display text-xl font-semibold tracking-tight">Create your shop</h2>
            <p class="text-sm text-muted-foreground">Choose a plan, then complete secure checkout with Stripe.</p>
        </div>

        <div class="space-y-3">
            <p class="text-sm font-medium">Choose your plan</p>
            <div class="grid gap-3">
                @foreach ($plans as $plan)
                    <label
                        class="flex cursor-pointer gap-3 rounded-xl border p-4 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/[0.04] has-[:checked]:shadow-sm has-[:checked]:shadow-primary/10"
                    >
                        <input
                            type="radio"
                            name="plan"
                            value="{{ $plan['value'] }}"
                            class="mt-1 border-input text-primary focus:ring-primary"
                            @checked(old('plan', $selectedPlan) === $plan['value'])
                            required
                        />
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="font-semibold">{{ $plan['label'] }}</span>
                                <span class="text-sm font-bold text-primary">{{ $plan['price'] }}<span class="font-normal text-muted-foreground">/mo</span></span>
                            </span>
                            <span class="mt-1 block text-xs text-muted-foreground">{{ $plan['description'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('plan')" />
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
            <x-primary-button>Continue to checkout</x-primary-button>
        </div>
    </form>
</x-guest-layout>
