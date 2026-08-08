<nav x-data="{ open: false }" class="cutcost-nav">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="font-display text-xl font-bold tracking-tight text-ink-950">
                    Cutcost
                </a>

                <div class="hidden items-center gap-1 sm:flex">
                    @if (Auth::user()->isOwner())
                        <x-nav-link :href="route('business.dashboard')" :active="request()->routeIs('business.dashboard')">Dashboard</x-nav-link>
                        <x-nav-link :href="route('business.clients.index')" :active="request()->routeIs('business.clients.*')">Clients</x-nav-link>
                        <x-nav-link :href="route('business.bookings.index')" :active="request()->routeIs('business.bookings.*')">Bookings</x-nav-link>
                        <x-nav-link :href="route('business.services.index')" :active="request()->routeIs('business.services.*')">Services</x-nav-link>
                        <x-nav-link :href="route('business.staff.index')" :active="request()->routeIs('business.staff.*')">Staff</x-nav-link>
                    @elseif (Auth::user()->isBarber())
                        <x-nav-link :href="route('barber.dashboard')" :active="request()->routeIs('barber.dashboard')">Schedule</x-nav-link>
                        <x-nav-link :href="route('barber.bookings.index')" :active="request()->routeIs('barber.bookings.*')">My bookings</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden items-center sm:flex">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-xl border border-ink-900/10 bg-white px-3 py-2 text-sm font-medium text-ink-700 transition hover:border-ink-900/20 hover:text-ink-950">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-ink-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-ink-500 hover:bg-ink-100 hover:text-ink-900">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-ink-100 sm:hidden">
        <div class="space-y-1 px-3 py-3">
            @if (Auth::user()->isOwner())
                <x-responsive-nav-link :href="route('business.dashboard')" :active="request()->routeIs('business.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('business.clients.index')" :active="request()->routeIs('business.clients.*')">Clients</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('business.bookings.index')" :active="request()->routeIs('business.bookings.*')">Bookings</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('business.services.index')" :active="request()->routeIs('business.services.*')">Services</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('business.staff.index')" :active="request()->routeIs('business.staff.*')">Staff</x-responsive-nav-link>
            @elseif (Auth::user()->isBarber())
                <x-responsive-nav-link :href="route('barber.dashboard')" :active="request()->routeIs('barber.dashboard')">Schedule</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('barber.bookings.index')" :active="request()->routeIs('barber.bookings.*')">My bookings</x-responsive-nav-link>
            @endif
        </div>
        <div class="border-t border-ink-100 px-4 py-4">
            <div class="font-medium text-ink-900">{{ Auth::user()->name }}</div>
            <div class="text-sm text-ink-500">{{ Auth::user()->email }}</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
