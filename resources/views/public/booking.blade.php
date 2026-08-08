<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Book at {{ $business->name }} · Cutcost</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet" />
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
    </head>
    <body class="font-sans app-shell-bg">
        @php
            $hasSlots = ! empty($availableSlots);
            $oldTime = old('time');
        @endphp

        <div
            class="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-12"
            x-data="{
                serviceId: @js((string) ($selectedService?->id ?? '')),
                barberId: @js((string) $selectedBarberId),
                date: @js($selectedDate),
                time: @js($oldTime),
                name: @js(old('name', '')),
                phone: @js(old('phone', '')),
                email: @js(old('email', '')),
                notes: @js(old('notes', '')),
                submitted: false,
                hasSlots: @js($hasSlots),
                get hasService() { return !!this.serviceId },
                get hasWhen() { return !!this.date && !!this.time },
                get hasDetails() { return this.name.trim().length > 0 && this.phone.trim().length > 0 },
                get canSubmit() { return this.hasService && this.hasWhen && this.hasDetails && this.hasSlots },
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
                selectTime(slot) {
                    this.time = slot
                },
                onSubmit(event) {
                    this.submitted = true
                    if (!this.canSubmit) {
                        event.preventDefault()
                        this.$refs.checklist?.scrollIntoView({ behavior: 'smooth', block: 'start' })
                    }
                }
            }"
        >
            <div class="mb-8">
                <p>
                    <span class="brand-logo brand-logo-gradient brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></span>
                </p>
                <div class="mt-4">
                    <h1 class="font-display text-3xl font-bold tracking-tight text-ink-950">{{ $business->name }}</h1>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ $business->city ? $business->city.' · ' : '' }}Book an appointment in 3 steps
                    </p>
                </div>
            </div>

            @if ($paymentsBlocked ?? false)
                <div class="mb-4 flex gap-3 rounded-2xl border border-warning/25 bg-warning/[0.06] px-4 py-3.5 text-sm text-ink-900">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold">Online payment is not available yet</p>
                        <p class="mt-0.5 text-[13px] text-ink-600">This shop hasn’t finished Stripe setup, so paid bookings can’t be completed online. Please contact {{ $business->name }} directly.</p>
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="flash-ok mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($services->isEmpty())
                <div class="panel p-6">
                    <p class="text-sm text-ink-600">This shop hasn’t published any services yet. Please check back soon.</p>
                </div>
            @else
                <div x-ref="checklist" class="card mb-4 p-5">
                    <p class="stat-label">Your progress</p>
                    <ol class="mt-3 space-y-2 text-sm">
                        <li class="flex items-center gap-2" :class="hasService ? 'text-ink-950' : 'text-ink-400'">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold" :class="hasService ? 'bg-primary text-white' : 'bg-ink-100 text-ink-400'">1</span>
                            <span><span class="font-medium">Service</span> <span x-text="hasService ? '· done' : '· required'"></span></span>
                        </li>
                        <li class="flex items-center gap-2" :class="hasWhen ? 'text-ink-950' : 'text-ink-400'">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold" :class="hasWhen ? 'bg-primary text-white' : 'bg-ink-100 text-ink-400'">2</span>
                            <span><span class="font-medium">Date &amp; time</span> <span x-text="hasWhen ? '· done' : '· required'"></span></span>
                        </li>
                        <li class="flex items-center gap-2" :class="hasDetails ? 'text-ink-950' : 'text-ink-400'">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold" :class="hasDetails ? 'bg-primary text-white' : 'bg-ink-100 text-ink-400'">3</span>
                            <span><span class="font-medium">Your details</span> <span x-text="hasDetails ? '· done' : '· required'"></span></span>
                        </li>
                    </ol>

                    <div
                        x-show="submitted && missing().length"
                        x-cloak
                        class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                    >
                        <p class="font-semibold">Still needed before you can book:</p>
                        <ul class="mt-1 list-disc ps-5">
                            <template x-for="item in missing()" :key="item">
                                <li x-text="item"></li>
                            </template>
                        </ul>
                    </div>

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <p class="font-semibold">Please fix the following:</p>
                            <ul class="mt-1 list-disc ps-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('public.booking.store', $business) }}"
                    class="space-y-4"
                    @submit="onSubmit"
                >
                    @csrf

                    <section class="card space-y-4 p-6">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">1</span>
                            <h2 class="font-display text-lg font-semibold text-ink-950">Choose a service</h2>
                        </div>

                        <div>
                            <label for="service_id" class="block text-sm font-medium text-ink-700">
                                Service <span class="text-red-600">*</span>
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
                            <label for="barber_id" class="block text-sm font-medium text-ink-700">Stylist</label>
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
                    </section>

                    <section class="card space-y-4 p-6">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">2</span>
                            <h2 class="font-display text-lg font-semibold text-ink-950">Pick a date &amp; time</h2>
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-ink-700">
                                Date <span class="text-red-600">*</span>
                            </label>
                            <input
                                id="date"
                                name="date"
                                type="date"
                                class="form-input mt-1"
                                min="{{ $minDate }}"
                                max="{{ $maxDate }}"
                                required
                                x-model="date"
                                @change="refreshSlots()"
                            >
                            <p class="mt-1 text-xs text-ink-400">Open hours: {{ $hoursLabel }}</p>
                        </div>

                        <div>
                            <p class="block text-sm font-medium text-ink-700">
                                Time <span class="text-red-600">*</span>
                            </p>
                            <input type="hidden" name="time" x-model="time" :required="hasSlots">

                            @if (! $hasSlots)
                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    No open times on this date. Choose another day or stylist above.
                                </div>
                            @else
                                <p class="mt-1 text-xs text-ink-500" x-show="!time" x-cloak>Tap a time below to select it.</p>
                                <div class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                    @foreach ($availableSlots as $slot)
                                        <button
                                            type="button"
                                            class="rounded-xl border px-3 py-2.5 text-sm font-semibold transition"
                                            :class="time === @js($slot)
                                                ? 'border-primary bg-primary text-white shadow-sm shadow-primary/30'
                                                : 'border-ink-200 bg-white text-ink-800 hover:border-primary/50 hover:text-primary'"
                                            @click="selectTime(@js($slot))"
                                        >
                                            {{ $slot }}
                                        </button>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-sm font-medium text-ink-950" x-show="time" x-cloak>
                                    Selected: <span x-text="time"></span>
                                </p>
                            @endif

                            <p class="mt-2 text-sm text-red-600" x-show="submitted && hasSlots && !time" x-cloak>
                                Please select a time.
                            </p>
                        </div>
                    </section>

                    <section class="card space-y-4 p-6">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">3</span>
                            <h2 class="font-display text-lg font-semibold text-ink-950">Your details</h2>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-ink-700">
                                Name <span class="text-red-600">*</span>
                            </label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-input mt-1"
                                required
                                autocomplete="name"
                                x-model="name"
                                :class="submitted && !name.trim() ? 'border-red-400' : ''"
                            >
                            <p class="mt-1 text-sm text-red-600" x-show="submitted && !name.trim()" x-cloak>Name is required.</p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-ink-700">
                                Phone <span class="text-red-600">*</span>
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                class="form-input mt-1"
                                required
                                autocomplete="tel"
                                x-model="phone"
                                :class="submitted && !phone.trim() ? 'border-red-400' : ''"
                            >
                            <p class="mt-1 text-sm text-red-600" x-show="submitted && !phone.trim()" x-cloak>Phone is required.</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-ink-700">Email <span class="font-normal text-ink-400">(optional)</span></label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-input mt-1"
                                autocomplete="email"
                                x-model="email"
                            >
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-ink-700">Notes <span class="font-normal text-ink-400">(optional)</span></label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                class="form-textarea"
                                x-model="notes"
                            ></textarea>
                        </div>
                    </section>

                    <div class="card p-5">
                        @if ($requiresPayment && $selectedService)
                            <div class="mb-4 rounded-xl border border-primary/20 bg-primary/[0.05] px-4 py-3.5 text-sm text-ink-800">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-medium">Total due today</span>
                                    <span class="font-display text-xl font-semibold text-primary">{{ $selectedService->priceLabel() }}</span>
                                </div>
                                <p class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    You’ll pay securely with Stripe before your slot is confirmed.
                                </p>
                            </div>
                        @endif

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
                        <p class="mt-3 text-center text-xs text-ink-500" x-show="!canSubmit" x-cloak>
                            Complete the required steps above to book.
                        </p>
                        <p class="mt-3 text-center text-xs text-ink-500" x-show="canSubmit" x-cloak>
                            @if ($requiresPayment)
                                Ready — continue to secure checkout.
                            @else
                                Ready to book — tap confirm.
                            @endif
                        </p>
                    </div>
                </form>
            @endif

            <p class="mt-8 text-center text-xs text-ink-400">Powered by Cutcost</p>
        </div>

        <style>[x-cloak]{display:none!important}</style>
    </body>
</html>
