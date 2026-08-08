<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    business: { type: Object, required: true },
    presets: { type: Array, default: () => [] },
    defaultColor: { type: String, required: true },
    weekdays: { type: Array, default: () => [] },
    subscription: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash?.status);
const bookingCopied = ref(false);
const confirmCancel = ref(false);

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
    if (!/^[0-9A-Fa-f]{6}$/.test(raw)) return [226, 78, 55];
    const r = parseInt(raw.slice(0, 2), 16) / 255;
    const g = parseInt(raw.slice(2, 4), 16) / 255;
    const b = parseInt(raw.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const delta = max - min;
    let h = 0;
    const l = (max + min) / 2;
    if (delta < 0.00001) return [0, 0, Math.round(l * 100)];
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

function cloneOpeningHours(hours) {
    const source = hours && typeof hours === 'object' ? hours : {};
    const days = Array.isArray(props.weekdays) && props.weekdays.length
        ? props.weekdays.map((day) => day.value)
        : ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    return Object.fromEntries(days.map((day) => {
        const row = source[day] && typeof source[day] === 'object' && !Array.isArray(source[day])
            ? source[day]
            : {};
        return [day, {
            closed: !!row.closed,
            open: row.open ?? '09:00',
            close: row.close ?? '18:00',
        }];
    }));
}

const form = useForm({
    name: props.business.name ?? '',
    slug: props.business.slug ?? '',
    phone: props.business.phone ?? '',
    city: props.business.city ?? '',
    address: props.business.address ?? '',
    public_booking_enabled: !!props.business.public_booking_enabled,
    primary_color: props.business.primary_color || props.defaultColor,
    slot_interval_minutes: props.business.slot_interval_minutes ?? 15,
    booking_lead_minutes: props.business.booking_lead_minutes ?? 0,
    booking_horizon_days: props.business.booking_horizon_days ?? 60,
    // Avoid structuredClone(props.*) — Vue/Inertia proxies throw DataCloneError and blank the page.
    opening_hours: cloneOpeningHours(props.business.opening_hours),
});

const cancelForm = useForm({ confirm: false });

const selected = computed(() => (form.primary_color || props.defaultColor).toUpperCase());
const preview = computed(() => tokensFromHex(selected.value));
const bookingUrlPreview = computed(() => {
    try {
        const url = new URL(props.business.public_booking_url);
        const parts = url.pathname.split('/').filter(Boolean);
        parts[parts.length - 1] = form.slug || parts[parts.length - 1];
        url.pathname = `/${parts.join('/')}`;
        return url.toString();
    } catch {
        return props.business.public_booking_url;
    }
});

watch(preview, (tokens) => {
    const root = document.documentElement;
    Object.entries(tokenVarMap).forEach(([key, cssVar]) => {
        if (tokens[key]) root.style.setProperty(cssVar, tokens[key]);
    });
}, { immediate: true });

function pick(color) {
    form.primary_color = color.toUpperCase();
}

function save() {
    form.transform((data) => ({
        ...data,
        primary_color: data.primary_color === props.defaultColor && !props.business.primary_color
            ? data.primary_color
            : data.primary_color,
        public_booking_enabled: !!data.public_booking_enabled,
        slot_interval_minutes: Number(data.slot_interval_minutes),
        booking_lead_minutes: Number(data.booking_lead_minutes),
        booking_horizon_days: Number(data.booking_horizon_days),
    })).patch('/business/settings', { preserveScroll: true });
}

async function copyBookingLink() {
    await navigator.clipboard.writeText(bookingUrlPreview.value);
    bookingCopied.value = true;
    window.setTimeout(() => { bookingCopied.value = false; }, 2000);
}

function submitCancel() {
    cancelForm.confirm = true;
    cancelForm.post('/business/settings/subscription/cancel', {
        preserveScroll: true,
        onSuccess: () => { confirmCancel.value = false; },
    });
}
</script>

<template>
    <AppLayout title="Settings" subtitle="Shop details, booking, branding, and billing">
        <div class="page-shell max-w-3xl space-y-4">
            <p v-if="flash" class="flash-ok">{{ flash }}</p>

            <form class="space-y-4" @submit.prevent="save">
                <div class="card overflow-hidden">
                    <div class="border-b border-border/60 px-6 py-5">
                        <h2 class="card-title">Shop details</h2>
                        <p class="card-description">Shown to clients on your booking page and in the CRM.</p>
                    </div>
                    <div class="grid gap-4 p-6 sm:grid-cols-2">
                        <FormInput v-model="form.name" class="sm:col-span-2" label="Shop name" name="name" required :error="form.errors.name" />
                        <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                        <FormInput v-model="form.city" label="City" name="city" optional :error="form.errors.city" />
                        <FormInput v-model="form.address" class="sm:col-span-2" label="Address" name="address" optional :error="form.errors.address" />
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-border/60 px-6 py-5">
                        <h2 class="card-title">Booking link</h2>
                        <p class="card-description">Let clients book themselves with your private link.</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <label class="flex items-start gap-3">
                            <input v-model="form.public_booking_enabled" type="checkbox" class="mt-1 h-4 w-4 border-input text-primary focus:ring-primary">
                            <span>
                                <span class="block text-sm font-medium">Public booking enabled</span>
                                <span class="mt-0.5 block text-xs text-muted-foreground">When off, your booking link returns a 404.</span>
                            </span>
                        </label>
                        <FormInput v-model="form.slug" label="Link slug" name="slug" required :error="form.errors.slug" />
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <code class="block flex-1 break-all border border-border/70 bg-muted/60 px-3.5 py-2.5 text-xs text-muted-foreground">{{ bookingUrlPreview }}</code>
                            <button type="button" class="btn-secondary shrink-0" @click="copyBookingLink">
                                {{ bookingCopied ? 'Copied' : 'Copy' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-border/60 px-6 py-5">
                        <h2 class="card-title">Opening hours</h2>
                        <p class="card-description">Controls available times on your public booking page.</p>
                    </div>
                    <div class="divide-y divide-border/60">
                        <div
                            v-for="day in weekdays"
                            :key="day.value"
                            class="grid items-center gap-3 px-6 py-3 sm:grid-cols-[5rem_auto_1fr_auto_1fr]"
                        >
                            <p class="text-sm font-medium">{{ day.label }}</p>
                            <label class="flex items-center gap-2 text-xs text-muted-foreground">
                                <input v-model="form.opening_hours[day.value].closed" type="checkbox" class="h-4 w-4 border-input text-primary focus:ring-primary">
                                Closed
                            </label>
                            <input
                                v-model="form.opening_hours[day.value].open"
                                type="time"
                                class="form-input h-10"
                                :disabled="form.opening_hours[day.value].closed"
                            >
                            <span class="hidden text-xs text-muted-foreground sm:inline">to</span>
                            <input
                                v-model="form.opening_hours[day.value].close"
                                type="time"
                                class="form-input h-10"
                                :disabled="form.opening_hours[day.value].closed"
                            >
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-border/60 px-6 py-5">
                        <h2 class="card-title">Booking rules</h2>
                        <p class="card-description">How far ahead clients can book and how slots are spaced.</p>
                    </div>
                    <div class="grid gap-4 p-6 sm:grid-cols-3">
                        <div class="space-y-2">
                            <label for="slot_interval_minutes" class="text-sm font-medium">Slot interval</label>
                            <select id="slot_interval_minutes" v-model="form.slot_interval_minutes" class="form-select">
                                <option :value="5">Every 5 minutes</option>
                                <option :value="10">Every 10 minutes</option>
                                <option :value="15">Every 15 minutes</option>
                                <option :value="20">Every 20 minutes</option>
                                <option :value="30">Every 30 minutes</option>
                                <option :value="60">Every 60 minutes</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="booking_lead_minutes" class="text-sm font-medium">Minimum notice</label>
                            <select id="booking_lead_minutes" v-model="form.booking_lead_minutes" class="form-select">
                                <option :value="0">None</option>
                                <option :value="30">30 minutes</option>
                                <option :value="60">1 hour</option>
                                <option :value="120">2 hours</option>
                                <option :value="240">4 hours</option>
                                <option :value="1440">1 day</option>
                                <option :value="2880">2 days</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="booking_horizon_days" class="text-sm font-medium">Book up to</label>
                            <select id="booking_horizon_days" v-model="form.booking_horizon_days" class="form-select">
                                <option :value="14">14 days ahead</option>
                                <option :value="30">30 days ahead</option>
                                <option :value="60">60 days ahead</option>
                                <option :value="90">90 days ahead</option>
                                <option :value="180">180 days ahead</option>
                                <option :value="365">365 days ahead</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-border/60 px-6 py-5">
                        <h2 class="card-title">Brand colour</h2>
                        <p class="card-description">Sidebar, buttons, and booking page accents.</p>
                    </div>
                    <div class="space-y-4 p-6">
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="relative flex h-14 w-14 cursor-pointer items-center justify-center border border-border/70 bg-card shadow-sm">
                                <span class="absolute inset-1" :style="{ backgroundColor: selected }" />
                                <input v-model="form.primary_color" type="color" class="absolute inset-0 cursor-pointer opacity-0">
                            </label>
                            <div>
                                <p class="text-sm font-medium">Primary colour</p>
                                <p class="mt-0.5 font-mono text-xs uppercase text-muted-foreground">{{ selected }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in presets"
                                :key="color"
                                type="button"
                                class="h-9 w-9 border border-border/70"
                                :class="{ 'ring-2 ring-foreground ring-offset-2 ring-offset-background': selected === color.toUpperCase() }"
                                :style="{ backgroundColor: color }"
                                @click="pick(color)"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save settings' }}
                    </button>
                </div>
            </form>

            <div class="card overflow-hidden border-destructive/20">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Subscription</h2>
                    <p class="card-description">Your Cutcost plan billing.</p>
                </div>
                <div class="space-y-4 p-6">
                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-muted-foreground">Plan</dt>
                            <dd class="mt-0.5 font-medium">{{ subscription.plan || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Status</dt>
                            <dd class="mt-0.5 font-medium">{{ subscription.status_label }}</dd>
                        </div>
                    </dl>

                    <p v-if="subscription.cancel_at_label" class="rounded-none border border-warning/25 bg-warning/[0.06] px-4 py-3 text-sm text-foreground">
                        Cancellation scheduled. Access continues until {{ subscription.cancel_at_label }}.
                    </p>

                    <p v-if="cancelForm.errors.subscription" class="text-sm text-destructive">{{ cancelForm.errors.subscription }}</p>

                    <template v-if="subscription.can_cancel">
                        <div v-if="!confirmCancel">
                            <button type="button" class="btn-destructive" @click="confirmCancel = true">
                                Cancel subscription
                            </button>
                        </div>
                        <div v-else class="space-y-3 border border-destructive/20 bg-destructive/[0.04] p-4">
                            <p class="text-sm text-foreground">
                                Cancel at the end of your current billing period? You’ll keep access until then.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="btn-destructive" :disabled="cancelForm.processing" @click="submitCancel">
                                    {{ cancelForm.processing ? 'Cancelling…' : 'Yes, cancel' }}
                                </button>
                                <button type="button" class="btn-secondary" @click="confirmCancel = false">Keep plan</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
