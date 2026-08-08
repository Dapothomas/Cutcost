<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, required: true },
    hint: { type: String, default: '' },
});

const tints = {
    clients: 'bg-blue-500/[0.09] text-blue-600',
    services: 'bg-violet-500/[0.09] text-violet-600',
    staff: 'bg-amber-500/[0.1] text-amber-600',
    bookings: 'bg-primary/[0.09] text-primary',
    revenue: 'bg-emerald-500/[0.09] text-emerald-600',
};

const tint = computed(() => tints[props.icon] ?? 'bg-primary/[0.09] text-primary');
</script>

<template>
    <div class="stat-card">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="stat-label">{{ label }}</p>
                <p class="stat-value mt-2.5 truncate">{{ value }}</p>
                <p v-if="hint" class="mt-1.5 text-xs text-muted-foreground">{{ hint }}</p>
            </div>
            <div class="stat-card-icon" :class="tint">
                <svg v-if="icon === 'clients'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <svg v-else-if="icon === 'services'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" x2="8.12" y1="4" y2="15.88"/><line x1="14.47" x2="20" y1="14.48" y2="20"/><line x1="8.12" x2="12" y1="8.12" y2="12"/></svg>
                <svg v-else-if="icon === 'staff'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                <svg v-else-if="icon === 'bookings'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <svg v-else-if="icon === 'revenue'" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
        </div>
    </div>
</template>
