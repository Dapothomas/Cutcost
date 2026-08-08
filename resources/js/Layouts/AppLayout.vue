<script setup>
import SidebarLink from '@/Components/SidebarLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watchEffect } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    /** Full-bleed gradient banner; header sits inside it until scroll */
    hero: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash?.status);
const themeTokens = computed(() => page.props.theme?.tokens ?? null);
const notifications = computed(() => page.props.notifications ?? { items: [], unread_count: 0, see_all_href: null });
const sidebarOpen = ref(false);
const bookingLinkCopied = ref(false);
const notificationsOpen = ref(false);
const profileOpen = ref(false);
const headerRef = ref(null);
const heroRef = ref(null);
const scrolled = ref(false);
const headerHeight = ref(64);

const ownerNav = [
    { href: '/business', label: 'Dashboard', icon: 'dashboard' },
    { href: '/business/clients', label: 'Clients', icon: 'clients' },
    { href: '/business/bookings', label: 'Bookings', icon: 'bookings' },
    { href: '/business/services', label: 'Services', icon: 'services' },
    { href: '/business/staff', label: 'Stylists', icon: 'staff' },
    { href: '/business/payments', label: 'Payments', icon: 'payments' },
    { href: '/business/settings', label: 'Settings', icon: 'settings' },
];

const themeVarMap = {
    primary: '--primary',
    primary_deep: '--primary-deep',
    ring: '--ring',
    accent: '--accent',
    accent_foreground: '--accent-foreground',
    background: '--background',
    secondary: '--secondary',
    secondary_foreground: '--secondary-foreground',
    muted: '--muted',
    muted_foreground: '--muted-foreground',
    border: '--border',
    input: '--input',
    sidebar_background: '--sidebar-background',
    sidebar_foreground: '--sidebar-foreground',
    sidebar_border: '--sidebar-border',
    sidebar_accent: '--sidebar-accent',
    sidebar_accent_foreground: '--sidebar-accent-foreground',
};

watchEffect(() => {
    const root = document.documentElement;
    const tokens = themeTokens.value;

    Object.entries(themeVarMap).forEach(([key, cssVar]) => {
        if (tokens?.[key]) {
            root.style.setProperty(cssVar, tokens[key]);
        } else {
            root.style.removeProperty(cssVar);
        }
    });
});

const barberNav = [
    { href: '/barber', label: 'Today', icon: 'dashboard' },
    { href: '/barber/bookings', label: 'My bookings', icon: 'bookings' },
];

const navItems = computed(() => (user.value?.role === 'owner' ? ownerNav : barberNav));
const isOwner = computed(() => user.value?.role === 'owner');
const homeHref = computed(() => (isOwner.value ? '/business' : '/barber'));
const headerSolid = computed(() => !props.hero || scrolled.value);
const showCompactTitle = computed(() => headerSolid.value);

