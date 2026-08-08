<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    clients: { type: Object, required: true },
});

function removeClient(id) {
    if (confirm('Remove this client?')) {
        router.delete(`/business/clients/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Clients" subtitle="Your shop's CRM contacts">
        <template #actions>
            <Link href="/business/clients/create" class="btn-primary">Add client</Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Bookings</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="client in clients.data" :key="client.id">
                            <td class="font-medium">{{ client.name }}</td>
                            <td>
                                {{ client.phone || '—' }}
                                <div v-if="client.email" class="text-xs text-muted-foreground">{{ client.email }}</div>
                            </td>
                            <td>{{ client.bookings_count }}</td>
                            <td class="space-x-3 text-right">
                                <Link :href="`/business/clients/${client.id}/edit`" class="btn-ghost">Edit</Link>
                                <button type="button" class="text-sm font-medium text-destructive hover:underline" @click="removeClient(client.id)">Remove</button>
                            </td>
                        </tr>
                        <tr v-if="!clients.data.length">
                            <td colspan="4">
                                <EmptyState title="No clients yet" description="Add your first regular.">
                                    <Link href="/business/clients/create" class="btn-primary">Add client</Link>
                                </EmptyState>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <Pagination :links="clients.links" />
            </div>
        </div>
    </AppLayout>
</template>
