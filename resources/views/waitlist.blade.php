<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Join the Cutcost waitlist — private CRM and booking for salons and barbershops.">
        <title>Join the waitlist · Cutcost</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        <style>
            .font-display,
            body { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }

            .waitlist-shell {
                background-color: hsl(var(--background));
                background-image:
                    radial-gradient(1000px 480px at 0% 0%, hsl(var(--primary) / 0.16), transparent 55%),
                    radial-gradient(800px 420px at 100% 0%, hsl(var(--primary) / 0.10), transparent 50%),
                    linear-gradient(180deg, hsl(var(--background)) 0%, hsl(var(--secondary) / 0.45) 100%);
            }

            .cc-stripe {
                background-image: repeating-linear-gradient(
                    45deg,
                    currentColor 0px, currentColor 6px,
                    transparent 6px, transparent 14px
                );
                background-size: 28px 28px;
                animation: cc-stripe-slide 2.6s linear infinite;
            }
            @keyframes cc-stripe-slide {
                from { background-position: 0 0; }
                to   { background-position: 28px 28px; }
            }

            .cc-squiggle path {
                stroke-dasharray: 220;
                stroke-dashoffset: 220;
                animation: cc-draw 0.9s ease-out 0.4s forwards;
            }
            @keyframes cc-draw { to { stroke-dashoffset: 0; } }

            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
    </head>
    <body class="font-sans text-foreground antialiased">
        <div class="waitlist-shell relative min-h-dvh overflow-x-hidden">
            <div class="pointer-events-none absolute inset-0 landing-grid-bg opacity-50"></div>
            <div class="cc-stripe pointer-events-none absolute right-0 top-0 h-[380px] w-[380px] text-primary/[0.06]" aria-hidden="true"></div>

            <header class="relative z-10 mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('home') }}" class="transition-opacity hover:opacity-85">
                    <span class="brand-logo brand-logo-gradient">Cut<span class="brand-logo-accent">cost</span></span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}#features" class="btn-ghost hidden sm:inline-flex">Features</a>
                    <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                </div>
            </header>

            <main class="relative z-10 mx-auto flex max-w-6xl flex-col gap-12 px-4 pb-16 pt-10 sm:px-6 sm:pt-16 lg:flex-row lg:items-center lg:gap-16 lg:pb-24 lg:pt-20">
                <div class="lg:w-[48%]">
                    <div class="landing-shimmer-badge inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium">
                        Early access · Waitlist open
                    </div>

                    <h1 class="font-display mt-6 text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl">
                        Your shop.
                        <span class="relative inline-block whitespace-nowrap">
                            <span class="landing-gradient-text">Your clients.</span>
                            <svg class="cc-squiggle pointer-events-none absolute -bottom-2 left-0 h-3 w-full text-primary/70" viewBox="0 0 220 12" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                <path d="M2 8.5C40 2 80 2 110 6.5C140 11 180 6 218 3" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </h1>

                    <p class="mt-6 max-w-md text-lg leading-relaxed text-muted-foreground">
                        Cutcost is the private CRM for salons and barbershops — clients, stylists, bookings, and a booking link that’s yours, not a marketplace listing.
                    </p>

                    <ul class="mt-8 space-y-3 text-sm text-muted-foreground">
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Private booking page clients can book on directly</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>CRM for clients, services, stylists, and appointments</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Online payments when you’re ready (Stripe)</span>
                        </li>
                    </ul>
                </div>

                <div class="w-full lg:w-[52%]">
                    <div class="rounded-2xl bg-card p-6 shadow-card sm:p-8">
                        @if (session('status'))
                            <div class="flash-ok mb-5">{{ session('status') }}</div>
                        @endif

                        <div class="mb-6">
                            <h2 class="font-display text-xl font-semibold tracking-tight">Join the waitlist</h2>
                            <p class="mt-1.5 text-sm text-muted-foreground">
                                Leave your details and we’ll email you when it’s your turn to set up your shop.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('waitlist.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="source" value="waitlist">

                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    placeholder="you@salon.com"
                                    class="form-input"
                                >
                                @error('email')
                                    <p class="mt-1.5 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-1.5 block text-sm font-medium">Your name <span class="font-normal text-muted-foreground">(optional)</span></label>
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        autocomplete="name"
                                        placeholder="Alex"
                                        class="form-input"
                                    >
                                    @error('name')
                                        <p class="mt-1.5 text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="shop_name" class="mb-1.5 block text-sm font-medium">Shop name <span class="font-normal text-muted-foreground">(optional)</span></label>
                                    <input
                                        id="shop_name"
                                        type="text"
                                        name="shop_name"
                                        value="{{ old('shop_name') }}"
                                        placeholder="Trish’s Beauty"
                                        class="form-input"
                                    >
                                    @error('shop_name')
                                        <p class="mt-1.5 text-xs text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn-primary landing-btn-shine h-11 w-full justify-center">
                                Request early access
                            </button>

                            <p class="text-center text-xs text-muted-foreground">
                                No spam. We’ll only email you about Cutcost access.
                            </p>
                        </form>
                    </div>

                    <p class="mt-5 text-center text-sm text-muted-foreground">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Log in</a>
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
