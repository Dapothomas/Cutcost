<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Cutcost is the private CRM for salons and barbershops — manage clients, staff, and bookings in one place, with a booking link that's yours, not a marketplace listing.">
        <title>Cutcost — Salon &amp; barber CRM</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        <style>
            /* ---- Cutcost landing page enhancements (scoped, additive) ---- */
            .font-display { font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif; }

            html { scroll-behavior: smooth; }

            /* Diagonal "barber pole" stripe motif — the page's one signature flourish.
               Uses currentColor so it always matches whatever text color / theme it's given. */
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
                to   { background-position: 28px 0; }
            }

            .cc-float { animation: cc-float 5.5s ease-in-out infinite; }
            @keyframes cc-float {
                0%, 100% { transform: translateY(0) rotate(var(--cc-rot, 0deg)); }
                50%      { transform: translateY(-10px) rotate(var(--cc-rot, 0deg)); }
            }

            .cc-pulse { position: relative; }
            .cc-pulse::after {
                content: '';
                position: absolute;
                inset: -5px;
                border-radius: 9999px;
                border: 1.5px solid currentColor;
                opacity: 0.55;
                animation: cc-pulse-ring 1.8s cubic-bezier(0,0,0.2,1) infinite;
            }
            @keyframes cc-pulse-ring {
                0%   { transform: scale(0.7); opacity: 0.55; }
                100% { transform: scale(1.9); opacity: 0; }
            }

            .cc-bar {
                transform: scaleY(0);
                transform-origin: bottom;
                animation: cc-grow 0.7s cubic-bezier(0.22,1,0.36,1) forwards;
            }
            @keyframes cc-grow { to { transform: scaleY(1); } }

            .cc-squiggle path {
                stroke-dasharray: 220;
                stroke-dashoffset: 220;
                animation: cc-draw 0.9s ease-out 1.15s forwards;
            }
            @keyframes cc-draw { to { stroke-dashoffset: 0; } }

            .cc-line {
                transform: scaleX(0);
                transform-origin: left;
                transition: transform 0.9s cubic-bezier(0.22,1,0.36,1);
            }
            .cc-line.is-revealed { transform: scaleX(1); }

            .cc-mobile-panel {
                max-height: 0;
                opacity: 0;
                overflow: hidden;
                transition: max-height 0.35s ease, opacity 0.25s ease;
            }
            .cc-mobile-panel.is-open { max-height: 24rem; opacity: 1; }

            .cc-burger span {
                display: block;
                transition: transform 0.3s ease, opacity 0.2s ease;
                transform-origin: center;
            }
            .cc-burger.is-open span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
            .cc-burger.is-open span:nth-child(2) { opacity: 0; }
            .cc-burger.is-open span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

            .cc-tilt { transition: transform 0.5s cubic-bezier(0.22,1,0.36,1); }
            .cc-tilt:hover { transform: perspective(1000px) rotateX(1.5deg) rotateY(-2deg) translateY(-4px); }

            a:focus-visible, button:focus-visible, [tabindex]:focus-visible {
                outline: 2px solid currentColor;
                outline-offset: 3px;
                border-radius: 8px;
            }

            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
                html { scroll-behavior: auto; }
            }
        </style>
    </head>
    <body class="font-sans">
        <div class="relative min-h-screen overflow-x-hidden bg-background">
            {{-- Background --}}
            <div class="pointer-events-none absolute inset-0 landing-grid-bg opacity-60"></div>
            <div class="landing-orb pointer-events-none absolute -left-32 top-20 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="landing-orb landing-orb-delay pointer-events-none absolute -right-24 top-1/3 h-80 w-80 rounded-full bg-brand-200/40 blur-3xl"></div>
            <div class="cc-stripe pointer-events-none absolute right-0 top-0 h-[420px] w-[420px] text-primary/[0.06]" aria-hidden="true"></div>

            {{-- Header --}}
            <header
                id="landing-header"
                class="sticky top-0 z-50 border-b border-transparent bg-background/80 backdrop-blur-md transition-all duration-300"
            >
                <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <a href="{{ route('home') }}" class="landing-hero-in landing-hero-in-1 transition-opacity hover:opacity-85">
                        <span class="brand-logo brand-logo-gradient">Cut<span class="brand-logo-accent">cost</span></span>
                    </a>

                    <nav class="landing-hero-in landing-hero-in-2 flex items-center gap-1 sm:gap-2">
                        <a href="#features" class="btn-ghost hidden sm:inline-flex">Features</a>
                        <a href="#pricing" class="btn-ghost hidden sm:inline-flex">Pricing</a>
                        <a href="#how-it-works" class="btn-ghost hidden sm:inline-flex">How it works</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary landing-btn-shine">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn-ghost hidden sm:inline-flex">Log in</a>
                            <a href="{{ route('register') }}" class="btn-primary landing-btn-shine">Start free</a>
                        @endauth

                        <button
                            type="button"
                            id="cc-menu-btn"
                            class="cc-burger flex h-9 w-9 flex-col items-center justify-center gap-[5px] rounded-md sm:hidden"
                            aria-label="Toggle menu"
                            aria-controls="cc-mobile-panel"
                            aria-expanded="false"
                        >
                            <span class="h-[1.5px] w-5 rounded-full bg-foreground"></span>
                            <span class="h-[1.5px] w-5 rounded-full bg-foreground"></span>
                            <span class="h-[1.5px] w-5 rounded-full bg-foreground"></span>
                        </button>
                    </nav>
                </div>

                <div id="cc-mobile-panel" class="cc-mobile-panel border-b bg-background/95 backdrop-blur-md sm:hidden">
                    <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3">
                        <a href="#features" class="btn-ghost justify-start">Features</a>
                        <a href="#pricing" class="btn-ghost justify-start">Pricing</a>
                        <a href="#how-it-works" class="btn-ghost justify-start">How it works</a>
                        @guest
                            <a href="{{ route('login') }}" class="btn-ghost justify-start">Log in</a>
                        @endguest
                    </div>
                </div>
            </header>

            {{-- Hero --}}
            <section class="relative mx-auto max-w-6xl px-4 pb-20 pt-16 sm:px-6 sm:pt-24 lg:pb-28">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <div>
                        <div class="landing-hero-in landing-hero-in-2 landing-shimmer-badge inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium">
                            Beauty &amp; grooming CRM
                        </div>

                        <h1 class="landing-hero-in landing-hero-in-3 font-display mt-6 text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                            Run your shop.
                            <span class="relative inline-block whitespace-nowrap">
                                <span class="landing-gradient-text">Fill your chair.</span>
                                <svg class="cc-squiggle pointer-events-none absolute -bottom-2 left-0 h-3 w-full text-primary/70" viewBox="0 0 220 12" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M2 8.5C40 2 80 2 110 6.5C140 11 180 6 218 3" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            </span>
                        </h1>

                        <p class="landing-hero-in landing-hero-in-4 mt-6 max-w-lg text-lg leading-relaxed text-muted-foreground">
                            Clients, staff, services, and appointments in one place — plus a private booking link so regulars can book themselves.
                        </p>

                        <div class="landing-hero-in landing-hero-in-5 mt-8 flex flex-wrap items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-primary landing-btn-shine h-11 px-6">Open dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="btn-primary landing-btn-shine h-11 px-6">Create your shop</a>
                                <a href="{{ route('login') }}" class="btn-secondary h-11 px-6 transition-all duration-300 hover:-translate-y-0.5">Log in</a>
                            @endauth
                        </div>

                        <div class="landing-hero-in landing-hero-in-5 mt-10 flex flex-wrap gap-6 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                From £10/month
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                Client booking link
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                Owner + barber roles
                            </div>
                        </div>
                    </div>

                    {{-- Dashboard preview mock --}}
                    <div class="landing-preview relative mx-auto w-full max-w-md lg:max-w-none">
                        <div class="absolute -inset-4 rounded-2xl bg-gradient-to-br from-muted/80 via-background to-secondary/50 blur-2xl"></div>

                        <div class="cc-tilt relative overflow-hidden rounded-xl border bg-card shadow-2xl">
                            <div class="flex items-center gap-2 border-b bg-muted/40 px-4 py-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-400/80"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400/80"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400/80"></span>
                                <span class="ml-2 text-xs text-muted-foreground">Cutcost · Dashboard</span>
                            </div>

                            <div class="grid grid-cols-3 gap-2 border-b p-3">
                                <div class="rounded-md border bg-background p-2.5 transition-colors duration-500">
                                    <p class="text-[10px] text-muted-foreground">Clients</p>
                                    <p class="font-display text-lg font-bold">132</p>
                                </div>
                                <div class="rounded-md border bg-background p-2.5">
                                    <p class="flex items-center gap-1 text-[10px] text-muted-foreground">
                                        Today
                                        <span class="cc-pulse h-1.5 w-1.5 rounded-full bg-primary text-primary"></span>
                                    </p>
                                    <p class="font-display text-lg font-bold">9</p>
                                </div>
                                <div class="rounded-md border bg-background p-2.5">
                                    <p class="text-[10px] text-muted-foreground">Staff</p>
                                    <p class="font-display text-lg font-bold">5</p>
                                </div>
                            </div>

                            {{-- mini activity chart --}}
                            <div class="flex items-end gap-1.5 border-b px-3 py-3" style="animation: landing-fade-up 0.6s cubic-bezier(0.22,1,0.36,1) 1s both">
                                @foreach ([40, 65, 50, 80, 60, 95, 70] as $i => $h)
                                    <div class="cc-bar flex-1 rounded-sm bg-primary/20" style="height: {{ $h }}%; animation-delay: {{ 1.1 + ($i * 0.06) }}s"></div>
                                @endforeach
                            </div>

                            <div class="space-y-2 p-3">
                                <p class="text-xs font-medium text-muted-foreground">Today’s appointments</p>
                                @foreach ([['11:00', 'Jordan L.', 'Skin fade'], ['12:30', 'Sam R.', 'Beard trim'], ['14:00', 'Alex M.', 'Cut & shape']] as $i => $apt)
                                    <div class="flex items-center gap-3 rounded-md border bg-background p-2.5 transition-all duration-300 hover:border-foreground/20 hover:-translate-y-0.5" style="animation: landing-fade-up 0.6s cubic-bezier(0.22,1,0.36,1) {{ 0.8 + ($i * 0.12) }}s both">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary text-[10px] font-bold text-primary-foreground">{{ $apt[0] }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-medium">{{ $apt[1] }}</p>
                                            <p class="truncate text-[10px] text-muted-foreground">{{ $apt[2] }}</p>
                                        </div>
                                        <span class="badge-outline shrink-0 scale-90">Scheduled</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- floating notification card --}}
                        <div
                            class="cc-float absolute -bottom-6 -left-6 hidden items-center gap-2.5 rounded-lg border bg-card px-3 py-2.5 shadow-xl sm:flex"
                            style="--cc-rot: -3deg; animation: landing-fade-up 0.6s cubic-bezier(0.22,1,0.36,1) 1.5s both, cc-float 5.5s ease-in-out 2.1s infinite"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <div class="leading-tight">
                                <p class="text-xs font-medium">Booking confirmed</p>
                                <p class="text-[10px] text-muted-foreground">Client booked themselves</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Not a marketplace --}}
            <section id="compare" class="border-t py-20 sm:py-24">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="landing-reveal mx-auto max-w-2xl text-center" data-reveal>
                        <span class="badge-default">Why not a marketplace</span>
                        <h2 class="font-display mt-4 text-3xl font-bold tracking-tight sm:text-4xl">You keep the relationship with your clients</h2>
                        <p class="mt-3 text-muted-foreground">Marketplace booking apps put your shop next to five competitors and take a cut of every visit. Cutcost is built the other way around.</p>
                    </div>

                    @php
                        $comparisonPoints = [
                            ['Public listing pushes clients toward nearby competitors', 'A private link — only clients you share it with can book'],
                            ['Takes a percentage of every booking you take', 'Flat, predictable pricing — the booking is yours'],
                            ["A ranking algorithm decides how often you're seen", 'You control your own schedule, no algorithm involved'],
                            ["Client history lives inside the marketplace's app", 'Every note and visit stays in your own CRM'],
                        ];
                    @endphp

                    <div class="mx-auto mt-12 grid max-w-3xl gap-4 sm:grid-cols-2">
                        <div class="landing-reveal landing-reveal-delay-1 card p-6" data-reveal>
                            <p class="text-sm font-semibold text-muted-foreground">Marketplace apps</p>
                            <ul class="mt-4 space-y-4">
                                @foreach ($comparisonPoints as $point)
                                    <li class="flex items-start gap-2.5 text-sm text-muted-foreground">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>
                                        <span>{{ $point[0] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="landing-reveal landing-reveal-delay-2 landing-card-hover card border-primary/30 bg-primary/[0.03] p-6 shadow-sm" data-reveal>
                            <p class="text-sm font-semibold text-primary">Cutcost</p>
                            <ul class="mt-4 space-y-4">
                                @foreach ($comparisonPoints as $point)
                                    <li class="flex items-start gap-2.5 text-sm">
                                        <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                        </span>
                                        <span>{{ $point[1] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section id="features" class="border-t bg-muted/30 py-20 sm:py-24">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="landing-reveal mx-auto max-w-2xl text-center" data-reveal>
                        <span class="badge-default">Features</span>
                        <h2 class="font-display mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Everything your shop needs</h2>
                        <p class="mt-3 text-muted-foreground">Built for salons and barbershops — not a public directory.</p>
                    </div>

                    <div class="mx-auto mt-14 grid max-w-5xl gap-4 sm:grid-cols-3">
                        <div class="landing-reveal landing-reveal-delay-1 landing-card-hover card p-6 transition-colors duration-300 hover:border-primary/30" data-reveal>
                            <div class="stat-card-icon mb-4 transition-transform duration-300 group-hover:scale-110">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            </div>
                            <h3 class="font-display font-semibold">Clients</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Contact details, notes, and visit history for every regular in your CRM.</p>
                        </div>
                        <div class="landing-reveal landing-reveal-delay-2 landing-card-hover card p-6 transition-colors duration-300 hover:border-primary/30" data-reveal>
                            <div class="stat-card-icon mb-4">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/></svg>
                            </div>
                            <h3 class="font-display font-semibold">Team</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Add barbers, assign chairs, and keep the floor running smoothly.</p>
                        </div>
                        <div class="landing-reveal landing-reveal-delay-3 landing-card-hover card p-6 transition-colors duration-300 hover:border-primary/30" data-reveal>
                            <div class="stat-card-icon mb-4">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </div>
                            <h3 class="font-display font-semibold">Bookings</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">Schedule in the shop or share a link so clients book themselves.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section id="how-it-works" class="py-20 sm:py-24">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="landing-reveal mx-auto max-w-2xl text-center" data-reveal>
                        <span class="badge-default">How it works</span>
                        <h2 class="font-display mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Up and running in minutes</h2>
                    </div>

                    <div class="mx-auto mt-14 grid max-w-4xl gap-8 sm:grid-cols-3">
                        @foreach ([
                            ['1', 'Create your shop', 'Register as an owner, add your services and team.'],
                            ['2', 'Share your link', 'Send clients your private booking page — not a marketplace listing.'],
                            ['3', 'Run the day', 'See today’s book, update statuses, and grow your CRM.'],
                        ] as $i => [$step, $title, $desc])
                            <div class="landing-reveal landing-reveal-delay-{{ $i + 1 }} relative text-center" data-reveal>
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border-2 border-primary bg-background font-display text-lg font-bold transition-transform duration-300 hover:scale-110">
                                    {{ $step }}
                                </div>
                                @if ($i < 2)
                                    <div class="cc-line absolute left-[calc(50%+2rem)] top-6 hidden h-px w-[calc(100%-4rem)] bg-gradient-to-r from-primary/60 to-transparent sm:block" data-reveal></div>
                                @endif
                                <h3 class="mt-4 font-semibold">{{ $title }}</h3>
                                <p class="mt-2 text-sm text-muted-foreground">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Pricing --}}
            <section id="pricing" class="border-t bg-muted/30 py-20 sm:py-24">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <div class="landing-reveal mx-auto max-w-2xl text-center" data-reveal>
                        <span class="badge-default">Pricing</span>
                        <h2 class="font-display mt-4 text-3xl font-bold tracking-tight sm:text-4xl">Simple plans for every shop</h2>
                        <p class="mt-3 text-muted-foreground">From £10/month for solo barbers. No marketplace fees — ever.</p>
                    </div>

                    @php
                        $plans = [
                            [
                                'name' => 'Starter',
                                'slug' => 'starter',
                                'price' => '£10',
                                'period' => 'per month',
                                'description' => 'For solo barbers getting organised.',
                                'featured' => false,
                                'features' => [
                                    '1 barber seat',
                                    'Client CRM & notes',
                                    'Services & pricing',
                                    'Manual bookings',
                                    'Private booking link',
                                ],
                                'cta' => 'Get started',
                            ],
                            [
                                'name' => 'Shop',
                                'slug' => 'shop',
                                'price' => '£25',
                                'period' => 'per month',
                                'description' => 'Everything a busy shop floor needs.',
                                'featured' => true,
                                'features' => [
                                    'Up to 5 barbers',
                                    'Full team scheduling',
                                    'Client self-booking',
                                    'Today’s dashboard',
                                    'Email support',
                                ],
                                'cta' => 'Start 14-day trial',
                            ],
                            [
                                'name' => 'Studio',
                                'slug' => 'studio',
                                'price' => '£59',
                                'period' => 'per month',
                                'description' => 'For multi-chair salons and growing teams.',
                                'featured' => false,
                                'features' => [
                                    'Unlimited barbers',
                                    'Multiple services & tiers',
                                    'Priority support',
                                    'Advanced booking rules',
                                    'Dedicated onboarding',
                                ],
                                'cta' => 'Talk to us',
                            ],
                        ];
                    @endphp

                    <div class="mx-auto mt-14 grid max-w-5xl gap-6 lg:grid-cols-3 lg:items-center">
                        @foreach ($plans as $i => $plan)
                            <div
                                class="landing-reveal landing-reveal-delay-{{ $i + 1 }} landing-card-hover relative flex flex-col rounded-xl border bg-card p-6 shadow-sm {{ $plan['featured'] ? 'border-primary/40 bg-gradient-to-b from-primary/[0.06] to-card shadow-lg shadow-primary/10 lg:scale-[1.03] lg:p-7' : 'border-border/80' }}"
                                data-reveal
                            >
                                @if ($plan['featured'])
                                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground shadow-md shadow-primary/25">
                                        Most popular
                                    </span>
                                @endif

                                <div class="mb-6">
                                    <h3 class="font-display text-lg font-semibold">{{ $plan['name'] }}</h3>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ $plan['description'] }}</p>
                                    <div class="mt-5 flex items-end gap-1">
                                        <span class="font-display text-4xl font-bold tracking-tight">{{ $plan['price'] }}</span>
                                        <span class="mb-1 text-sm text-muted-foreground">/ {{ $plan['period'] }}</span>
                                    </div>
                                </div>

                                <ul class="mb-8 flex-1 space-y-3">
                                    @foreach ($plan['features'] as $feature)
                                        <li class="flex items-start gap-2.5 text-sm">
                                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                                            </span>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @auth
                                    <a href="{{ route('dashboard') }}" class="{{ $plan['featured'] ? 'btn-primary landing-btn-shine' : 'btn-secondary' }} w-full justify-center">
                                        Go to dashboard
                                    </a>
                                @else
                                    <a href="{{ route('register', ['plan' => $plan['slug']]) }}" class="{{ $plan['featured'] ? 'btn-primary landing-btn-shine' : 'btn-secondary' }} w-full justify-center">
                                        {{ $plan['cta'] }}
                                    </a>
                                @endauth
                            </div>
                        @endforeach
                    </div>

                    <p class="landing-reveal landing-reveal-delay-3 mx-auto mt-10 max-w-xl text-center text-sm text-muted-foreground" data-reveal>
                        All plans include your private booking link — not a public marketplace listing. Cancel anytime.
                    </p>
                </div>
            </section>

            {{-- CTA --}}
            <section class="relative overflow-hidden border-t bg-muted/30 py-20">
                <div class="landing-orb pointer-events-none absolute left-1/2 top-1/2 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-br from-primary/10 via-secondary/30 to-transparent blur-3xl"></div>
                <div class="landing-reveal relative mx-auto max-w-3xl px-4 text-center sm:px-6" data-reveal>
                    <h2 class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Ready to run a tighter shop?</h2>
                    <p class="mt-4 text-lg text-muted-foreground">Join Cutcost and give your team one place to work — with clients booking on your link.</p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary landing-btn-shine h-11 px-8">Go to dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="btn-primary landing-btn-shine h-11 px-8">Create your shop — free</a>
                            <a href="{{ route('login') }}" class="btn-secondary h-11 px-8 transition-all duration-300 hover:-translate-y-0.5">Log in</a>
                        @endauth
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="border-t py-8">
                <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6">
                    <a href="{{ route('home') }}" class="transition-opacity hover:opacity-85">
                        <span class="brand-logo brand-logo-gradient brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></span>
                    </a>
                    <nav class="flex items-center gap-5 text-sm text-muted-foreground">
                        <a href="#features" class="transition-colors hover:text-foreground">Features</a>
                        <a href="#pricing" class="transition-colors hover:text-foreground">Pricing</a>
                        <a href="#how-it-works" class="transition-colors hover:text-foreground">How it works</a>
                    </nav>
                    <p class="text-sm text-muted-foreground">Salon &amp; barber CRM · No marketplace</p>
                </div>
            </footer>
        </div>

        <script>
            (() => {
                const header = document.getElementById('landing-header');
                const onScroll = () => {
                    if (!header) return;
                    header.classList.toggle('border-border', window.scrollY > 8);
                    header.classList.toggle('shadow-sm', window.scrollY > 8);
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();

                const menuBtn = document.getElementById('cc-menu-btn');
                const menuPanel = document.getElementById('cc-mobile-panel');
                if (menuBtn && menuPanel) {
                    menuBtn.addEventListener('click', () => {
                        const isOpen = menuPanel.classList.toggle('is-open');
                        menuBtn.classList.toggle('is-open', isOpen);
                        menuBtn.setAttribute('aria-expanded', String(isOpen));
                    });
                    menuPanel.querySelectorAll('a').forEach((link) => {
                        link.addEventListener('click', () => {
                            menuPanel.classList.remove('is-open');
                            menuBtn.classList.remove('is-open');
                            menuBtn.setAttribute('aria-expanded', 'false');
                        });
                    });
                }

                const reveals = document.querySelectorAll('[data-reveal]');
                if (!reveals.length) return;

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

                reveals.forEach((el) => observer.observe(el));
            })();
        </script>
    </body>
</html>