<script setup>
import SidebarLink from '@/Components/SidebarLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash?.status);
const sidebarOpen = ref(false);
const bookingLinkCopied = ref(false);

const ownerNav = [
    { href: '/business', label: 'Dashboard', icon: 'dashboard' },
    { href: '/business/clients', label: 'Clients', icon: 'clients' },
    { href: '/business/bookings', label: 'Bookings', icon: 'bookings' },
    { href: '/business/services', label: 'Services', icon: 'services' },
    { href: '/business/staff', label: 'Staff', icon: 'staff' },
    { href: '/business/payments', label: 'Payments', icon: 'payments' },
];

const barberNav = [
    { href: '/barber', label: 'Today', icon: 'dashboard' },
    { href: '/barber/bookings', label: 'My bookings', icon: 'bookings' },
];

const navItems = computed(() => (user.value?.role === 'owner' ? ownerNav : barberNav));
const isOwner = computed(() => user.value?.role === 'owner');

function navActive(item) {
    const url = page.url.split('?')[0];
    if (item.href === '/business') return url === '/business' || url === '/business/';
    if (item.href === '/barber') return url === '/barber' || url === '/barber/';
    return url.startsWith(item.href);
}

function closeSidebar() {
    sidebarOpen.value = false;
}

async function copyBookingLink() {
    if (!user.value?.booking_url) {
        return;
    }

    await navigator.clipboard.writeText(user.value.booking_url);
    bookingLinkCopied.value = true;

    window.setTimeout(() => {
        bookingLinkCopied.value = false;
    }, 2000);
}

function logout() {
    closeSidebar();
    router.post('/logout', {}, {
        preserveState: false,
        replace: true,
    });
}

let removeListener;
onMounted(() => {
    removeListener = router.on('navigate', () => {
        sidebarOpen.value = false;
    });
});
onUnmounted(() => removeListener?.());
</script>

<template>
    <div class="relative flex min-h-screen app-shell-bg">
        <div
            v-show="sidebarOpen"
            class="fixed inset-0 z-40 bg-ink-950/60 backdrop-blur-sm lg:hidden"
            @click="closeSidebar"
        />

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-[264px] flex-col bg-sidebar transition-transform duration-300 ease-out lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Brand -->
            <div class="flex h-16 items-center gap-2.5 px-5">
                <Link href="/dashboard" class="flex items-center gap-2.5" @click="closeSidebar">
                    <span class="brand-mark">C</span>
                    <span class="font-display text-[15px] font-semibold tracking-tight text-white">Cutcost</span>
                </Link>
            </div>

            <!-- Shop context -->
            <div v-if="user?.shop_name" class="mx-3 mb-2 rounded-xl border border-sidebar-border bg-sidebar-accent/60 px-3.5 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-sidebar-foreground/50">Shop</p>
                <p class="mt-0.5 truncate text-sm font-medium text-white">{{ user.shop_name }}</p>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-3">
                <p class="sidebar-section-label">
                    {{ isOwner ? 'Manage' : 'Schedule' }}
                </p>
                <SidebarLink
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :active="navActive(item)"
                    :label="item.label"
                    :icon="item.icon"
                    @click="closeSidebar"
                />

                <template v-if="isOwner && user?.booking_url">
                    <div class="my-4 border-t border-sidebar-border" />
                    <p class="sidebar-section-label">Share</p>
                    <button
                        type="button"
                        class="sidebar-link"
                        @click="copyBookingLink"
                    >
                        <svg v-if="!bookingLinkCopied" class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <svg v-else class="h-[18px] w-[18px] shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span :class="bookingLinkCopied ? 'text-emerald-400' : ''">
                            {{ bookingLinkCopied ? 'Copied!' : 'Booking link' }}
                        </span>
                    </button>
                </template>
            </nav>

            <!-- User footer -->
            <div class="border-t border-sidebar-border p-3">
                <div class="flex items-center gap-3 px-2 py-2">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-xs font-semibold text-white">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ user?.name }}</p>
                        <p class="truncate text-xs text-sidebar-foreground/70">{{ user?.email }}</p>
                    </div>
                </div>
                <div class="mt-1 space-y-0.5">
                    <SidebarLink href="/profile" :active="page.url.startsWith('/profile')" label="Profile" icon="profile" @click="closeSidebar" />
                    <button type="button" class="sidebar-link" @click="logout">
                        <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Log out
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:pl-[264px]">
            <header class="sticky top-0 z-30 border-b border-border/60 bg-background/85 backdrop-blur-xl">
                <div class="mx-auto flex min-h-16 w-full max-w-7xl items-center justify-between gap-6 px-4 py-3 md:px-6 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border bg-card shadow-sm lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h1 v-if="title" class="page-title truncate">{{ title }}</h1>
                            <p v-if="subtitle" class="page-sub truncate">{{ subtitle }}</p>
                        </div>
                    </div>
                    <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                        <slot name="actions" />
                    </div>
                </div>
            </header>

            <main class="flex-1">
                <div v-if="flash" class="page-shell pb-0">
                    <div class="flash-ok">{{ flash }}</div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
