@php
    $user = Auth::user();
    $shop = $user->shop();
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-200 ease-in-out lg:translate-x-0"
>
    {{-- Brand --}}
    <div class="flex h-14 items-center border-b border-sidebar-border px-4">
        <a href="{{ route('dashboard') }}" class="transition-opacity hover:opacity-85">
            <span class="brand-logo brand-logo-gradient brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></span>
        </a>
    </div>

    {{-- Shop context --}}
    @if ($shop)
        <div class="border-b border-sidebar-border px-4 py-3">
            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Shop</p>
            <p class="mt-1 truncate text-sm font-medium text-foreground">{{ $shop->name }}</p>
            @if ($user->isOwner())
                <p class="truncate text-xs text-muted-foreground">{{ $user->role->label() }}</p>
            @else
                <p class="truncate text-xs text-muted-foreground">{{ $user->role->label() }}</p>
            @endif
        </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        @if ($user->isOwner())
            <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Manage</p>

            <x-sidebar-link :href="route('business.dashboard')" :active="request()->routeIs('business.dashboard')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                Dashboard
            </x-sidebar-link>

            <x-sidebar-link :href="route('business.clients.index')" :active="request()->routeIs('business.clients.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Clients
            </x-sidebar-link>

            <x-sidebar-link :href="route('business.bookings.index')" :active="request()->routeIs('business.bookings.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Bookings
            </x-sidebar-link>

            <x-sidebar-link :href="route('business.services.index')" :active="request()->routeIs('business.services.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77A6 6 0 0 1 21 8.5V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8.5a6 6 0 0 1 4.2 1.8z"/></svg>
                Services
            </x-sidebar-link>

            <x-sidebar-link :href="route('business.staff.index')" :active="request()->routeIs('business.staff.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                Staff
            </x-sidebar-link>

            @if ($shop?->public_booking_enabled)
                <div class="my-4 border-t border-sidebar-border"></div>
                <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Share</p>
                <a
                    href="{{ $shop->publicBookingUrl() }}"
                    target="_blank"
                    class="sidebar-link text-muted-foreground"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Booking link
                </a>
            @endif
        @elseif ($user->isBarber())
            <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Schedule</p>

            <x-sidebar-link :href="route('barber.dashboard')" :active="request()->routeIs('barber.dashboard')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                Today
            </x-sidebar-link>

            <x-sidebar-link :href="route('barber.bookings.index')" :active="request()->routeIs('barber.bookings.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                My bookings
            </x-sidebar-link>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="border-t border-sidebar-border p-3">
        <div class="flex items-center gap-3 rounded-md px-2 py-2">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-foreground">{{ $user->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $user->email }}</p>
            </div>
        </div>
        <div class="mt-1 space-y-0.5">
            <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile
            </x-sidebar-link>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link w-full text-left text-muted-foreground">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Log out
                </button>
            </form>
        </div>
    </div>
</aside>
