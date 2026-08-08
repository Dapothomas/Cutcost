<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    barbers: { type: Object, required: true },
});

function removeBarber(id) {
    if (confirm('Remove this barber from your team?')) {
        router.delete(`/business/staff/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Staff" subtitle="Barbers on your team">
        <template #actions>
            <Link href="/business/staff/create" class="btn-primary">Add barber</Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="barber in barbers.data" :key="barber.id">
                            <td class="font-medium">{{ barber.name }}</td>
                            <td>{{ barber.email }}</td>
                            <td>{{ barber.phone || '—' }}</td>
                            <td class="text-right">
                                <button type="button" class="text-sm font-medium text-destructive hover:underline" @click="removeBarber(barber.id)">Remove</button>
                            </td>
                        </tr>
                        <tr v-if="!barbers.data.length">
                            <td colspan="4">
                                <EmptyState title="No barbers yet" description="Invite your team so they can see their schedule.">
                                    <Link href="/business/staff/create" class="btn-primary">Add barber</Link>
                                </EmptyState>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <Pagination :links="barbers.links" />
            </div>
        </div>
    </AppLayout>
</template>
