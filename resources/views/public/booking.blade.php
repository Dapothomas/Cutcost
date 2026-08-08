<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title>Book at {{ $business->name }} · Cutcost</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        @if ($tokens = $business->brandTheme())
            <style>
                :root {
                    --primary: {{ $tokens['primary'] }};
                    --primary-deep: {{ $tokens['primary_deep'] }};
                    --ring: {{ $tokens['ring'] }};
                    --accent: {{ $tokens['accent'] }};
                    --accent-foreground: {{ $tokens['accent_foreground'] }};
                    --background: {{ $tokens['background'] }};
                    --secondary: {{ $tokens['secondary'] }};
                    --secondary-foreground: {{ $tokens['secondary_foreground'] }};
                    --muted: {{ $tokens['muted'] }};
                    --muted-foreground: {{ $tokens['muted_foreground'] }};
                    --border: {{ $tokens['border'] }};
                    --input: {{ $tokens['input'] }};
                }
            </style>
        @endif
        <style>
            [x-cloak] { display: none !important; }
            .book-shell {
                background-color: hsl(var(--background));
                background-image:
                    radial-gradient(1000px 480px at 0% 0%, hsl(var(--primary) / 0.16), transparent 55%),
                    radial-gradient(800px 420px at 100% 0%, hsl(var(--primary) / 0.10), transparent 50%),
                    linear-gradient(180deg, hsl(var(--background)) 0%, hsl(var(--secondary) / 0.45) 100%);
            }
        </style>
    </head>
    <body class="font-sans text-foreground antialiased">
        @php
            $hasSlots = ! empty($availableSlots);
            $oldTime = old('time');
            $serviceOptions = $services->map(fn ($service) => [
                'id' => (string) $service->id,
                'name' => $service->name,
                'duration' => $service->duration_minutes,
                'price' => $service->priceLabel(),
            ])->values();
            $barberOptions = $barbers->map(fn ($barber) => [
                'id' => (string) $barber->id,
                'name' => $barber->name,
            ])->values();
        @endphp

        <div
            class="book-shell min-h-dvh lg:h-dvh lg:overflow-hidden"
            x-data="{
                serviceId: @js((string) ($selectedService?->id ?? '')),
                barberId: @js((string) $selectedBarberId),
                date: @js($selectedDate),
                time: @js($oldTime),
                name: @js(old('name', '')),
                phone: @js(old('phone', '')),
                email: @js(old('email', '')),
                notes: @js(old('notes', '')),
                showNotes: @js((bool) old('notes')),
                submitted: false,
                hasSlots: @js($hasSlots),
                services: @js($serviceOptions),
                barbers: @js($barberOptions),
                get service() { return this.services.find(s => s.id === this.serviceId) || null },
                get barber() {
                    if (this.barberId === 'any' || !this.barberId) return null
                    return this.barbers.find(b => b.id === this.barberId) || null
                },
                get hasService() { return !!this.serviceId },
                get hasWhen() { return !!this.date && !!this.time },
                get hasDetails() { return this.name.trim().length > 0 && this.phone.trim().length > 0 },
                get canSubmit() { return this.hasService && this.hasWhen && this.hasDetails && this.hasSlots },
                get dateLabel() {
                    if (!this.date) return null
                    try {
                        return new Date(this.date + 'T12:00:00').toLocaleDateString(undefined, {
                            weekday: 'short', day: 'numeric', month: 'short'
                        })
                    } catch { return this.date }
                },
                get step() {
                    if (!this.hasService) return 1
                    if (!this.hasWhen) return 2
                    return 3
                },
                missing() {
                    const items = []
                    if (!this.hasService) items.push('Choose a service')
                    if (!this.date) items.push('Choose a date')
                    if (this.date && !this.hasSlots) items.push('Pick a date with open times')
                    if (this.hasSlots && !this.time) items.push('Select a time')
                    if (!this.name.trim()) items.push('Enter your name')
                    if (!this.phone.trim()) items.push('Enter your phone number')
                    return items
                },
                refreshSlots() {
                    const params = new URLSearchParams({
                        service_id: this.serviceId,
                        barber_id: this.barberId,
                        date: this.date,
                    })
                    window.location = `{{ route('public.booking.show', $business) }}?${params.toString()}`
                },
                selectTime(slot) { this.time = slot },
                onSubmit(event) {
                    this.submitted = true
                    if (!this.canSubmit) {
                        event.preventDefault()
                    }
                }
            }"
        >
            <div class="mx-auto flex min-h-dvh w-full max-w-6xl flex-col lg:h-dvh lg:flex-row lg:items-stretch">
                {{-- Brand / summary rail --}}
                <aside class="relative flex flex-col justify-between px-5 pb-4 pt-[max(1.25rem,env(safe-area-inset-top))] sm:px-8 lg:w-[38%] lg:px-10 lg:py-8 xl:w-[36%]">
                    <div>
                        <p class="brand-logo brand-logo-gradient brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></p>
                        <h1 class="mt-5 font-display text-3xl font-semibold tracking-tight text-foreground sm:text-4xl lg:mt-8 lg:text-[2.5rem] lg:leading-[1.1]">
                            {{ $business->name }}
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            @if ($business->city){{ $business->city }} · @endif Book your appointment
                        </p>

                        @if ($paymentsBlocked ?? false)
                            <div class="mt-5 rounded-2xl bg-warning/[0.1] px-4 py-3 text-sm text-foreground">
                                <p class="font-semibold">Online payment unavailable</p>
                                <p class="mt-1 text-[13px] text-muted-foreground">Contact {{ $business->name }} to book paid services.</p>
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="flash-ok mt-5">{{ session('status') }}</div>
                        @endif

                        <div class="mt-6 hidden rounded-2xl bg-card/70 p-5 shadow-card backdrop-blur-sm lg:block">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">Your booking</p>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">Service</dt>
                                    <dd class="text-right font-medium" x-text="service?.name || '—'"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">Stylist</dt>
                                    <dd class="text-right font-medium" x-text="barber?.name || 'First available'"></dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted-foreground">When</dt>
                                    <dd class="text-right font-medium">
                                        <span x-text="dateLabel || '—'"></span>
                                        <span x-show="time" x-cloak> · <span x-text="time"></span></span>
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-4" x-show="service" x-cloak>
                                    <dt class="text-muted-foreground">Duration</dt>
                                    <dd class="text-right font-medium"><span x-text="service?.duration"></span> min</dd>
                                </div>
                            </dl>
                            @if ($requiresPayment)
                                <div class="mt-5 flex items-end justify-between border-t border-border/50 pt-4">
                                    <div>
                                        <p class="text-xs text-muted-foreground">Total due today</p>
                                        <p class="mt-0.5 font-display text-2xl font-semibold text-primary" x-text="service?.price || '—'"></p>
                                    </div>
                                    <p class="pb-1 text-[11px] text-muted-foreground">Secure Stripe checkout</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-6 hidden text-xs text-muted-foreground lg:block">Powered by Cutcost</p>
                </aside>

                {{-- Booking form --}}
                <main class="relative flex min-h-0 flex-1 flex-col px-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] sm:px-6 lg:px-8 lg:py-6">
                    @if ($services->isEmpty())
                        <div class="my-auto rounded-2xl bg-card p-6 shadow-card">
                            <p class="text-sm text-muted-foreground">This shop hasn’t published any services yet. Please check back soon.</p>
                        </div>
                    @else
                        <form
                            method="POST"
                            action="{{ route('public.booking.store', $business) }}"
                            class="flex min-h-0 flex-1 flex-col rounded-2xl bg-card shadow-card lg:overflow-hidden"
                            @submit="onSubmit"
                        >
                            @csrf

                            {{-- Progress --}}
                            <div class="shrink-0 border-b border-border/40 px-4 py-3.5 sm:px-6">
                                <ol class="flex items-center gap-2 sm:gap-3">
                                    <li class="flex min-w-0 items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition" :class="step > 1 ? 'bg-primary text-primary-foreground' : (step === 1 ? 'bg-primary text-primary-foreground ring-4 ring-primary/15' : 'bg-muted text-muted-foreground')">1</span>
                                        <span class="truncate text-xs font-medium sm:text-sm" :class="step >= 1 ? 'text-foreground' : 'text-muted-foreground'">Service</span>
                                    </li>
                                    <span class="h-px flex-1 bg-border/70"></span>
                                    <li class="flex min-w-0 items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition" :class="step > 2 ? 'bg-primary text-primary-foreground' : (step === 2 ? 'bg-primary text-primary-foreground ring-4 ring-primary/15' : 'bg-muted text-muted-foreground')">2</span>
                                        <span class="truncate text-xs font-medium sm:text-sm" :class="step >= 2 ? 'text-foreground' : 'text-muted-foreground'">When</span>
                                    </li>
                                    <span class="h-px flex-1 bg-border/70"></span>
                                    <li class="flex min-w-0 items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition" :class="step === 3 && hasDetails ? 'bg-primary text-primary-foreground' : (step === 3 ? 'bg-primary text-primary-foreground ring-4 ring-primary/15' : 'bg-muted text-muted-foreground')">3</span>
                                        <span class="truncate text-xs font-medium sm:text-sm" :class="step >= 3 ? 'text-foreground' : 'text-muted-foreground'">Details</span>
                                    </li>
                                </ol>

                                <div
                                    x-show="submitted && missing().length"
                                    x-cloak
                                    class="mt-3 rounded-xl bg-destructive/[0.08] px-3 py-2.5 text-sm text-destructive"
                                >
                                    <p class="font-semibold">Still needed:</p>
                                    <ul class="mt-1 list-disc ps-5">
                                        <template x-for="item in missing()" :key="item">
                                            <li x-text="item"></li>
                                        </template>
                                    </ul>
                                </div>

                                @if ($errors->any())
                                    <div class="mt-3 rounded-xl bg-destructive/[0.08] px-3 py-2.5 text-sm text-destructive">
                                        <p class="font-semibold">Please fix the following:</p>
                                        <ul class="mt-1 list-disc ps-5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5 lg:overflow-y-auto">
                                {{-- Step 1 --}}
                                <section>
                                    <div class="mb-3 flex items-baseline justify-between gap-3">
                                        <h2 class="font-display text-base font-semibold">Service &amp; stylist</h2>
                                        <span class="text-xs text-muted-foreground">Step 1</span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="service_id" class="mb-1.5 block text-sm font-medium">
                                                Service <span class="text-destructive">*</span>
                                            </label>
                                            <select
                                                id="service_id"
                                                name="service_id"
                                                class="form-select"
                                                required
                                                x-model="serviceId"
                                                @change="refreshSlots()"
                                            >
                                                @foreach ($services as $service)
                                                    <option value="{{ $service->id }}">
                                                        {{ $service->name }} · {{ $service->duration_minutes }} min · {{ $service->priceLabel() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label for="barber_id" class="mb-1.5 block text-sm font-medium">Stylist</label>
                                            <select
                                                id="barber_id"
                                                name="barber_id"
                                                class="form-select"
                                                x-model="barberId"
                                                @change="refreshSlots()"
                                            >
                                                <option value="any">First available</option>
                                                @foreach ($barbers as $barber)
                                                    <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                {{-- Step 2 --}}
                                <section>
                                    <div class="mb-3 flex items-baseline justify-between gap-3">
                                        <h2 class="font-display text-base font-semibold">Date &amp; time</h2>
                                        <span class="text-xs text-muted-foreground">{{ $hoursLabel }}</span>
                                    </div>
                                    <div class="grid gap-3 lg:grid-cols-[12rem_minmax(0,1fr)]">
                                        <div>
                                            <label for="date" class="mb-1.5 block text-sm font-medium">
                                                Date <span class="text-destructive">*</span>
                                            </label>
                                            <input
                                                id="date"
                                                name="date"
                                                type="date"
                                                class="form-input"
                                                min="{{ $minDate }}"
                                                max="{{ $maxDate }}"
                                                required
                                                x-model="date"
                                                @change="refreshSlots()"
                                            >
                                        </div>
                                        <div class="min-w-0">
                                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                                <p class="text-sm font-medium">
                                                    Time <span class="text-destructive">*</span>
                                                </p>
                                                <p class="text-xs font-medium text-primary" x-show="time" x-cloak>
                                                    Selected <span x-text="time"></span>
                                                </p>
                                            </div>
                                            <input type="hidden" name="time" x-model="time" :required="hasSlots">

                                            @if (! $hasSlots)
                                                <div class="rounded-xl bg-warning/[0.1] px-3 py-3 text-sm text-foreground">
                                                    No open times on this date. Try another day or stylist.
                                                </div>
                                            @else
                                                <div class="grid max-h-[9.5rem] grid-cols-3 gap-1.5 overflow-y-auto overscroll-contain pr-0.5 sm:grid-cols-4 md:grid-cols-5 lg:max-h-[11rem]">
                                                    @foreach ($availableSlots as $slot)
                                                        <button
                                                            type="button"
                                                            class="rounded-xl px-2 py-2 text-center text-sm font-semibold transition"
                                                            :class="time === @js($slot)
                                                                ? 'bg-primary text-primary-foreground shadow-sm'
                                                                : 'bg-secondary text-foreground hover:bg-primary/10 hover:text-primary'"
                                                            @click="selectTime(@js($slot))"
                                                        >
                                                            {{ $slot }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <p class="mt-1.5 text-xs text-destructive" x-show="submitted && hasSlots && !time" x-cloak>
                                                Please select a time.
                                            </p>
                                        </div>
                                    </div>
                                </section>

                                {{-- Step 3 --}}
                                <section>
                                    <div class="mb-3 flex items-baseline justify-between gap-3">
                                        <h2 class="font-display text-base font-semibold">Your details</h2>
                                        <span class="text-xs text-muted-foreground">Step 3</span>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="name" class="mb-1.5 block text-sm font-medium">
                                                Name <span class="text-destructive">*</span>
                                            </label>
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                class="form-input"
                                                required
                                                autocomplete="name"
                                                x-model="name"
                                                :class="submitted && !name.trim() ? 'bg-destructive/[0.08] ring-4 ring-destructive/15' : ''"
                                            >
                                        </div>
                                        <div>
                                            <label for="phone" class="mb-1.5 block text-sm font-medium">
                                                Phone <span class="text-destructive">*</span>
                                            </label>
                                            <input
                                                id="phone"
                                                name="phone"
                                                type="tel"
                                                class="form-input"
                                                required
                                                autocomplete="tel"
                                                x-model="phone"
                                                :class="submitted && !phone.trim() ? 'bg-destructive/[0.08] ring-4 ring-destructive/15' : ''"
                                            >
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label for="email" class="mb-1.5 block text-sm font-medium">
                                                Email <span class="font-normal text-muted-foreground">(optional)</span>
                                            </label>
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                class="form-input"
                                                autocomplete="email"
                                                x-model="email"
                                            >
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="mt-3 text-sm font-medium text-primary hover:underline"
                                        @click="showNotes = !showNotes"
                                        x-text="showNotes ? 'Hide notes' : 'Add a note (optional)'"
                                    ></button>
                                    <div x-show="showNotes" x-cloak class="mt-2">
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows="2"
                                            class="form-textarea"
                                            placeholder="Anything the stylist should know?"
                                            x-model="notes"
                                        ></textarea>
                                    </div>
                                </section>

                                {{-- Mobile summary --}}
                                <div class="rounded-2xl bg-secondary/70 p-4 lg:hidden">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold" x-text="service?.name || 'Select a service'"></p>
                                            <p class="mt-0.5 text-xs text-muted-foreground">
                                                <span x-text="dateLabel || 'Pick a date'"></span>
                                                <span x-show="time" x-cloak> · <span x-text="time"></span></span>
                                            </p>
                                        </div>
                                        @if ($requiresPayment)
                                            <p class="shrink-0 font-display text-lg font-semibold text-primary" x-text="service?.price || ''"></p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 border-t border-border/40 bg-card px-4 py-3.5 sm:px-6">
                                <button
                                    type="submit"
                                    class="btn-primary w-full justify-center"
                                    :class="!canSubmit ? 'opacity-60' : ''"
                                    @if ($paymentsBlocked ?? false) disabled @endif
                                >
                                    @if ($requiresPayment)
                                        Continue to payment
                                    @else
                                        Confirm booking
                                    @endif
                                </button>
                                <p class="mt-2 text-center text-xs text-muted-foreground" x-show="!canSubmit" x-cloak>
                                    Complete the steps above to continue
                                </p>
                                <p class="mt-2 text-center text-xs text-muted-foreground" x-show="canSubmit" x-cloak>
                                    @if ($requiresPayment)
                                        Next: secure Stripe checkout
                                    @else
                                        You’re ready to book
                                    @endif
                                </p>
                            </div>
                        </form>

                        <p class="mt-3 text-center text-xs text-muted-foreground lg:hidden">Powered by Cutcost</p>
                    @endif
                </main>
            </div>
        </div>
    </body>
</html>
