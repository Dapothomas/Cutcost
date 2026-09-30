<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    signups: { type: Object, required: true },
    total: { type: Number, required: true },
});

const pending = ref(null);

function confirmRemove() {
    const target = pending.value;
    pending.value = null;
    router.delete(`/admin/waitlist/${target.id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Waitlist" :subtitle="`${total} signup${total === 1 ? '' : 's'} from the landing page`">
        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Shop</th>
                                <th>Source</th>
                                <th>Joined</th>
                                <th class="w-12"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(signup, index) in signups.data" :key="signup.id">
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="avatar" :class="`avatar-tint-${index % 6}`">
                                            {{ (signup.name || signup.email)?.charAt(0)?.toUpperCase() }}
                                        </span>
                                        <a :href="`mailto:${signup.email}`" class="cell-primary truncate hover:text-primary hover:underline">
                                            {{ signup.email }}
                                        </a>
                                    </div>
                                </td>
                                <td class="cell-muted">{{ signup.name || '—' }}</td>
                                <td class="cell-muted">{{ signup.shop_name || '—' }}</td>
                                <td><span class="badge-muted">{{ signup.source || 'waitlist' }}</span></td>
                                <td class="cell-muted whitespace-nowrap">{{ signup.created_at_label }}</td>
                                <td>
                                    <div class="row-actions">
                                        <RowActions>
                                            <a :href="`mailto:${signup.email}`" class="menu-item">
                                                <Icon name="external" :size="15" class="text-muted-foreground" />
                                                Email
                                            </a>
                                            <div class="menu-separator" />
                                            <button type="button" class="menu-item-danger" @click="pending = signup">
                                                <Icon name="trash" :size="15" />
                                                Remove
                                            </button>
                                        </RowActions>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!signups.data.length">
                                <td colspan="6" class="p-0">
                                    <EmptyState
                                        icon="inbox"
                                        title="No waitlist signups yet"
                                        description="New joins from the landing page will show up here."
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination
                :links="signups.links"
                :from="signups.from"
                :to="signups.to"
                :total="signups.total"
            />
        </div>

        <ConfirmDialog
            :open="Boolean(pending)"
            title="Remove from waitlist?"
            :description="pending ? `${pending.email} will be removed from the waitlist.` : ''"
            confirm-label="Remove"
            @cancel="pending = null"
            @confirm="confirmRemove"
        />
    </AppLayout>
</template>