const mobileTabs = computed(() => {
    if (isOwner.value) {
        return [
            { href: '/business', label: 'Home', icon: 'home', match: 'exact' },
            { href: '/business/bookings', label: 'Bookings', icon: 'bookings', match: 'prefix' },
            { href: '/business/clients', label: 'Clients', icon: 'clients', match: 'prefix' },
            { href: '/business/settings', label: 'Settings', icon: 'settings', match: 'prefix' },
        ];
    }

    return [
        { href: '/barber', label: 'Today', icon: 'home', match: 'exact' },
        { href: '/barber/bookings', label: 'Bookings', icon: 'bookings', match: 'prefix' },
        { href: '/profile', label: 'Profile', icon: 'profile', match: 'prefix' },
    ];
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

function navActive(item) {
    const url = page.url.split('?')[0];
    if (item.href === '/business') return url === '/business' || url === '/business/';
    if (item.href === '/barber') return url === '/barber' || url === '/barber/';
    return url.startsWith(item.href);
}

function tabActive(tab) {
    const url = page.url.split('?')[0];
    if (tab.match === 'exact') {
        return url === tab.href || url === `${tab.href}/`;
    }
    return url === tab.href || url.startsWith(`${tab.href}/`);
}

function closeSidebar() {
    sidebarOpen.value = false;
}

function closeMenus() {
    notificationsOpen.value = false;
    profileOpen.value = false;
}

function toggleNotifications() {
    profileOpen.value = false;
    notificationsOpen.value = !notificationsOpen.value;
}

function toggleProfile() {
    notificationsOpen.value = false;
    profileOpen.value = !profileOpen.value;
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
    closeMenus();
    router.post('/logout', {}, {
        preserveState: false,
        replace: true,
    });
}

function onDocumentClick(event) {
    if (!headerRef.value?.contains(event.target)) {
        closeMenus();
    }
}

function measureHeader() {
    if (headerRef.value) {
        headerHeight.value = headerRef.value.offsetHeight;
    }
}

function onScroll() {
    if (!props.hero || !heroRef.value) {
        scrolled.value = false;
        return;
    }

    scrolled.value = heroRef.value.getBoundingClientRect().bottom <= headerHeight.value + 8;
}

let removeListener;
let headerObserver;
onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    measureHeader();
    headerObserver = typeof ResizeObserver !== 'undefined' && headerRef.value
        ? new ResizeObserver(() => {
            measureHeader();
            onScroll();
        })
        : null;
    if (headerRef.value) {
        headerObserver?.observe(headerRef.value);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', measureHeader, { passive: true });
    onScroll();
    removeListener = router.on('navigate', () => {
        sidebarOpen.value = false;
        closeMenus();
        requestAnimationFrame(() => {
            measureHeader();
            onScroll();
        });
    });
});
onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('resize', measureHeader);
    headerObserver?.disconnect();
    removeListener?.();
});
</script>

