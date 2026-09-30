<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    icon: { type: String, default: 'inbox' },
});
</script>

<template>
    <div class="empty-state">
        <div class="empty-state-icon">
            <slot name="icon">
                <Icon :name="icon" :size="19" />
            </slot>
        </div>
        <p v-if="title" class="font-display text-[14px] font-semibold text-foreground">{{ title }}</p>
        <p v-if="description" class="mt-1 max-w-sm text-[13px] leading-relaxed text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
