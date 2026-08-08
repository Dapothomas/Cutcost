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
    <AppLayout
        hero
        title="Today's schedule"
        :subtitle="`${businessName} · ${todayLabel}`"
    >
        <div class="page-shell">
            <div class="grid grid-cols-3 gap-2 sm:gap-4">
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
                        class="-mx-3 flex flex-col gap-3 rounded-xl px-3 py-3 transition-colors hover:bg-primary/[0.03] sm:flex-row sm:items-center sm:justify-between"
                        :class="{ 'border-b border-border/40': index < todaysBookings.length - 1 }"
                    >
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-11 w-14 shrink-0 items-center justify-center rounded-xl bg-primary/[0.07] text-[13px] font-bold text-primary">
                                {{ booking.time }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground">{{ booking.client_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ booking.service_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge-outline badge-dot">{{ booking.status_label }}</span>
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
