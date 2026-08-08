<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    business: { type: Object, required: true },
    presets: { type: Array, default: () => [] },
    defaultColor: { type: String, required: true },
});

const tokenVarMap = {
    primary: '--primary',
    primary_deep: '--primary-deep',
    ring: '--ring',
    accent: '--accent',
    accent_foreground: '--accent-foreground',
    background: '--background',
    secondary: '--secondary',
    secondary_foreground: '--secondary-foreground',
    muted: '--muted',
    muted_foreground: '--muted-foreground',
    border: '--border',
    input: '--input',
    sidebar_background: '--sidebar-background',
    sidebar_foreground: '--sidebar-foreground',
    sidebar_border: '--sidebar-border',
    sidebar_accent: '--sidebar-accent',
};

function hexToHsl(hex) {
    const raw = String(hex || '').replace('#', '');
    if (!/^[0-9A-Fa-f]{6}$/.test(raw)) {
        return [226, 78, 55];
    }

    const r = parseInt(raw.slice(0, 2), 16) / 255;
    const g = parseInt(raw.slice(2, 4), 16) / 255;
    const b = parseInt(raw.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const delta = max - min;
    let h = 0;
    const l = (max + min) / 2;

    if (delta < 0.00001) {
        return [0, 0, Math.round(l * 100)];
    }

    const s = delta / (1 - Math.abs(2 * l - 1));
    if (max === r) h = ((g - b) / delta) % 6;
    else if (max === g) h = (b - r) / delta + 2;
    else h = (r - g) / delta + 4;

    h = Math.round(h * 60);
    if (h < 0) h += 360;

    return [h, Math.round(s * 100), Math.round(l * 100)];
}

function tokensFromHex(hex) {
    const [h, s, l] = hexToHsl(hex);
    const neutralS = Math.max(8, Math.min(28, Math.round(s * 0.35)));
    const sidebarS = Math.max(18, Math.min(40, Math.round(s * 0.55)));
    const hsl = (hh, ss, ll) => `${hh} ${Math.max(0, Math.min(100, ss))}% ${Math.max(0, Math.min(100, ll))}%`;

    return {
        primary: hsl(h, s, l),
        primary_deep: hsl(h, Math.min(82, s + 4), Math.max(28, l - 14)),
        ring: hsl(h, s, l),
        accent: hsl(h, Math.min(90, s + 6), Math.min(96, Math.max(92, l + 42))),
        accent_foreground: hsl(h, Math.min(78, s), Math.max(32, l - 10)),
        background: hsl(h, neutralS, 97),
        secondary: hsl(h, neutralS, 94),
        secondary_foreground: hsl(h, Math.min(30, s), 12),
        muted: hsl(h, neutralS, 94),
        muted_foreground: hsl(h, Math.max(8, Math.round(neutralS * 0.7)), 42),
        border: hsl(h, neutralS, 89),
        input: hsl(h, neutralS, 86),
        sidebar_background: hsl(h, sidebarS, 9),
        sidebar_foreground: hsl(h, Math.max(12, Math.round(sidebarS * 0.55)), 72),
        sidebar_border: hsl(h, sidebarS, 16),
        sidebar_accent: hsl(h, sidebarS, 14),
    };
}

const form = useForm({
    primary_color: props.business.primary_color || props.defaultColor,
});

const selected = computed(() => (form.primary_color || props.defaultColor).toUpperCase());
const isDefault = computed(() => !props.business.primary_color);
const preview = computed(() => tokensFromHex(selected.value));

watch(
    () => props.business.primary_color,
    (value) => {
        form.primary_color = value || props.defaultColor;
    },
);

watch(
    preview,
    (tokens) => {
        const root = document.documentElement;
        Object.entries(tokenVarMap).forEach(([key, cssVar]) => {
            if (tokens[key]) {
                root.style.setProperty(cssVar, tokens[key]);
            }
        });
    },
    { immediate: true },
);

function pick(color) {
    form.primary_color = color.toUpperCase();
}

function resetDefault() {
    router.patch('/business/settings', { primary_color: null }, { preserveScroll: true });
}

function save() {
    form.patch('/business/settings', { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Settings" subtitle="Customise how your CRM looks">
        <div class="page-shell max-w-2xl space-y-4">
            <div class="card overflow-hidden">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Brand colour</h2>
                    <p class="card-description">
                        Sets a matching palette for {{ business.name }} — sidebar, buttons, page background, and booking page.
                    </p>
                </div>

                <form class="space-y-6 p-6" @submit.prevent="save">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="relative flex h-14 w-14 cursor-pointer items-center justify-center border border-border/70 bg-card shadow-sm">
                            <span
                                class="absolute inset-1"
                                :style="{ backgroundColor: selected }"
                            />
                            <input
                                v-model="form.primary_color"
                                type="color"
                                class="absolute inset-0 cursor-pointer opacity-0"
                            >
                        </label>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">Primary colour</p>
                            <p class="mt-0.5 font-mono text-xs uppercase text-muted-foreground">{{ selected }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-medium text-foreground">Presets</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in presets"
                                :key="color"
                                type="button"
                                class="h-9 w-9 border border-border/70 transition-transform hover:scale-105"
                                :class="{ 'ring-2 ring-foreground ring-offset-2 ring-offset-background': selected === color.toUpperCase() }"
                                :style="{ backgroundColor: color }"
                                :aria-label="`Use ${color}`"
                                @click="pick(color)"
                            />
                        </div>
                    </div>

                    <div class="overflow-hidden border border-border/70">
                        <p class="border-b border-border/60 px-4 py-2 text-xs font-medium uppercase tracking-[0.12em] text-muted-foreground">
                            Preview
                        </p>
                        <div class="flex min-h-[140px]">
                            <div
                                class="flex w-28 shrink-0 flex-col gap-1.5 p-3"
                                :style="{ backgroundColor: `hsl(${preview.sidebar_background})` }"
                            >
                                <div
                                    class="h-2 w-12"
                                    :style="{ backgroundColor: `hsl(${preview.sidebar_foreground} / 0.35)` }"
                                />
                                <div
                                    class="mt-2 px-2 py-1.5 text-[10px] font-semibold text-white"
                                    :style="{
                                        backgroundImage: `linear-gradient(135deg, hsl(${preview.primary}), hsl(${preview.primary_deep}))`,
                                    }"
                                >
                                    Dashboard
                                </div>
                                <div
                                    class="px-2 py-1.5 text-[10px]"
                                    :style="{ color: `hsl(${preview.sidebar_foreground})` }"
                                >
                                    Clients
                                </div>
                                <div
                                    class="px-2 py-1.5 text-[10px]"
                                    :style="{ color: `hsl(${preview.sidebar_foreground})` }"
                                >
                                    Bookings
                                </div>
                            </div>
                            <div
                                class="flex flex-1 flex-col justify-center gap-3 p-4"
                                :style="{ backgroundColor: `hsl(${preview.background})` }"
                            >
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-fit items-center px-3 text-xs font-semibold text-white"
                                    :style="{
                                        backgroundImage: `linear-gradient(180deg, hsl(${preview.primary}), hsl(${preview.primary_deep}))`,
                                    }"
                                >
                                    Primary button
                                </button>
                                <span
                                    class="inline-flex w-fit items-center border px-2 py-1 text-[11px] font-medium"
                                    :style="{
                                        color: `hsl(${preview.accent_foreground})`,
                                        borderColor: `hsl(${preview.primary} / 0.25)`,
                                        backgroundColor: `hsl(${preview.accent})`,
                                    }"
                                >
                                    Status badge
                                </span>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.primary_color" class="text-sm text-destructive">{{ form.errors.primary_color }}</p>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Saving…' : 'Save colour' }}
                        </button>
                        <button
                            type="button"
                            class="btn-secondary"
                            :disabled="form.processing || isDefault"
                            @click="resetDefault"
                        >
                            Reset to default
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
