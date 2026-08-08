<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    notifications: { type: Object, required: true },
});

function markAllRead() {
    router.post('/business/notifications/read-all');
}
</script>

<template>
    <AppLayout title="Notifications" subtitle="Everything happening in your shop">
        <template #actions>
            <button type="button" class="btn-ghost" @click="markAllRead">Mark all read</button>
        </template>

        <div class="page-shell">
            <div class="panel divide-y divide-border/40">
                <template v-if="notifications.data?.length">
                    <Link
                        v-for="item in notifications.data"
                        :key="item.id"
                        :href="item.href || `/business/notifications/${item.id}/read`"
                        class="flex items-start gap-3 px-4 py-4 transition-colors hover:bg-muted/40 sm:px-5"
                    >
                        <span
                            class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                            :class="item.read ? 'bg-transparent' : 'bg-primary'"
                            aria-hidden="true"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                <p class="text-sm font-medium text-foreground" :class="{ 'font-semibold': !item.read }">
                                    {{ item.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">{{ item.created_at_label }}</p>
                            </div>
                            <p v-if="item.body" class="mt-0.5 text-sm text-muted-foreground">{{ item.body }}</p>
                        </div>
                    </Link>
                </template>
                <div v-else class="px-4 py-10">
                    <EmptyState title="No notifications yet" description="New bookings, payments, and shop updates will show up here." />
                </div>
            </div>

            <div v-if="notifications.links?.length > 3" class="mt-4">
                <Pagination :links="notifications.links" />
            </div>
        </div>
    </AppLayout>
</template>
