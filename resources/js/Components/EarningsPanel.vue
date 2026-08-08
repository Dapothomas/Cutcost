<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    periods: { type: Array, default: () => [] },
    byPeriod: { type: Object, required: true },
    defaultPeriod: { type: String, default: 'month' },
});

const period = ref(props.byPeriod[props.defaultPeriod] ? props.defaultPeriod : 'month');
const selectedIndex = ref(null);

const palette = [
    'hsl(226 78% 55%)',
    'hsl(152 60% 36%)',
    'hsl(36 92% 46%)',
    'hsl(280 45% 48%)',
    'hsl(190 65% 38%)',
    'hsl(355 72% 52%)',
];

const active = computed(() => props.byPeriod[period.value] ?? props.byPeriod.month);
const summary = computed(() => active.value?.summary ?? {});
const series = computed(() => active.value?.series ?? []);
const breakdown = computed(() => active.value?.breakdown ?? []);

const maxCents = computed(() => Math.max(...series.value.map((p) => p.amount_cents), 0));
const hasRevenue = computed(() => (summary.value.amount_cents ?? 0) > 0);
const dense = computed(() => series.value.length > 10);

const chartMinWidth = computed(() => {
    if (!dense.value) return null;
    // Keep bars readable on phones: ~28px each + gap
    return `${Math.max(series.value.length * 1.75, 18)}rem`;
});

const selectedPoint = computed(() => {
    if (selectedIndex.value === null) return null;
    return series.value[selectedIndex.value] ?? null;
});

const labelStep = computed(() => {
    const n = series.value.length;
    if (n <= 8) return 1;
    if (n <= 14) return 2;
    if (n <= 21) return 3;
    return 5;
});

function showLabel(index) {
    const last = series.value.length - 1;
    return index === 0 || index === last || index % labelStep.value === 0;
}

function selectBar(index) {
    selectedIndex.value = selectedIndex.value === index ? null : index;
}

watch(period, () => {
    selectedIndex.value = null;
});

const donut = computed(() => {
    const size = 132;
    const stroke = 14;
    const radius = (size - stroke) / 2;
    const circumference = 2 * Math.PI * radius;
    let offset = 0;

    const segments = breakdown.value.map((item, index) => {
        const length = (item.percent / 100) * circumference;
        const segment = {
            ...item,
            color: palette[index % palette.length],
            dasharray: `${length} ${circumference - length}`,
            dashoffset: -offset,
        };
        offset += length;
        return segment;
    });

    return { size, stroke, radius, circumference, segments };
});
</script>

