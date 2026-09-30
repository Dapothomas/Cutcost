<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    bookings: { type: Object, required: true },
});

const page = usePage();
const statuses = computed(() => page.props.bookingStatuses ?? []);

function updateStatus(bookingId, status) {
    router.patch(`/barber/bookings/${bookingId}/status`, { status }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="My bookings" subtitle="Your full appointment list">
        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in bookings.data" :key="booking.id">
                                <td class="cell-primary whitespace-nowrap">{{ booking.starts_at_label }}</td>
                                <td>{{ booking.client_name }}</td>
                                <td class="cell-muted">{{ booking.service_name }}</td>
                                <td><StatusBadge :status="booking.status" /></td>
                                <td>
                                    <div class="row-actions">
                                        <button
                                            v-if="booking.status === 'scheduled'"
                                            type="button"
                                            class="btn-ghost"
                                            @click="updateStatus(booking.id, 'completed')"
                                        >
                                            <Icon name="check" :size="14" :stroke-width="2.25" />
                                            Complete
                                        </button>
                                        <RowActions>
                                            <p class="menu-label">Set status</p>
                                            <button
                                                v-for="status in statuses"
                                                :key="status.value"
                                                type="button"
                                                class="menu-item"
                                                :class="status.value === booking.status ? 'text-primary' : ''"
                                                @click="updateStatus(booking.id, status.value)"
                                            >
                                                <Icon
                                                    name="check"
                                                    :size="15"
                                                    :class="status.value === booking.status ? 'opacity-100' : 'opacity-0'"
                                                />
                                                {{ status.label }}
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!bookings.data.length">
                                <td colspan="5" class="p-0">
                                    <EmptyState
                                        icon="bookings"
                                        title="No bookings yet"
                                        description="Appointments assigned to you will show up here."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :links="bookings.links"
                :from="bookings.from"
                :to="bookings.to"
                :total="bookings.total"
            />
        </div>
    </AppLayout>
</template>
