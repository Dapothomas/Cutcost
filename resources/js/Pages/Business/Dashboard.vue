<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import EarningsPanel from '@/Components/EarningsPanel.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    business: { type: Object, required: true },
    todaysBookings: { type: Array, default: () => [] },
    todayLabel: { type: String, required: true },
    earningsPeriods: { type: Array, default: () => [] },
    earningsByPeriod: { type: Object, required: true },
});

const subtitle = computed(() => {
    const parts = [];
    if (props.business.city) parts.push(props.business.city);
    parts.push(props.todayLabel);
    return parts.join(' · ');
});

function copyBookingLink() {
    navigator.clipboard.writeText(props.business.public_booking_url);
}
</script>

<template>
    <AppLayout hero :title="business.name" :subtitle="subtitle">
        <div class="page-shell">
            <div
                v-if="!business.payments_ready && !business.payments_bypassed"
                class="flex flex-col gap-4 rounded-2xl bg-warning/[0.06] p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning/15 text-warning">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-foreground">Connect Stripe to accept client payments</p>
                        <p class="mt-0.5 text-[13px] text-muted-foreground">Clients can't pay online until your shop is connected. Booking links still work for free services.</p>
                    </div>
                </div>
                <Link href="/business/payments" class="btn-primary w-full shrink-0 sm:w-auto sm:self-center">Set up payments</Link>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                <StatCard label="Clients" :value="business.clients_count" icon="clients" />
                <StatCard label="Services" :value="business.services_count" icon="services" />
                <StatCard label="Stylists" :value="business.barbers_count" icon="staff" />
                <StatCard label="Bookings" :value="business.bookings_count" icon="bookings" />
            </div>

            <EarningsPanel :periods="earningsPeriods" :by-period="earningsByPeriod" />

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card lg:col-span-2">
                    <div class="card-header flex-row items-center justify-between space-y-0">
                        <div>
                            <h2 class="card-title">Today's appointments</h2>
                            <p class="card-description">{{ todayLabel }}</p>
                        </div>
                        <Link href="/business/bookings" class="btn-ghost">View all</Link>
                    </div>
                    <div class="card-content">
                        <div
                            v-for="(booking, index) in todaysBookings"
                            :key="index"
                            class="-mx-1 flex flex-col gap-2 rounded-xl px-2 py-3 transition-colors hover:bg-primary/[0.03] sm:-mx-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-3"
                            :class="{ 'border-b border-border/40': index < todaysBookings.length - 1 }"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-11 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-primary/[0.07] text-primary">
                                    <span class="text-[13px] font-bold leading-tight">{{ booking.time }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ booking.client_name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">{{ booking.service_name }} · {{ booking.barber_name }}</p>
                                </div>
                            </div>
                            <span class="badge-outline badge-dot w-fit shrink-0">{{ booking.status }}</span>
                        </div>

                        <EmptyState v-if="!todaysBookings.length" title="No appointments today" description="Book one or share your client link.">
                            <Link href="/business/bookings/create" class="btn-primary">Book appointment</Link>
                        </EmptyState>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Quick actions</h2>
                            <p class="card-description">Common tasks for your shop</p>
                        </div>
                        <div class="card-content grid gap-2">
                            <Link href="/business/bookings/create" class="btn-primary justify-start">New booking</Link>
                            <Link href="/business/clients/create" class="btn-secondary justify-start">Add client</Link>
                            <Link href="/business/services/create" class="btn-secondary justify-start">Add service</Link>
                            <Link href="/business/staff/create" class="btn-secondary justify-start">Add stylist</Link>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Client booking link</h2>
                            <p class="card-description">Share so clients can book themselves</p>
                        </div>
                        <div class="card-content space-y-3">
                            <code class="block break-all rounded-xl bg-muted/60 px-3.5 py-2.5 text-xs leading-relaxed text-muted-foreground">{{ business.public_booking_url }}</code>
                            <div class="flex gap-2">
                                <a :href="business.public_booking_url" target="_blank" class="btn-secondary flex-1 justify-center">Open</a>
                                <button type="button" class="btn-primary flex-1" @click="copyBookingLink">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
