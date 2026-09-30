<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import SearchInput from '@/Components/SearchInput.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    clients: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const pending = ref(null);
const searching = computed(() => Boolean(props.filters.search));

function confirmRemove() {
    const target = pending.value;
    pending.value = null;
    router.delete(`/business/clients/${target.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Clients" subtitle="Everyone who books with your shop">
        <template #actions>
            <Link href="/business/clients/create" class="btn-primary">
                <Icon name="plus" :size="16" :stroke-width="2.25" />
                Add client
            </Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="toolbar">
                    <SearchInput
                        url="/business/clients"
                        :model-value="filters.search"
                        placeholder="Search name, email or phone…"
                    />
                    <p class="result-count sm:ml-auto">
                        {{ clients.total }} {{ clients.total === 1 ? 'client' : 'clients' }}
                    </p>
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th class="text-right">Bookings</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(client, index) in clients.data" :key="client.id">
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="avatar" :class="`avatar-tint-${index % 6}`">{{ client.name?.charAt(0) }}</span>
                                        <Link
                                            :href="`/business/clients/${client.id}/edit`"
                                            class="cell-primary truncate transition-colors hover:text-primary"
                                        >
                                            {{ client.name }}
                                        </Link>
                                    </div>
                                </td>
                                <td class="cell-muted">{{ client.phone || '—' }}</td>
                                <td class="cell-muted">{{ client.email || '—' }}</td>
                                <td class="text-right">{{ client.bookings_count }}</td>
                                <td>
                                    <div class="row-actions">
                                        <RowActions>
                                            <Link :href="`/business/clients/${client.id}/edit`" class="menu-item">
                                                <Icon name="edit" :size="15" class="text-muted-foreground" />
                                                Edit client
                                            </Link>
                                            <div class="menu-separator" />
                                            <button type="button" class="menu-item-danger" @click="pending = client">
                                                <Icon name="trash" :size="15" />
                                                Remove
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!clients.data.length">
                                <td colspan="5" class="p-0">
                                    <EmptyState
                                        v-if="searching"
                                        icon="search"
                                        title="No matching clients"
                                        :description="`Nothing matches “${filters.search}”. Try a different name, email or phone number.`"
                                    />
                                    <EmptyState
                                        v-else
                                        icon="clients"
                                        title="No clients yet"
                                        description="Add your regulars so you can book them in faster and keep notes on each visit."
                                    >
                                        <Link href="/business/clients/create" class="btn-primary">Add client</Link>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :links="clients.links"
                :from="clients.from"
                :to="clients.to"
                :total="clients.total"
            />
        </div>

        <ConfirmDialog
            :open="Boolean(pending)"
            title="Remove this client?"
            :description="pending ? `${pending.name} will be removed from your client list. Their past bookings stay on record.` : ''"
            confirm-label="Remove client"
            @cancel="pending = null"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
