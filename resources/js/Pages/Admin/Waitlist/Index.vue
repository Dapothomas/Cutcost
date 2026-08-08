<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { router } from '@inertiajs/vue3';

defineProps({
    signups: { type: Object, required: true },
    total: { type: Number, required: true },
});

function removeSignup(id, email) {
    if (confirm(`Remove ${email} from the waitlist?`)) {
        router.delete(`/admin/waitlist/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Waitlist" :subtitle="`${total} signup${total === 1 ? '' : 's'}`">
        <div class="page-shell">
            <div class="panel table-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Shop</th>
                            <th>Source</th>
                            <th>Joined</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(signup, index) in signups.data" :key="signup.id">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="avatar" :class="`avatar-tint-${index % 6}`">
                                        {{ (signup.name || signup.email)?.charAt(0)?.toUpperCase() }}
                                    </span>
                                    <a :href="`mailto:${signup.email}`" class="font-medium text-primary hover:underline">
                                        {{ signup.email }}
                                    </a>
                                </div>
                            </td>
                            <td>{{ signup.name || '—' }}</td>
                            <td>{{ signup.shop_name || '—' }}</td>
                            <td>
                                <span class="badge-muted">{{ signup.source || 'waitlist' }}</span>
                            </td>
                            <td class="text-muted-foreground">{{ signup.created_at_label }}</td>
                            <td class="text-right">
                                <button
                                    type="button"
                                    class="btn-ghost text-destructive hover:bg-destructive/[0.06] hover:text-destructive"
                                    @click="removeSignup(signup.id, signup.email)"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!signups.data.length">
                            <td colspan="6">
                                <EmptyState
                                    title="No waitlist signups yet"
                                    description="New joins from the landing page will show up here."
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="signups.links?.length > 3" class="mt-4">
                <Pagination :links="signups.links" />
            </div>
        </div>
    </AppLayout>
</template>
