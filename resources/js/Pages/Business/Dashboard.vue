<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EarningsPanel from '@/Components/EarningsPanel.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    business: { type: Object, required: true },
    todaysBookings: { type: Array, default: () => [] },
    todayLabel: { type: String, required: true },
    earningsPeriods: { type: Array, default: () => [] },
    earningsByPeriod: { type: Object, required: true },
});

const linkCopied = ref(false);

const subtitle = computed(() => {
    const parts = [];
    if (props.business.city) parts.push(props.business.city);
    parts.push(props.todayLabel);
    return parts.join(' · ');
});

const quickActions = [
    { href: '/business/bookings/create', label: 'New booking', icon: 'bookings' },
    { href: '/business/clients/create', label: 'Add client', icon: 'clients' },
    { href: '/business/services/create', label: 'Add service', icon: 'services' },
    { href: '/business/staff/create', label: 'Add stylist', icon: 'staff' },
];

async function copyBookingLink() {
    await navigator.clipboard.writeText(props.business.public_booking_url);
    linkCopied.value = true;
    window.setTimeout(() => {
        linkCopied.value = false;
    }, 2000);
}
</script>

<template>
    <AppLayout hero :title="business.name" :subtitle="subtitle">
        <template #actions>
            <Link href="/business/bookings/create" class="btn-primary">
                <Icon name="plus" :size="16" :stroke-width="2.25" />
                New booking
            </Link>
        </template>

        <div class="page-shell">
            <div
                v-if="!business.payments_ready && !business.payments_bypassed"
                class="flex flex-col gap-3.5 rounded-2xl border border-warning/25 bg-warning/[0.05] p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4"
            >
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning">
                        <Icon name="payments" :size="17" />
                    </span>
                    <div>
                        <p class="text-[13px] font-semibold text-foreground">Connect Stripe to accept client payments</p>
                        <p class="mt-0.5 text-[13px] leading-relaxed text-muted-foreground">
                            Clients can't pay online until your shop is connected. Booking links still work for free services.
                        </p>
                    </div>
                </div>
                <Link href="/business/payments" class="btn-secondary w-full shrink-0 justify-center sm:w-auto">
                    Set up payments
                </Link>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard
                    label="Clients"
                    :value="business.clients_count"
                    icon="clients"
                    href="/business/clients"
                    hint="In your CRM"
                />
                <StatCard
                    label="Services"
                    :value="business.services_count"
                    icon="services"
                    href="/business/services"
                    hint="Bookable"
                />
                <StatCard
                    label="Stylists"
                    :value="business.barbers_count"
                    icon="staff"
                    href="/business/staff"
                    hint="Taking bookings"
                />
                <StatCard
                    label="Bookings"
                    :value="business.bookings_count"
                    icon="bookings"
                    href="/business/bookings"
                    hint="All time"
                />
            </div>

            <EarningsPanel :periods="earningsPeriods" :by-period="earningsByPeriod" />

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card lg:col-span-2">
                    <div class="card-header-bordered flex-row items-center justify-between space-y-0">
                        <div>
                            <h2 class="card-title">Today's appointments</h2>
                            <p class="card-description">{{ todayLabel }}</p>
                        </div>
                        <Link href="/business/bookings" class="btn-ghost">View all</Link>
                    </div>
                    <div :class="todaysBookings.length ? 'divide-y divide-border/70' : ''">
                        <Link
                            v-for="(booking, index) in todaysBookings"
                            :key="index"
                            href="/business/bookings"
                            class="flex items-center justify-between gap-3 px-4 py-2.5 transition-colors hover:bg-secondary/60 sm:px-5 sm:py-3"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-[3.25rem] shrink-0 items-center justify-center rounded-md bg-secondary text-[12.5px] font-semibold tabular-nums text-foreground">
                                    {{ booking.time }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-medium text-foreground">{{ booking.client_name }}</p>
                                    <p class="truncate text-[12px] text-muted-foreground">
                                        {{ booking.service_name }} · {{ booking.barber_name }}
                                    </p>
                                </div>
                            </div>
                            <StatusBadge :status="booking.status" class="shrink-0" />
                        </Link>

                        <EmptyState
                            v-if="!todaysBookings.length"
                            icon="bookings"
                            title="No appointments today"
                            description="Book one in, or share your client link so they can book themselves."
                        >
                            <Link href="/business/bookings/create" class="btn-primary">Book appointment</Link>
                        </EmptyState>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Quick actions</h2>
                        </div>
                        <div class="card-content grid gap-1">
                            <Link
                                v-for="action in quickActions"
                                :key="action.href"
                                :href="action.href"
                                class="quick-action"
                            >
                                <span class="quick-action-icon">
                                    <Icon :name="action.icon" :size="15" />
                                </span>
                                {{ action.label }}
                            </Link>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Client booking link</h2>
                            <p class="card-description">Share so clients can book themselves</p>
                        </div>
                        <div class="card-content space-y-2.5">
                            <code class="block break-all rounded-lg border border-border bg-secondary/70 px-3 py-2 text-[12px] leading-relaxed text-muted-foreground">
                                {{ business.public_booking_url }}
                            </code>
                            <div class="flex gap-2">
                                <a
                                    :href="business.public_booking_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-secondary flex-1 justify-center"
                                >
                                    <Icon name="external" :size="15" />
                                    Open
                                </a>
                                <button type="button" class="btn-primary flex-1 justify-center" @click="copyBookingLink">
                                    <Icon :name="linkCopied ? 'check' : 'copy'" :size="15" />
                                    {{ linkCopied ? 'Copied' : 'Copy' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
