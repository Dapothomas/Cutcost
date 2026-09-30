<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import SearchInput from '@/Components/SearchInput.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    services: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const pending = ref(null);
const searching = computed(() => Boolean(props.filters.search));

function confirmRemove() {
    const target = pending.value;
    pending.value = null;
    router.delete(`/business/services/${target.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Services" subtitle="What you offer, how long it takes, and what it costs">
        <template #actions>
            <Link href="/business/services/create" class="btn-primary">
                <Icon name="plus" :size="16" :stroke-width="2.25" />
                Add service
            </Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="toolbar">
                    <SearchInput
                        url="/business/services"
                        :model-value="filters.search"
                        placeholder="Search services…"
                    />
                    <p class="result-count sm:ml-auto">
                        {{ services.total }} {{ services.total === 1 ? 'service' : 'services' }}
                    </p>
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th class="text-right">Duration</th>
                                <th class="text-right">Price</th>
                                <th>Status</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="service in services.data" :key="service.id">
                                <td>
                                    <Link
                                        :href="`/business/services/${service.id}/edit`"
                                        class="cell-primary transition-colors hover:text-primary"
                                    >
                                        {{ service.name }}
                                    </Link>
                                </td>
                                <td class="cell-muted text-right">{{ service.duration_minutes }} min</td>
                                <td class="text-right font-medium">{{ service.price_label }}</td>
                                <td><StatusBadge :status="service.is_active ? 'active' : 'hidden'" /></td>
                                <td>
                                    <div class="row-actions">
                                        <RowActions>
                                            <Link :href="`/business/services/${service.id}/edit`" class="menu-item">
                                                <Icon name="edit" :size="15" class="text-muted-foreground" />
                                                Edit service
                                            </Link>
                                            <div class="menu-separator" />
                                            <button type="button" class="menu-item-danger" @click="pending = service">
                                                <Icon name="trash" :size="15" />
                                                Remove
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!services.data.length">
                                <td colspan="5" class="p-0">
                                    <EmptyState
                                        v-if="searching"
                                        icon="search"
                                        title="No matching services"
                                        :description="`Nothing matches “${filters.search}”.`"
                                    />
                                    <EmptyState
                                        v-else
                                        icon="services"
                                        title="No services yet"
                                        description="Add the cuts, colours and treatments you offer so clients know what they can book."
                                    >
                                        <Link href="/business/services/create" class="btn-primary">Add service</Link>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :links="services.links"
                :from="services.from"
                :to="services.to"
                :total="services.total"
            />
        </div>

        <ConfirmDialog
            :open="Boolean(pending)"
            title="Remove this service?"
            :description="pending ? `${pending.name} will no longer be bookable. Past bookings keep their record.` : ''"
            confirm-label="Remove service"
            @cancel="pending = null"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
