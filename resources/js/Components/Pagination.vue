<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <Link
            v-for="(link, index) in links"
            :key="index"
            :href="link.url || '#'"
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm transition-colors"
            :class="[
                link.active ? 'border-primary bg-primary text-primary-foreground' : 'bg-background hover:bg-muted',
                !link.url ? 'pointer-events-none opacity-50' : '',
            ]"
            v-html="link.label"
        />
    </nav>
</template>