<template>
    <div class="app-frame relative flex min-h-dvh app-shell-bg">
        <div
            v-show="sidebarOpen"
            class="fixed inset-0 z-40 bg-ink-950/60 backdrop-blur-sm lg:hidden"
            @click="closeSidebar"
        />

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-[min(18rem,86vw)] flex-col bg-sidebar pt-[env(safe-area-inset-top)] transition-transform duration-300 ease-out lg:w-[264px] lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-14 items-center justify-between px-4 sm:h-16 sm:px-5">
                <Link :href="homeHref" class="group" @click="closeSidebar">
                    <span class="brand-logo brand-logo-light brand-logo-sm transition-opacity group-hover:opacity-90">
                        Cut<span class="brand-logo-accent">cost</span>
                    </span>
                </Link>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-sidebar-foreground hover:bg-white/10 lg:hidden"
                    aria-label="Close menu"
                    @click="closeSidebar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div v-if="user?.shop_name" class="mx-3 mb-2 rounded-xl border border-sidebar-border bg-sidebar-accent/60 px-3.5 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-sidebar-foreground/50">Shop</p>
                <p class="mt-0.5 truncate text-sm font-medium text-white">{{ user.shop_name }}</p>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto overscroll-contain px-3 py-2 pb-[calc(1rem+env(safe-area-inset-bottom))]">
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
                    <button type="button" class="sidebar-link" @click="copyBookingLink">
                        <svg v-if="!bookingLinkCopied" class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <svg v-else class="h-[18px] w-[18px] shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span :class="bookingLinkCopied ? 'text-emerald-400' : ''">
                            {{ bookingLinkCopied ? 'Copied!' : 'Booking link' }}
                        </span>
                    </button>
                </template>

                <div class="my-4 border-t border-sidebar-border lg:hidden" />
                <SidebarLink
                    class="lg:hidden"
                    href="/profile"
                    :active="page.url.startsWith('/profile')"
                    label="Profile"
                    icon="profile"
                    @click="closeSidebar"
                />
                <button type="button" class="sidebar-link lg:hidden" @click="logout">
                    <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Log out
                </button>
            </nav>
        </aside>

        <div class="app-main relative flex min-h-dvh min-w-0 flex-1 flex-col lg:pl-[264px]">
            <header
                ref="headerRef"
                :class="[
                    'app-topbar pt-[env(safe-area-inset-top)] transition-all duration-300',
                    headerSolid
                        ? 'border-b border-border/50 bg-background/95 shadow-sm backdrop-blur-xl'
                        : 'border-b border-transparent bg-transparent',
                ]"
            >
                <div class="flex min-h-14 w-full items-center gap-2 px-3 py-2.5 sm:min-h-16 sm:gap-3 sm:px-5 sm:py-3 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-2">
                        <button
                            type="button"
                            :class="[
                                'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl lg:hidden',
                                headerSolid ? 'bg-card text-foreground shadow-sm' : 'bg-white/70 text-foreground shadow-sm backdrop-blur',
                            ]"
                            aria-label="Open menu"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                        </button>

                        <div
                            class="min-w-0 transition-all duration-300"
                            :class="showCompactTitle ? 'opacity-100' : 'pointer-events-none absolute opacity-0'"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <Link :href="homeHref" class="hidden shrink-0 lg:inline-flex">
                                    <span class="brand-logo brand-logo-gradient text-[1.05rem]">
                                        Cut<span class="brand-logo-accent">cost</span>
                                    </span>
                                </Link>
                                <span v-if="title" class="hidden h-4 w-px shrink-0 bg-border/70 lg:block" />
                                <div class="min-w-0">
                                    <h1 v-if="title" class="page-title truncate">{{ title }}</h1>
                                    <p v-if="subtitle" class="page-sub hidden truncate sm:block">{{ subtitle }}</p>
                                </div>
                            </div>
                        </div>

                        <Link
                            v-if="!showCompactTitle"
                            :href="homeHref"
                            class="hidden min-w-0 lg:inline-flex"
                        >
                            <span class="brand-logo brand-logo-gradient text-[1.05rem]">
                                Cut<span class="brand-logo-accent">cost</span>
                            </span>
                        </Link>
                    </div>

                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <div v-if="$slots.actions" class="mr-0.5 hidden items-center gap-2 md:flex">
                            <slot name="actions" />
                        </div>

                        <div class="relative">
                            <button
                                type="button"
                                :class="[
                                    'relative inline-flex h-10 w-10 items-center justify-center rounded-xl transition-colors',
                                    headerSolid ? 'bg-card text-foreground shadow-sm hover:bg-muted/70' : 'bg-white/70 text-foreground shadow-sm backdrop-blur hover:bg-white/90',
                                ]"
                                aria-label="Notifications"
                                :aria-expanded="notificationsOpen"
                                @click.stop="toggleNotifications"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                </svg>
                                <span
                                    v-if="notifications.unread_count"
                                    class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold text-primary-foreground"
                                >
                                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                                </span>
                            </button>

                            <div
                                v-if="notificationsOpen"
                                class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1.5rem)] rounded-2xl bg-card py-1 shadow-card sm:max-w-none"
                            >
                                <div class="flex items-center justify-between px-4 py-3">
                                    <p class="text-sm font-semibold">Notifications</p>
                                    <span v-if="notifications.unread_count" class="text-xs text-muted-foreground">
                                        {{ notifications.unread_count }} new
                                    </span>
                                </div>
                                <div v-if="notifications.items?.length" class="max-h-72 overflow-y-auto overscroll-contain">
                                    <Link
                                        v-for="item in notifications.items"
                                        :key="item.id"
                                        :href="item.href"
                                        class="block px-4 py-3 transition-colors hover:bg-muted/50"
                                        :class="{ 'bg-primary/[0.04]': item.read === false }"
                                        @click="closeMenus"
                                    >
                                        <div class="flex items-start gap-2">
                                            <span
                                                v-if="item.read === false"
                                                class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"
                                                aria-hidden="true"
                                            />
                                            <div class="min-w-0 flex-1" :class="{ 'pl-3.5': item.read !== false && notifications.see_all_href }">
                                                <p class="truncate text-sm font-medium text-foreground">{{ item.title }}</p>
                                                <p class="mt-0.5 text-xs text-muted-foreground">{{ item.body }}</p>
                                                <p v-if="item.created_at_label" class="mt-1 text-[11px] text-muted-foreground/80">{{ item.created_at_label }}</p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="px-4 py-8 text-center">
                                    <p class="text-sm font-medium text-foreground">You’re all caught up</p>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{ notifications.see_all_href ? 'No notifications yet.' : 'No upcoming bookings for today.' }}
                                    </p>
                                </div>
                                <div v-if="notifications.see_all_href" class="border-t border-border/40 px-2 py-2">
                                    <Link
                                        :href="notifications.see_all_href"
                                        class="block rounded-xl px-3 py-2.5 text-center text-sm font-medium text-primary transition-colors hover:bg-muted/50"
                                        @click="closeMenus"
                                    >
                                        See all notifications
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <button
                                type="button"
                                :class="[
                                    'inline-flex items-center gap-2 rounded-full p-1 transition-colors sm:py-1 sm:pl-1 sm:pr-2.5',
                                    headerSolid ? 'bg-card shadow-sm hover:bg-muted/70' : 'bg-white/70 shadow-sm backdrop-blur hover:bg-white/90',
                                ]"
                                :aria-expanded="profileOpen"
                                @click.stop="toggleProfile"
                            >
                                <span class="avatar bg-primary text-primary-foreground">
                                    {{ user?.initials || user?.name?.charAt(0)?.toUpperCase() }}
                                </span>
                                <span class="hidden min-w-0 text-left sm:block">
                                    <span class="block max-w-[8rem] truncate text-sm font-medium leading-tight">{{ user?.name }}</span>
                                    <span class="block max-w-[8rem] truncate text-[11px] text-muted-foreground">{{ user?.shop_name || user?.email }}</span>
                                </span>
                            </button>

                            <div
                                v-if="profileOpen"
                                class="absolute right-0 mt-2 w-56 rounded-2xl bg-card py-1 shadow-card"
                            >
                                <div class="border-b border-border/40 px-3 py-3 sm:hidden">
                                    <p class="truncate text-sm font-medium">{{ user?.name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
                                </div>
                                <Link
                                    href="/profile"
                                    class="flex min-h-11 items-center gap-2.5 px-3 py-2.5 text-sm transition-colors hover:bg-muted/50"
                                    @click="closeMenus"
                                >
                                    Profile
                                </Link>
                                <Link
                                    v-if="user?.is_platform_admin"
                                    href="/admin/waitlist"
                                    class="flex min-h-11 items-center gap-2.5 px-3 py-2.5 text-sm transition-colors hover:bg-muted/50"
                                    @click="closeMenus"
                                >
                                    Waitlist
                                </Link>
                                <button
                                    type="button"
                                    class="flex min-h-11 w-full items-center gap-2.5 px-3 py-2.5 text-sm transition-colors hover:bg-muted/50"
                                    @click="logout"
                                >
                                    Log out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="$slots.actions" class="border-t border-border/40 px-3 py-2.5 md:hidden">
                    <div class="mobile-actions">
                        <slot name="actions" />
                    </div>
                </div>
            </header>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                <div
                    v-if="hero"
                    ref="heroRef"
                    class="dash-hero-band"
                    :style="{ paddingTop: `${headerHeight}px` }"
                >
                    <div class="dash-hero-copy">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-primary">
                            {{ greeting }}{{ user?.name ? `, ${user.name.split(' ')[0]}` : '' }}
                        </p>
                        <h1 class="mt-2 font-display text-[1.85rem] font-semibold tracking-tight text-foreground sm:text-4xl">
                            {{ title }}
                        </h1>
                        <p v-if="subtitle" class="mt-2 max-w-xl text-sm text-muted-foreground sm:text-[15px]">
                            {{ subtitle }}
                        </p>
                    </div>
                </div>
                <div v-else aria-hidden="true" :style="{ height: `${headerHeight}px` }" class="shrink-0" />

                <main class="min-w-0 flex-1 pb-[calc(4.25rem+env(safe-area-inset-bottom))] lg:pb-0">
                    <div v-if="flash" class="page-shell pb-0">
                        <div class="flash-ok">{{ flash }}</div>
                    </div>
                    <slot />
                </main>
            </div>

            <nav class="mobile-tabbar lg:hidden" aria-label="Primary">
                <Link
                    v-for="tab in mobileTabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="mobile-tab"
                    :class="{ 'mobile-tab-active': tabActive(tab) }"
                >
                    <svg v-if="tab.icon === 'home'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <svg v-else-if="tab.icon === 'bookings'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <svg v-else-if="tab.icon === 'clients'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <svg v-else-if="tab.icon === 'settings'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else-if="tab.icon === 'profile'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ tab.label }}</span>
                </Link>
                <button type="button" class="mobile-tab" aria-label="Open menu" @click="sidebarOpen = true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    <span>More</span>
                </button>
            </nav>
        </div>
    </div>
</template>
