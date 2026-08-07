<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="page-title">Today’s schedule</h1>
            <p class="page-sub">{{ $business?->name ?? 'Your shop' }} · {{ now()->format('l, j F') }}</p>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Today</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $todaysBookings->count() }}</p>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Upcoming</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $upcomingCount }}</p>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <p class="stat-label">Clients seen</p>
                    <div class="stat-card-icon">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                </div>
                <p class="stat-value mt-3">{{ $clientsSeen }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-row items-center justify-between space-y-0">
                <div>
                    <h2 class="card-title">Appointments</h2>
                    <p class="card-description">Your chair today</p>
                </div>
                <a href="{{ route('barber.bookings.index') }}" class="btn-ghost">All bookings</a>
            </div>
            <div class="card-content">
                @forelse ($todaysBookings as $booking)
                    <div class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between {{ ! $loop->last ? 'border-b' : '' }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-sm font-semibold">
                                {{ $booking->starts_at->format('H:i') }}
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ $booking->client->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $booking->service->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge-outline">{{ $booking->status->label() }}</span>
                            @if ($booking->status === \App\Enums\BookingStatus::Scheduled)
                                <form method="POST" action="{{ route('barber.bookings.status', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button class="btn-primary h-8 px-3 text-xs">Complete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p class="text-sm font-medium">Nothing on the book today</p>
                        <p class="mt-1 text-xs text-muted-foreground">Enjoy the quiet — or check upcoming bookings.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
