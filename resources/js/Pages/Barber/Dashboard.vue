<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    businessName: { type: String, default: 'Your shop' },
    todayLabel: { type: String, required: true },
    todaysBookings: { type: Array, default: () => [] },
    upcomingCount: { type: Number, default: 0 },
    clientsSeen: { type: Number, default: 0 },
});

function completeBooking(id) {
    router.patch(`/barber/bookings/${id}/status`, { status: 'completed' }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Today's schedule" :subtitle="`${businessName} · ${todayLabel}`">
        <template #actions>
            <Link href="/barber/bookings" class="btn-secondary">All bookings</Link>
        </template>

        <div class="page-shell">
            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard label="Today" :value="todaysBookings.length" icon="bookings" />
                <StatCard label="Upcoming" :value="upcomingCount" icon="bookings" />
                <StatCard label="Clients seen" :value="clientsSeen" icon="clients" />
            </div>

            <div class="card">
                <div class="card-header flex-row items-center justify-between space-y-0">
                    <div>
                        <h2 class="card-title">Appointments</h2>
                        <p class="card-description">Your chair today</p>
                    </div>
                    <Link href="/barber/bookings" class="btn-ghost">All bookings</Link>
                </div>
                <div class="card-content">
                    <div
                        v-for="(booking, index) in todaysBookings"
                        :key="booking.id"
                        class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between"
                        :class="{ 'border-b': index < todaysBookings.length - 1 }"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-muted text-sm font-semibold">
                                {{ booking.time }}
                            </div>
                            <div>
                                <p class="text-sm font-medium">{{ booking.client_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ booking.service_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge-outline">{{ booking.status_label }}</span>
                            <button
                                v-if="booking.status === 'scheduled'"
                                type="button"
                                class="btn-primary h-8 px-3 text-xs"
                                @click="completeBooking(booking.id)"
                            >
                                Complete
                            </button>
                        </div>
                    </div>

                    <EmptyState v-if="!todaysBookings.length" title="Nothing on the book today" description="Enjoy the quiet — or check upcoming bookings." />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
