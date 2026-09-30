<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    notifications: { type: Object, required: true },
});

function markAllRead() {
    router.post('/business/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Notifications" subtitle="Everything happening in your shop">
        <template #actions>
            <button type="button" class="btn-secondary" @click="markAllRead">
                <Icon name="check" :size="15" :stroke-width="2.25" />
                Mark all read
            </button>
        </template>

        <div class="page-shell">
            <div class="panel overflow-hidden">
                <div v-if="notifications.data?.length" class="divide-y divide-border/70">
                    <Link
                        v-for="item in notifications.data"
                        :key="item.id"
                        :href="item.href || `/business/notifications/${item.id}/read`"
                        class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-secondary/60 sm:px-5"
                        :class="item.read ? '' : 'bg-primary/[0.025]'"
                    >
                        <span
                            class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full"
                            :class="item.read ? 'bg-transparent' : 'bg-primary'"
                            aria-hidden="true"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
                                <p class="text-[13px] text-foreground" :class="item.read ? 'font-medium' : 'font-semibold'">
                                    {{ item.title }}
                                </p>
                                <p class="shrink-0 text-[11.5px] text-muted-foreground">{{ item.created_at_label }}</p>
                            </div>
                            <p v-if="item.body" class="mt-0.5 text-[13px] leading-relaxed text-muted-foreground">{{ item.body }}</p>
                        </div>
                    </Link>
                </div>
                <EmptyState
                    v-else
                    icon="bell"
                    title="No notifications yet"
                    description="New bookings, payments and shop updates will show up here."
                />
            </div>

            <Pagination
                :links="notifications.links"
                :from="notifications.from"
                :to="notifications.to"
                :total="notifications.total"
            />
        </div>
    </AppLayout>
</template>
