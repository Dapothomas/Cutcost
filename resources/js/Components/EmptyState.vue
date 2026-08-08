<script setup>
defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="empty-state">
        <p v-if="title" class="text-sm font-medium">{{ title }}</p>
        <p v-if="description" class="mt-1 text-xs text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
