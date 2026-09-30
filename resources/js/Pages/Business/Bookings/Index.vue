<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import SearchInput from '@/Components/SearchInput.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    bookings: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const pending = ref(null);
const statuses = computed(() => page.props.bookingStatuses ?? []);
const filtered = computed(() => Boolean(props.filters.search || props.filters.status));

function setStatusFilter(status) {
    const query = {};
    if (props.filters.search) query.search = props.filters.search;
    if (status) query.status = status;

    router.get('/business/bookings', query, { preserveState: true, preserveScroll: true, replace: true });
}

function updateStatus(bookingId, status) {
    router.patch(`/business/bookings/${bookingId}/status`, { status }, { preserveScroll: true });
}

function confirmRemove() {
    const target = pending.value;
    pending.value = null;
    router.delete(`/business/bookings/${target.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Bookings" subtitle="Every appointment across your shop">
        <template #actions>
            <Link href="/business/bookings/create" class="btn-primary">
                <Icon name="plus" :size="16" :stroke-width="2.25" />
                Book appointment
            </Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="toolbar">
                    <SearchInput
                        url="/business/bookings"
                        :model-value="filters.search"
                        :params="filters.status ? { status: filters.status } : {}"
                        placeholder="Search client, service or stylist…"
                    />

                    <select
                        class="form-select h-9 w-full text-[13px] sm:w-auto"
                        :value="filters.status || ''"
                        aria-label="Filter by status"
                        @change="setStatusFilter($event.target.value)"
                    >
                        <option value="">All statuses</option>
                        <option v-for="status in statuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>

                    <p class="result-count sm:ml-auto">
                        {{ bookings.total }} {{ bookings.total === 1 ? 'booking' : 'bookings' }}
                    </p>
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Stylist</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in bookings.data" :key="booking.id">
                                <td class="cell-primary whitespace-nowrap">{{ booking.starts_at_label }}</td>
                                <td>{{ booking.client_name }}</td>
                                <td class="cell-muted">{{ booking.service_name }}</td>
                                <td class="cell-muted">{{ booking.barber_name }}</td>
                                <td class="text-right">{{ booking.amount_label }}</td>
                                <td><StatusBadge :status="booking.status" /></td>
                                <td>
                                    <div class="row-actions">
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
                                            <div class="menu-separator" />
                                            <button type="button" class="menu-item-danger" @click="pending = booking">
                                                <Icon name="trash" :size="15" />
                                                Delete booking
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!bookings.data.length">
                                <td colspan="7" class="p-0">
                                    <EmptyState
                                        v-if="filtered"
                                        icon="search"
                                        title="No matching bookings"
                                        description="Try a different search term or clear the status filter."
                                    >
                                        <Link href="/business/bookings" class="btn-secondary">Clear filters</Link>
                                    </EmptyState>
                                    <EmptyState
                                        v-else
                                        icon="bookings"
                                        title="No bookings yet"
                                        description="Create your first appointment, or share your booking link so clients can book themselves."
                                    >
                                        <Link href="/business/bookings/create" class="btn-primary">Book appointment</Link>
                                    </EmptyState>
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

        <ConfirmDialog
            :open="Boolean(pending)"
            title="Delete this appointment?"
            :description="pending ? `${pending.client_name}'s ${pending.service_name} on ${pending.starts_at_label} will be deleted. This can't be undone.` : ''"
            confirm-label="Delete booking"
            @cancel="pending = null"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
