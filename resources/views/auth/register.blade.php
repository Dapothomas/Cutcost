<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Create your shop · Cutcost</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        <style>
            [x-cloak] { display: none !important; }
            .register-shell {
                background-color: hsl(var(--background));
                background-image:
                    radial-gradient(1000px 480px at 0% 0%, hsl(var(--primary) / 0.16), transparent 55%),
                    radial-gradient(800px 420px at 100% 0%, hsl(var(--primary) / 0.10), transparent 50%),
                    linear-gradient(180deg, hsl(var(--background)) 0%, hsl(var(--secondary) / 0.45) 100%);
            }
        </style>
    </head>
    <body class="font-sans text-foreground antialiased">
        <div
            class="register-shell min-h-dvh lg:h-dvh lg:overflow-hidden"
            x-data="{
                plan: @js(old('plan', $selectedPlan)),
                plans: @js(collect($plans)->keyBy('value')),
                get selected() { return this.plans[this.plan] || null }
            }"
        >
            <div class="mx-auto flex min-h-dvh w-full max-w-6xl flex-col lg:h-dvh lg:flex-row lg:items-stretch">
                <aside class="relative flex flex-col justify-between px-5 pb-4 pt-[max(1.25rem,env(safe-area-inset-top))] sm:px-8 lg:w-[38%] lg:px-10 lg:py-8 xl:w-[36%]">
                    <div>
                        <a href="{{ route('home') }}" class="inline-block transition-opacity hover:opacity-85">
                            <span class="brand-logo brand-logo-gradient brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></span>
                        </a>
                        <h1 class="mt-5 font-display text-3xl font-semibold tracking-tight sm:text-4xl lg:mt-8 lg:text-[2.5rem] lg:leading-[1.1]">
                            Create your shop
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Pick a plan, set up your account, then finish with secure Stripe checkout.
                        </p>

                        <div class="mt-6 hidden rounded-2xl bg-card/70 p-5 shadow-card backdrop-blur-sm lg:block">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">Selected plan</p>
                            <template x-if="selected">
                                <div class="mt-4">
                                    <div class="flex items-baseline justify-between gap-3">
                                        <p class="font-display text-xl font-semibold" x-text="selected.label"></p>
                                        <p class="font-display text-xl font-semibold text-primary">
                                            <span x-text="selected.price"></span><span class="text-sm font-normal text-muted-foreground">/mo</span>
                                        </p>
                                    </div>
                                    <p class="mt-1 text-sm text-muted-foreground" x-text="selected.description"></p>
                                    <ul class="mt-4 space-y-2 text-sm">
                                        <template x-for="feature in selected.features" :key="feature">
                                            <li class="flex items-start gap-2">
                                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span x-text="feature"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p class="mt-6 hidden text-xs text-muted-foreground lg:block">
                        Already registered?
                        <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Log in</a>
                    </p>
                </aside>

                <main class="relative flex min-h-0 flex-1 flex-col px-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] sm:px-6 lg:px-8 lg:py-6">
                    @if (session('status'))
                        <div class="flash-ok mb-3 shrink-0">{{ session('status') }}</div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('register') }}"
                        class="flex min-h-0 flex-1 flex-col rounded-2xl bg-card shadow-card lg:overflow-hidden"
                    >
                        @csrf

                        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                            <section>
                                <div class="mb-3 flex items-baseline justify-between gap-3">
                                    <h2 class="font-display text-base font-semibold">Choose your plan</h2>
                                    <span class="text-xs text-muted-foreground">Step 1</span>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-3">
                                    @foreach ($plans as $plan)
                                        <label
                                            class="relative cursor-pointer rounded-2xl bg-secondary/70 p-3.5 transition has-[:checked]:bg-primary/[0.1] has-[:checked]:ring-2 has-[:checked]:ring-primary/25"
                                        >
                                            <input
                                                type="radio"
                                                name="plan"
                                                value="{{ $plan['value'] }}"
                                                class="peer sr-only"
                                                x-model="plan"
                                                @checked(old('plan', $selectedPlan) === $plan['value'])
                                                required
                                            >
                                            <span class="block">
                                                <span class="flex items-center justify-between gap-2">
                                                    <span class="text-sm font-semibold">{{ $plan['label'] }}</span>
                                                    <span class="text-sm font-bold text-primary">{{ $plan['price'] }}</span>
                                                </span>
                                                <span class="mt-1 block text-xs leading-snug text-muted-foreground">{{ $plan['description'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('plan')" />
                            </section>

                            <section>
                                <div class="mb-3 flex items-baseline justify-between gap-3">
                                    <h2 class="font-display text-base font-semibold">Shop details</h2>
                                    <span class="text-xs text-muted-foreground">Step 2</span>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="space-y-1.5 sm:col-span-2">
                                        <x-input-label for="name" :value="__('Your name')" />
                                        <x-text-input id="name" class="block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                                        <x-input-error :messages="$errors->get('name')" />
                                    </div>
                                    <div class="space-y-1.5 sm:col-span-2">
                                        <x-input-label for="business_name" :value="__('Shop name')" />
                                        <x-text-input id="business_name" class="block w-full" type="text" name="business_name" :value="old('business_name')" required />
                                        <x-input-error :messages="$errors->get('business_name')" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <x-input-label for="city" :value="__('City')" />
                                        <x-text-input id="city" class="block w-full" type="text" name="city" :value="old('city')" autocomplete="address-level2" />
                                        <x-input-error :messages="$errors->get('city')" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <x-input-label for="phone" :value="__('Phone')" />
                                        <x-text-input id="phone" class="block w-full" type="text" name="phone" :value="old('phone')" autocomplete="tel" />
                                        <x-input-error :messages="$errors->get('phone')" />
                                    </div>
                                </div>
                            </section>

                            <section>
                                <div class="mb-3 flex items-baseline justify-between gap-3">
                                    <h2 class="font-display text-base font-semibold">Account</h2>
                                    <span class="text-xs text-muted-foreground">Step 3</span>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="space-y-1.5 sm:col-span-2">
                                        <x-input-label for="email" :value="__('Email')" />
                                        <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                        <x-input-error :messages="$errors->get('email')" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <x-input-label for="password" :value="__('Password')" />
                                        <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
                                        <x-input-error :messages="$errors->get('password')" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                                        <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                                        <x-input-error :messages="$errors->get('password_confirmation')" />
                                    </div>
                                </div>
                            </section>

                            <div class="rounded-2xl bg-secondary/70 p-4 lg:hidden" x-show="selected" x-cloak>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold" x-text="selected?.label"></p>
                                        <p class="mt-0.5 text-xs text-muted-foreground" x-text="selected?.description"></p>
                                    </div>
                                    <p class="shrink-0 font-display text-lg font-semibold text-primary">
                                        <span x-text="selected?.price"></span><span class="text-xs font-normal text-muted-foreground">/mo</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 border-0 bg-card px-4 py-3.5 sm:px-6">
                            <button type="submit" class="btn-primary w-full justify-center">
                                Continue to checkout
                            </button>
                            <p class="mt-3 text-center text-xs text-muted-foreground lg:hidden">
                                Already registered?
                                <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Log in</a>
                            </p>
                        </div>
                    </form>
                </main>
            </div>
        </div>
    </body>
</html>
