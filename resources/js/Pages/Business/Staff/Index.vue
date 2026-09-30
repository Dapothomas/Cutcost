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
    barbers: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const pending = ref(null);
const searching = computed(() => Boolean(props.filters.search));

function confirmRemove() {
    const target = pending.value;
    pending.value = null;
    router.delete(`/business/staff/${target.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Stylists" subtitle="Your team, and who can take bookings">
        <template #actions>
            <Link href="/business/staff/create" class="btn-primary">
                <Icon name="plus" :size="16" :stroke-width="2.25" />
                Add stylist
            </Link>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="toolbar">
                    <SearchInput
                        url="/business/staff"
                        :model-value="filters.search"
                        placeholder="Search name, email or phone…"
                    />
                    <p class="result-count sm:ml-auto">
                        {{ barbers.total }} {{ barbers.total === 1 ? 'stylist' : 'stylists' }}
                    </p>
                </div>

                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(barber, index) in barbers.data" :key="barber.id">
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="avatar" :class="`avatar-tint-${index % 6}`">{{ barber.name?.charAt(0) }}</span>
                                        <span class="cell-primary truncate">{{ barber.name }}</span>
                                    </div>
                                </td>
                                <td class="cell-muted">{{ barber.email }}</td>
                                <td class="cell-muted">{{ barber.phone || '—' }}</td>
                                <td>
                                    <div class="row-actions">
                                        <RowActions>
                                            <button type="button" class="menu-item-danger" @click="pending = barber">
                                                <Icon name="trash" :size="15" />
                                                Remove from team
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!barbers.data.length">
                                <td colspan="4" class="p-0">
                                    <EmptyState
                                        v-if="searching"
                                        icon="search"
                                        title="No matching stylists"
                                        :description="`Nothing matches “${filters.search}”.`"
                                    />
                                    <EmptyState
                                        v-else
                                        icon="staff"
                                        title="No stylists yet"
                                        description="Invite your team so each of them can see their own schedule and manage their appointments."
                                    >
                                        <Link href="/business/staff/create" class="btn-primary">Add stylist</Link>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :links="barbers.links"
                :from="barbers.from"
                :to="barbers.to"
                :total="barbers.total"
            />
        </div>

        <ConfirmDialog
            :open="Boolean(pending)"
            title="Remove this stylist?"
            :description="pending ? `${pending.name} will lose access to the shop. Their past bookings stay on record.` : ''"
            confirm-label="Remove stylist"
            @cancel="pending = null"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
