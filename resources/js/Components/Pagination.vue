<script setup>
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: { type: Array, default: () => [] },
    /** Laravel paginator meta, for the "1–15 of 120" summary. */
    from: { type: Number, default: null },
    to: { type: Number, default: null },
    total: { type: Number, default: null },
});

// Drop Laravel's "Previous"/"Next" entries; they get dedicated arrow buttons.
const numbered = computed(() => props.links.slice(1, -1));
const previous = computed(() => props.links[0] ?? null);
const next = computed(() => props.links[props.links.length - 1] ?? null);
const hasPages = computed(() => numbered.value.length > 1);
const showSummary = computed(() => props.total !== null && props.total > 0);
</script>

<template>
    <div v-if="hasPages || showSummary" class="flex flex-col-reverse items-center justify-between gap-3 sm:flex-row">
        <p v-if="showSummary" class="result-count">
            Showing <span class="font-medium text-foreground">{{ from }}–{{ to }}</span> of
            <span class="font-medium text-foreground">{{ total }}</span>
        </p>
        <span v-else />

        <nav v-if="hasPages" class="flex items-center gap-1" aria-label="Pagination">
            <Link
                :href="previous?.url || '#'"
                class="icon-btn icon-btn-sm border border-border bg-card"
                :class="!previous?.url ? 'pointer-events-none opacity-40' : ''"
                aria-label="Previous page"
            >
                <Icon name="chevron-down" :size="15" :stroke-width="2" class="rotate-90" />
            </Link>

            <Link
                v-for="(link, index) in numbered"
                :key="index"
                :href="link.url || '#'"
                class="inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-[13px] font-medium tabular-nums transition-colors"
                :class="[
                    link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-secondary hover:text-foreground',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
                :aria-current="link.active ? 'page' : undefined"
                v-html="link.label"
            />

            <Link
                :href="next?.url || '#'"
                class="icon-btn icon-btn-sm border border-border bg-card"
                :class="!next?.url ? 'pointer-events-none opacity-40' : ''"
                aria-label="Next page"
            >
                <Icon name="chevron-down" :size="15" :stroke-width="2" class="-rotate-90" />
            </Link>
        </nav>
    </div>
</template>
