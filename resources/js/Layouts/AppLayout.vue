<script setup>
import Icon from '@/Components/Icon.vue';
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
const bandRef = ref(null);
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
// The big title lives in the page band; it only collapses into the topbar
// once you've scrolled past it.
const showCompactTitle = computed(() => scrolled.value);

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

function onKeydown(event) {
    if (event.key !== 'Escape') return;
    closeMenus();
    closeSidebar();
}

function measureHeader() {
    if (headerRef.value) {
        headerHeight.value = headerRef.value.offsetHeight;
    }
}

function onScroll() {
    if (!bandRef.value) {
        scrolled.value = window.scrollY > 24;
        return;
    }

    scrolled.value = bandRef.value.getBoundingClientRect().bottom <= headerHeight.value + 8;
}

let removeListener;
let headerObserver;
onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
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
    document.removeEventListener('keydown', onKeydown);
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
                'app-sidebar fixed inset-y-0 left-0 z-50 flex w-[min(17rem,86vw)] flex-col pt-[env(safe-area-inset-top)] transition-transform duration-300 ease-out lg:w-[248px] lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-14 items-center justify-between px-4 sm:h-16">
                <Link :href="homeHref" class="group" aria-label="Cutcost home" @click="closeSidebar">
                    <img
                        src="/images/logo-on-dark.png"
                        alt=""
                        width="2499"
                        height="615"
                        class="brand-mark brand-mark-sm transition-opacity group-hover:opacity-90"
                    />
                </Link>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-sidebar-foreground transition-colors hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="Close menu"
                    @click="closeSidebar"
                >
                    <Icon name="close" :size="18" :stroke-width="2" />
                </button>
            </div>

            <div v-if="user?.shop_name" class="sidebar-shop-card">
                <span class="avatar h-7 w-7 bg-primary/20 text-[10px] text-white">
                    {{ user.shop_name.charAt(0) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-medium leading-tight text-white">{{ user.shop_name }}</p>
                    <p class="text-[11px] leading-tight text-sidebar-foreground/60">{{ isOwner ? 'Owner' : 'Stylist' }}</p>
                </div>
            </div>

            <nav class="flex-1 space-y-0.5 overflow-y-auto overscroll-contain px-2.5 py-2 pb-[calc(1rem+env(safe-area-inset-bottom))]">
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
                    <div class="my-3 border-t border-sidebar-border" />
                    <p class="sidebar-section-label">Share</p>
                    <button type="button" class="sidebar-link min-h-10" @click="copyBookingLink">
                        <Icon :name="bookingLinkCopied ? 'check' : 'link'" :size="17" />
                        <span :class="bookingLinkCopied ? 'text-emerald-400' : ''">
                            {{ bookingLinkCopied ? 'Copied' : 'Booking link' }}
                        </span>
                    </button>
                </template>

                <div class="my-3 border-t border-sidebar-border lg:hidden" />
                <SidebarLink
                    class="lg:hidden"
                    href="/profile"
                    :active="page.url.startsWith('/profile')"
                    label="Profile"
                    icon="profile"
                    @click="closeSidebar"
                />
                <button type="button" class="sidebar-link min-h-10 lg:hidden" @click="logout">
                    <Icon name="logout" :size="17" />
                    Log out
                </button>
            </nav>
        </aside>

        <div class="app-main relative flex min-h-dvh min-w-0 flex-1 flex-col lg:pl-[248px]">
            <header
                ref="headerRef"
                :class="[
                    'app-topbar pt-[env(safe-area-inset-top)] transition-colors duration-200',
                    headerSolid
                        ? 'border-b border-border bg-card/90 backdrop-blur-xl'
                        : 'border-b border-transparent bg-transparent',
                ]"
            >
                <div class="mx-auto flex min-h-14 w-full max-w-7xl items-center gap-2 px-3 py-2 sm:min-h-16 sm:gap-3 sm:px-5 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-2">
                        <button
                            type="button"
                            class="icon-btn border border-border bg-card lg:hidden"
                            aria-label="Open menu"
                            @click="sidebarOpen = true"
                        >
                            <Icon name="menu" :size="18" :stroke-width="2" />
                        </button>

                        <Link :href="homeHref" class="hidden shrink-0 lg:inline-flex" aria-label="Cutcost home">
                            <img src="/images/logo.png" alt="" width="2499" height="615" class="brand-mark brand-mark-sm" />
                        </Link>

                        <!-- Collapsed page title: appears once the band scrolls away. -->
                        <div
                            v-if="title"
                            class="min-w-0 items-center gap-2.5 transition-opacity duration-200"
                            :class="showCompactTitle ? 'flex opacity-100' : 'pointer-events-none hidden opacity-0 lg:flex'"
                        >
                            <span class="hidden h-4 w-px shrink-0 bg-border lg:block" />
                            <h2 class="page-title truncate">{{ title }}</h2>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-1 sm:gap-1.5">
                        <div class="relative">
                            <button
                                type="button"
                                class="icon-btn relative border border-border bg-card"
                                :class="notificationsOpen ? 'bg-secondary text-foreground' : ''"
                                aria-label="Notifications"
                                :aria-expanded="notificationsOpen"
                                @click.stop="toggleNotifications"
                            >
                                <Icon name="bell" :size="18" />
                                <span
                                    v-if="notifications.unread_count"
                                    class="absolute -right-0.5 -top-0.5 flex h-[17px] min-w-[17px] items-center justify-center rounded-full border-2 border-card bg-primary px-1 text-[9px] font-semibold leading-none text-primary-foreground"
                                >
                                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                                </span>
                            </button>

                            <div
                                v-if="notificationsOpen"
                                class="menu absolute right-0 top-full mt-1.5 w-[20rem] max-w-[calc(100vw-1.5rem)] p-0"
                            >
                                <div class="flex items-center justify-between border-b border-border px-3.5 py-2.5">
                                    <p class="text-[13px] font-semibold">Notifications</p>
                                    <span v-if="notifications.unread_count" class="badge-outline">
                                        {{ notifications.unread_count }} new
                                    </span>
                                </div>
                                <div v-if="notifications.items?.length" class="max-h-[19rem] overflow-y-auto overscroll-contain">
                                    <Link
                                        v-for="item in notifications.items"
                                        :key="item.id"
                                        :href="item.href"
                                        class="flex items-start gap-2.5 border-b border-border/60 px-3.5 py-2.5 transition-colors last:border-0 hover:bg-secondary"
                                        @click="closeMenus"
                                    >
                                        <span
                                            class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"
                                            :class="item.read === false ? 'bg-primary' : 'bg-transparent'"
                                            aria-hidden="true"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[13px] font-medium text-foreground">{{ item.title }}</p>
                                            <p class="mt-0.5 text-[12px] leading-snug text-muted-foreground">{{ item.body }}</p>
                                            <p v-if="item.created_at_label" class="mt-1 text-[11px] text-muted-foreground/80">{{ item.created_at_label }}</p>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="px-4 py-8 text-center">
                                    <p class="text-[13px] font-medium text-foreground">You’re all caught up</p>
                                    <p class="mt-1 text-[12px] text-muted-foreground">
                                        {{ notifications.see_all_href ? 'No notifications yet.' : 'No upcoming bookings for today.' }}
                                    </p>
                                </div>
                                <div v-if="notifications.see_all_href" class="border-t border-border p-1">
                                    <Link
                                        :href="notifications.see_all_href"
                                        class="menu-item justify-center text-primary"
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
                                class="inline-flex items-center gap-2 rounded-lg border border-border bg-card p-1 transition-colors hover:bg-secondary sm:pr-2.5"
                                :class="profileOpen ? 'bg-secondary' : ''"
                                aria-label="Account menu"
                                :aria-expanded="profileOpen"
                                @click.stop="toggleProfile"
                            >
                                <span class="avatar bg-primary text-primary-foreground">
                                    {{ user?.initials || user?.name?.charAt(0)?.toUpperCase() }}
                                </span>
                                <span class="hidden min-w-0 text-left sm:block">
                                    <span class="block max-w-[8rem] truncate text-[13px] font-medium leading-tight">{{ user?.name }}</span>
                                    <span class="block max-w-[8rem] truncate text-[11px] leading-tight text-muted-foreground">{{ user?.shop_name || user?.email }}</span>
                                </span>
                                <Icon name="chevron-down" :size="14" class="hidden text-muted-foreground sm:block" />
                            </button>

                            <div v-if="profileOpen" class="menu absolute right-0 top-full mt-1.5 w-56">
                                <div class="border-b border-border px-2.5 pb-2 pt-1 sm:hidden">
                                    <p class="truncate text-[13px] font-medium">{{ user?.name }}</p>
                                    <p class="truncate text-[12px] text-muted-foreground">{{ user?.email }}</p>
                                </div>
                                <Link href="/profile" class="menu-item" @click="closeMenus">
                                    <Icon name="profile" :size="16" class="text-muted-foreground" />
                                    Profile
                                </Link>
                                <Link
                                    v-if="user?.is_platform_admin"
                                    href="/admin/waitlist"
                                    class="menu-item"
                                    @click="closeMenus"
                                >
                                    <Icon name="clients" :size="16" class="text-muted-foreground" />
                                    Waitlist
                                </Link>
                                <div class="menu-separator" />
                                <button type="button" class="menu-item" @click="logout">
                                    <Icon name="logout" :size="16" class="text-muted-foreground" />
                                    Log out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                <div
                    v-if="title"
                    ref="bandRef"
                    :class="hero ? 'dash-hero-band' : 'page-band'"
                    :style="{ paddingTop: `${headerHeight}px` }"
                >
                    <div class="page-band-copy">
                        <div class="min-w-0">
                            <p v-if="hero" class="page-band-eyebrow">
                                {{ greeting }}{{ user?.name ? `, ${user.name.split(' ')[0]}` : '' }}
                            </p>
                            <h1 class="page-band-title" :class="hero ? 'mt-1.5' : ''">{{ title }}</h1>
                            <p v-if="subtitle" class="page-band-sub">{{ subtitle }}</p>
                        </div>
                        <div v-if="$slots.actions" class="page-band-actions">
                            <slot name="actions" />
                        </div>
                    </div>
                </div>
                <div v-else aria-hidden="true" :style="{ height: `${headerHeight}px` }" class="shrink-0" />

                <main
                    class="min-w-0 flex-1 lg:pb-0"
                    :class="$slots.actions
                        ? 'pb-[calc(8.5rem+env(safe-area-inset-bottom))]'
                        : 'pb-[calc(4.25rem+env(safe-area-inset-bottom))]'"
                >
                    <div v-if="flash" class="page-shell pb-0">
                        <div class="flash-ok">{{ flash }}</div>
                    </div>
                    <slot />
                </main>
            </div>

            <div class="mobile-bottom lg:hidden">
                <div v-if="$slots.actions" class="mobile-actionbar">
                    <div class="mobile-actions">
                        <slot name="actions" />
                    </div>
                </div>
                <nav class="mobile-tabbar" aria-label="Primary">
                    <Link
                        v-for="tab in mobileTabs"
                        :key="tab.href"
                        :href="tab.href"
                        class="mobile-tab"
                        :class="{ 'mobile-tab-active': tabActive(tab) }"
                        :aria-current="tabActive(tab) ? 'page' : undefined"
                    >
                        <Icon :name="tab.icon" :size="19" />
                        <span>{{ tab.label }}</span>
                    </Link>
                    <button type="button" class="mobile-tab" aria-label="Open menu" @click="sidebarOpen = true">
                        <Icon name="menu" :size="19" />
                        <span>More</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
