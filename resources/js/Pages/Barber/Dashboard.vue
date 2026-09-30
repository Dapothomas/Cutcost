<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
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
            <div class="grid grid-cols-3 gap-2.5 sm:gap-4">
                <StatCard label="Today" :value="todaysBookings.length" icon="bookings" hint="On the book" />
                <StatCard label="Upcoming" :value="upcomingCount" icon="clock" href="/barber/bookings" hint="Ahead of today" />
                <StatCard label="Clients seen" :value="clientsSeen" icon="clients" hint="Completed" />
            </div>

            <div class="card">
                <div class="card-header-bordered flex-row items-center justify-between space-y-0">
                    <div>
                        <h2 class="card-title">Appointments</h2>
                        <p class="card-description">Your chair today</p>
                    </div>
                    <Link href="/barber/bookings" class="btn-ghost">All bookings</Link>
                </div>
                <div :class="todaysBookings.length ? 'divide-y divide-border/70' : ''">
                    <div
                        v-for="booking in todaysBookings"
                        :key="booking.id"
                        class="flex flex-col gap-2.5 px-4 py-3 transition-colors hover:bg-secondary/60 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-[3.25rem] shrink-0 items-center justify-center rounded-md bg-secondary text-[12.5px] font-semibold tabular-nums text-foreground">
                                {{ booking.time }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-foreground">{{ booking.client_name }}</p>
                                <p class="truncate text-[12px] text-muted-foreground">{{ booking.service_name }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <StatusBadge :status="booking.status" :label="booking.status_label" />
                            <button
                                v-if="booking.status === 'scheduled'"
                                type="button"
                                class="btn-secondary btn-sm"
                                @click="completeBooking(booking.id)"
                            >
                                <Icon name="check" :size="14" :stroke-width="2.25" />
                                Complete
                            </button>
                        </div>
                    </div>

                    <EmptyState
                        v-if="!todaysBookings.length"
                        icon="bookings"
                        title="Nothing on the book today"
                        description="Enjoy the quiet — or take a look at what's coming up."
                    >
                        <Link href="/barber/bookings" class="btn-secondary">View upcoming</Link>
                    </EmptyState>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
