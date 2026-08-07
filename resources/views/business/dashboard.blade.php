<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">{{ $business->name }}</h1>
            <p class="page-sub">{{ $business->city ? $business->city.' · ' : '' }}Overview</p>
        </div>
        <div class="flex shrink-0 gap-2">
            <a href="{{ $business->publicBookingUrl() }}" target="_blank" class="btn-secondary hidden sm:inline-flex">Booking link</a>
            <a href="{{ route('business.bookings.create') }}" class="btn-primary">New booking</a>
        </div>
    </x-slot>

    <div class="page-shell">
        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Clients</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $business->clients_count }}</p>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Services</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77A6 6 0 0 1 21 8.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8.5a6 6 0 0 1 4.2 1.8z"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $business->services_count }}</p>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Barbers</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $business->barbers_count }}</p>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Bookings</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $business->bookings_count }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Today's schedule --}}
            <div class="card lg:col-span-2">
                <div class="card-header flex-row items-center justify-between space-y-0">
                    <div>
                        <h2 class="card-title">Today’s appointments</h2>
                        <p class="card-description">{{ now()->format('l, j F') }}</p>
                    </div>
                    <a href="{{ route('business.bookings.index') }}" class="btn-ghost">View all</a>
                </div>
                <div class="card-content">
                    @forelse ($todaysBookings as $booking)
                        <div class="flex items-center justify-between gap-4 py-3 {{ ! $loop->last ? 'border-b' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-sm font-semibold">
                                    {{ $booking->starts_at->format('H:i') }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ $booking->client->name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $booking->service->name }} · {{ $booking->barber->name }}</p>
                                </div>
                            </div>
                            <span class="badge-outline">{{ $booking->status->label() }}</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <p class="text-sm font-medium">No appointments today</p>
                            <p class="mt-1 text-xs text-muted-foreground">Book one or share your client link.</p>
                            <a href="{{ route('business.bookings.create') }}" class="btn-primary mt-4">Book appointment</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick actions + booking link --}}
            <div class="space-y-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick actions</h2>
                        <p class="card-description">Common tasks for your shop</p>
                    </div>
                    <div class="card-content grid gap-2">
                        <a href="{{ route('business.clients.create') }}" class="btn-secondary justify-start">Add client</a>
                        <a href="{{ route('business.services.create') }}" class="btn-secondary justify-start">Add service</a>
                        <a href="{{ route('business.staff.create') }}" class="btn-secondary justify-start">Add barber</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Client booking link</h2>
                        <p class="card-description">Share so clients can book themselves</p>
                    </div>
                    <div class="card-content space-y-3">
                        <code class="block break-all rounded-md border bg-muted px-3 py-2 text-xs">{{ $business->publicBookingUrl() }}</code>
                        <div class="flex gap-2">
                            <a href="{{ $business->publicBookingUrl() }}" target="_blank" class="btn-secondary flex-1">Open</a>
                            <button type="button" class="btn-primary flex-1" onclick="navigator.clipboard.writeText(@js($business->publicBookingUrl()))">Copy</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
