<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    bookings: { type: Object, required: true },
});

function updateStatus(bookingId, status) {
    router.patch(`/business/bookings/${bookingId}/status`, { status }, { preserveScroll: true });
}

function removeBooking(id) {
    if (confirm('Delete this appointment?')) {
        router.delete(`/business/bookings/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Bookings" subtitle="Appointments across your shop">
        <template #actions>
            <Link href="/business/bookings/create" class="btn-primary">Book appointment</Link>
        </template>

        <div class="page-shell">
            <div class="panel table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Stylist</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings.data" :key="booking.id">
                            <td class="font-medium">{{ booking.starts_at_label }}</td>
                            <td>{{ booking.client_name }}</td>
                            <td>{{ booking.service_name }}</td>
                            <td>{{ booking.barber_name }}</td>
                            <td>
                                <select
                                    class="form-select h-9 w-auto rounded-lg py-1 pr-8 text-xs"
                                    :value="booking.status"
                                    @change="updateStatus(booking.id, $event.target.value)"
                                >
                                    <option v-for="status in $page.props.bookingStatuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn-ghost text-destructive hover:bg-destructive/[0.06] hover:text-destructive" @click="removeBooking(booking.id)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!bookings.data.length">
                            <td colspan="6">
                                <EmptyState title="No bookings yet" description="Create your first appointment.">
                                    <Link href="/business/bookings/create" class="btn-primary">Book appointment</Link>
                                </EmptyState>
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
