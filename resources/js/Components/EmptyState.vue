<script setup>
defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="empty-state">
        <div class="empty-state-icon">
            <slot name="icon">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </slot>
        </div>
        <p v-if="title" class="font-display text-sm font-semibold text-foreground">{{ title }}</p>
        <p v-if="description" class="mt-1 max-w-xs text-[13px] leading-relaxed text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
