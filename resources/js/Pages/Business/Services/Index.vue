<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    services: { type: Object, required: true },
});

function removeService(id) {
    if (confirm('Remove this service?')) {
        router.delete(`/business/services/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Services" subtitle="What you offer and how long it takes">
        <template #actions>
            <Link href="/business/services/create" class="btn-primary">Add service</Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="service in services.data" :key="service.id">
                            <td class="font-medium">{{ service.name }}</td>
                            <td class="text-muted-foreground">{{ service.duration_minutes }} min</td>
                            <td class="font-semibold">{{ service.price_label }}</td>
                            <td>
                                <span :class="service.is_active ? 'badge-success badge-dot' : 'badge-muted badge-dot'">
                                    {{ service.is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td class="space-x-1 text-right">
                                <Link :href="`/business/services/${service.id}/edit`" class="btn-ghost">Edit</Link>
                                <button type="button" class="btn-ghost text-destructive hover:bg-destructive/[0.06] hover:text-destructive" @click="removeService(service.id)">Remove</button>
                            </td>
                        </tr>
                        <tr v-if="!services.data.length">
                            <td colspan="5">
                                <EmptyState title="No services yet" description="Add cuts, colours, or treatments.">
                                    <Link href="/business/services/create" class="btn-primary">Add service</Link>
                                </EmptyState>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <Pagination :links="services.links" />
            </div>
        </div>
    </AppLayout>
</template>
