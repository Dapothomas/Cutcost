<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    business: { type: Object, required: true },
    todaysBookings: { type: Array, default: () => [] },
    todayLabel: { type: String, required: true },
});

const subtitle = computed(() =>
    props.business.city ? `${props.business.city} · Overview` : 'Overview',
);

function copyBookingLink() {
    navigator.clipboard.writeText(props.business.public_booking_url);
}
</script>

<template>
    <AppLayout :title="business.name" :subtitle="subtitle">
        <template #actions>
            <a :href="business.public_booking_url" target="_blank" class="btn-secondary hidden sm:inline-flex">Booking link</a>
            <Link href="/business/bookings/create" class="btn-primary">New booking</Link>
        </template>

        <div class="page-shell">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Clients" :value="business.clients_count" icon="clients" />
                <StatCard label="Services" :value="business.services_count" icon="services" />
                <StatCard label="Barbers" :value="business.barbers_count" icon="staff" />
                <StatCard label="Bookings" :value="business.bookings_count" icon="bookings" />
            </div>

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
                            class="flex items-center justify-between gap-4 py-3"
                            :class="{ 'border-b': index < todaysBookings.length - 1 }"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-sm font-semibold">
                                    {{ booking.time }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ booking.client_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ booking.service_name }} · {{ booking.barber_name }}</p>
                                </div>
                            </div>
                            <span class="badge-outline">{{ booking.status }}</span>
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
                            <Link href="/business/clients/create" class="btn-secondary justify-start">Add client</Link>
                            <Link href="/business/services/create" class="btn-secondary justify-start">Add service</Link>
                            <Link href="/business/staff/create" class="btn-secondary justify-start">Add barber</Link>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Client booking link</h2>
                            <p class="card-description">Share so clients can book themselves</p>
                        </div>
                        <div class="card-content space-y-3">
                            <code class="block break-all rounded-md border bg-muted px-3 py-2 text-xs">{{ business.public_booking_url }}</code>
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
