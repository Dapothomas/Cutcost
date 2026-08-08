<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1.5">
        <Link
            v-for="(link, index) in links"
            :key="index"
            :href="link.url || '#'"
            class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium transition-all duration-150"
            :class="[
                link.active
                    ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/30'
                    : 'border border-border/70 bg-card text-muted-foreground hover:border-primary/40 hover:text-primary',
                !link.url ? 'pointer-events-none opacity-40' : '',
            ]"
            v-html="link.label"
        />
    </nav>
</template>