<template>
    <div class="card overflow-hidden">
        <div class="card-header flex-row flex-wrap items-center justify-between gap-3 space-y-0">
            <div class="min-w-0">
                <h2 class="card-title">Earnings</h2>
                <p class="card-description truncate">Paid client bookings · {{ summary.label }}</p>
            </div>
            <div class="flex w-full items-center gap-2 sm:w-auto">
                <select v-model="period" class="form-select h-10 min-w-0 flex-1 py-1 text-xs font-medium sm:h-9 sm:w-auto sm:min-w-[9.5rem] sm:flex-none">
                    <option v-for="option in periods" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <Link href="/business/payments" class="btn-ghost shrink-0">Details</Link>
            </div>
        </div>

        <div class="card-content">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="font-display text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                        {{ summary.amount_label }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ summary.paid_bookings_count }}
                        paid booking{{ summary.paid_bookings_count === 1 ? '' : 's' }}
                    </p>
                </div>
                <p
                    v-if="selectedPoint"
                    class="rounded-xl bg-muted/70 px-3 py-2 text-right text-xs sm:hidden"
                >
                    <span class="font-medium text-foreground">{{ selectedPoint.label }}</span>
                    <span class="mt-0.5 block font-semibold text-primary">{{ selectedPoint.amount_label }}</span>
                </p>
            </div>

            <div class="mt-5 grid gap-6 font-display lg:mt-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:items-center lg:gap-8">
                <div class="min-w-0">
                    <div
                        class="-mx-1 overflow-x-auto overscroll-x-contain px-1 pb-1 [scrollbar-width:thin] max-sm:touch-pan-x"
                    >
                        <div
                            class="flex h-36 items-end gap-1 sm:h-40 sm:gap-1.5"
                            :class="{ 'max-sm:min-w-[var(--chart-min)]': dense }"
                            :style="dense && chartMinWidth ? { '--chart-min': chartMinWidth } : undefined"
                        >
                            <button
                                v-for="(point, index) in series"
                                :key="`${point.label}-${index}`"
                                type="button"
                                class="group relative flex h-full min-w-0 flex-1 flex-col items-center justify-end rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/40 max-sm:min-w-[1.5rem]"
                                :aria-label="`${point.label}: ${point.amount_label}`"
                                @click="selectBar(index)"
                            >
                                <div
                                    class="pointer-events-none absolute bottom-full z-10 mb-1 hidden whitespace-nowrap rounded-md bg-ink-950 px-2 py-1 text-[10px] font-medium text-white sm:group-hover:block"
                                    :class="{ 'sm:!block': selectedIndex === index }"
                                >
                                    {{ point.amount_label }}
                                </div>
                                <div
                                    class="w-full max-w-[2.25rem] rounded-t-sm bg-primary/15 transition-[height,colors,opacity]"
                                    :class="[
                                        point.amount_cents > 0 ? 'bg-primary/55 group-hover:bg-primary/70' : '',
                                        selectedIndex === index ? '!bg-primary opacity-100' : '',
                                        selectedIndex !== null && selectedIndex !== index ? 'opacity-45' : '',
                                    ]"
                                    :style="{
                                        height: maxCents > 0
                                            ? `${Math.max((point.amount_cents / maxCents) * 100, point.amount_cents > 0 ? 8 : 2)}%`
                                            : '2%',
                                    }"
                                />
                            </button>
                        </div>

                        <div
                            class="mt-2 flex gap-1 sm:gap-1.5"
                            :class="{ 'max-sm:min-w-[var(--chart-min)]': dense }"
                            :style="dense && chartMinWidth ? { '--chart-min': chartMinWidth } : undefined"
                        >
                            <span
                                v-for="(point, index) in series"
                                :key="`label-${point.label}-${index}`"
                                class="min-w-0 flex-1 text-center text-[10px] font-medium text-muted-foreground max-sm:min-w-[1.5rem]"
                                :class="{ invisible: !showLabel(index) }"
                            >
                                {{ point.label }}
                            </span>
                        </div>
                    </div>
                    <p v-if="dense" class="mt-1.5 text-[11px] text-muted-foreground sm:hidden">
                        Swipe to see more · tap a bar for the amount
                    </p>
                </div>

                <div class="flex flex-col items-center gap-4 border-t border-border/40 pt-5 sm:flex-row sm:items-center sm:pt-0 lg:flex-col lg:items-center lg:border-t-0 lg:pt-0">
                    <div class="relative shrink-0" :style="{ width: `${donut.size}px`, height: `${donut.size}px` }">
                        <svg :viewBox="`0 0 ${donut.size} ${donut.size}`" class="h-full w-full -rotate-90">
                            <circle
                                :cx="donut.size / 2"
                                :cy="donut.size / 2"
                                :r="donut.radius"
                                fill="none"
                                class="stroke-muted"
                                :stroke-width="donut.stroke"
                            />
                            <circle
                                v-for="(segment, index) in donut.segments"
                                :key="index"
                                :cx="donut.size / 2"
                                :cy="donut.size / 2"
                                :r="donut.radius"
                                fill="none"
                                :stroke="segment.color"
                                :stroke-width="donut.stroke"
                                :stroke-dasharray="segment.dasharray"
                                :stroke-dashoffset="segment.dashoffset"
                                stroke-linecap="butt"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <p class="text-[10px] font-medium uppercase tracking-[0.12em] text-muted-foreground">Share</p>
                            <p class="text-lg font-semibold text-foreground">
                                {{ hasRevenue ? `${breakdown[0]?.percent ?? 0}%` : '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="w-full min-w-0 space-y-2 text-sm">
                        <template v-if="hasRevenue">
                            <div
                                v-for="(item, index) in breakdown"
                                :key="item.label"
                                class="flex items-center justify-between gap-3"
                            >
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        class="h-2.5 w-2.5 shrink-0"
                                        :style="{ backgroundColor: palette[index % palette.length] }"
                                    />
                                    <span class="truncate font-medium text-muted-foreground">{{ item.label }}</span>
                                </div>
                                <span class="shrink-0 font-semibold text-foreground">{{ item.amount_label }}</span>
                            </div>
                        </template>
                        <p v-else class="text-center text-muted-foreground sm:text-left lg:text-center">
                            No paid bookings in this period yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
