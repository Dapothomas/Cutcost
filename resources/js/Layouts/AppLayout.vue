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
    { href: '/barber', label: 'Today', icon: 'bookings' },
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
    closeSidebar();

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
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="closeSidebar"
        />

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-sidebar-border bg-sidebar/95 shadow-sm shadow-primary/5 backdrop-blur-xl transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-14 items-center border-b border-sidebar-border px-4">
                <Link href="/dashboard" class="flex items-center gap-2.5 font-semibold text-foreground" @click="closeSidebar">
                    <span class="brand-mark">C</span>
                    <span>Cutcost</span>
                </Link>
            </div>

            <div v-if="user?.shop_name" class="border-b border-sidebar-border bg-primary/[0.03] px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-primary/70">Shop</p>
                <p class="mt-1 truncate text-sm font-medium text-foreground">{{ user.shop_name }}</p>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">
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
                    <p class="mb-2 px-3 text-xs font-medium uppercase tracking-wider text-muted-foreground">Share</p>
                    <button
                        type="button"
                        class="sidebar-link w-full text-left text-muted-foreground"
                        @click="copyBookingLink"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        {{ bookingLinkCopied ? 'Copied!' : 'Booking link' }}
                    </button>
                </template>
            </nav>

            <div class="border-t border-sidebar-border bg-muted/30 p-3">
                <div class="flex items-center gap-3 rounded-xl bg-background px-2 py-2 shadow-sm">
                    <span class="brand-mark h-9 w-9 text-xs">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium">{{ user?.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
                    </div>
                </div>
                <div class="mt-1 space-y-0.5">
                    <SidebarLink href="/profile" :active="page.url.startsWith('/profile')" label="Profile" icon="profile" @click="closeSidebar" />
                    <button type="button" class="sidebar-link w-full text-left text-muted-foreground" @click="logout">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Log out
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex min-h-14 items-center justify-between gap-6 border-b border-primary/10 bg-background/80 px-4 py-4 backdrop-blur-xl md:px-6">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h1 v-if="title" class="page-title">{{ title }}</h1>
                        <p v-if="subtitle" class="page-sub">{{ subtitle }}</p>
                    </div>
                </div>
                <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                    <slot name="actions" />
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
