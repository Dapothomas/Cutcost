<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    bookings: { type: Object, required: true },
});

function updateStatus(bookingId, status) {
    router.patch(`/barber/bookings/${bookingId}/status`, { status }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="My bookings" subtitle="Your full appointment list">
        <div class="page-shell">
            <div class="panel overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td class="font-medium">{{ booking.starts_at_label }}</td>
                            <td>{{ booking.client_name }}</td>
                            <td>{{ booking.service_name }}</td>
                            <td>
                                <select
                                    class="form-input h-9 py-1"
                                    :value="booking.status"
                                    @change="updateStatus(booking.id, $event.target.value)"
                                >
                                    <option v-for="status in $page.props.bookingStatuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="text-right">
                                <button
                                    v-if="booking.status === 'scheduled'"
                                    type="button"
                                    class="btn-primary h-8 px-3 text-xs"
                                    @click="updateStatus(booking.id, 'completed')"
                                >
                                    Complete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!bookings.data.length">
                            <td colspan="5">
                                <EmptyState title="No bookings yet" description="Appointments assigned to you will appear here." />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <Pagination :links="bookings.links" />
            </div>
        </div>
    </AppLayout>
</template>
