<script setup>
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, required: true },
    hint: { type: String, default: '' },
    /** Signed change vs the previous period, e.g. 12 or -4. */
    delta: { type: Number, default: null },
    deltaLabel: { type: String, default: '' },
    /** Makes the whole card a link so a KPI isn't a dead end. */
    href: { type: String, default: '' },
});

const direction = computed(() => {
    if (props.delta === null || props.delta === 0) return 'flat';
    return props.delta > 0 ? 'up' : 'down';
});

const deltaText = computed(() => {
    if (props.delta === null) return '';
    const sign = props.delta > 0 ? '+' : '';
    return `${sign}${props.delta}`;
});
</script>

<template>
    <component :is="href ? Link : 'div'" :href="href || undefined" class="stat-card group">
        <div class="flex items-start justify-between gap-3">
            <p class="stat-label">{{ label }}</p>
            <span class="stat-card-icon">
                <Icon :name="icon" :size="16" />
            </span>
        </div>

        <p class="stat-value mt-3 truncate">{{ value }}</p>

        <div v-if="delta !== null || hint" class="mt-2 flex items-center gap-1.5">
            <span v-if="delta !== null" class="stat-delta" :class="`stat-delta-${direction}`">
                <Icon v-if="direction !== 'flat'" :name="direction === 'up' ? 'arrow-up' : 'arrow-down'" :size="12" :stroke-width="2.5" />
                {{ deltaText }}
            </span>
            <span v-if="deltaLabel || hint" class="truncate text-[12px] text-muted-foreground">
                {{ deltaLabel || hint }}
            </span>
        </div>
    </component>
</template>
