<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    periods: { type: Array, default: () => [] },
    byPeriod: { type: Object, required: true },
    defaultPeriod: { type: String, default: 'month' },
});

const period = ref(props.byPeriod[props.defaultPeriod] ? props.defaultPeriod : 'month');

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

const donut = computed(() => {
    const size = 148;
    const stroke = 16;
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
    <div class="card">
        <div class="card-header flex-row flex-wrap items-center justify-between gap-3 space-y-0">
            <div>
                <h2 class="card-title">Earnings</h2>
                <p class="card-description">Paid client bookings · {{ summary.label }}</p>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="period" class="form-select h-9 w-auto min-w-[9.5rem] py-1 text-xs font-medium">
                    <option v-for="option in periods" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <Link href="/business/payments" class="btn-ghost">Details</Link>
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
            </div>

            <div class="mt-6 grid gap-8 font-display lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:items-center">
                <div>
                    <div class="flex h-40 items-end gap-1.5 sm:gap-2">
                        <div
                            v-for="(point, index) in series"
                            :key="`${point.label}-${index}`"
                            class="group relative flex h-full min-w-0 flex-1 flex-col items-center justify-end"
                        >
                            <div
                                class="absolute bottom-full z-10 mb-1 hidden whitespace-nowrap bg-ink-950 px-2 py-1 text-[10px] font-medium text-white group-hover:block"
                            >
                                {{ point.amount_label }}
                            </div>
                            <div
                                class="w-full max-w-[2.25rem] bg-primary/15 transition-[height,colors] group-hover:bg-primary/30"
                                :class="{ 'bg-primary/55 group-hover:bg-primary/70': point.amount_cents > 0 }"
                                :style="{
                                    height: maxCents > 0
                                        ? `${Math.max((point.amount_cents / maxCents) * 100, point.amount_cents > 0 ? 6 : 2)}%`
                                        : '2%',
                                }"
                            />
                        </div>
                    </div>
                    <div class="mt-2 flex gap-1.5 sm:gap-2">
                        <span
                            v-for="(point, index) in series"
                            :key="`label-${point.label}-${index}`"
                            class="min-w-0 flex-1 truncate text-center text-[10px] font-medium text-muted-foreground"
                        >
                            {{ point.label }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start lg:flex-col lg:items-center">
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
