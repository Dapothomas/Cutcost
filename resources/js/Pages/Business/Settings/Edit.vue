<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import FormInput from '@/Components/FormInput.vue';
import Icon from '@/Components/Icon.vue';
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
    if (!/^[0-9A-Fa-f]{6}$/.test(raw)) return [226, 70, 55];
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

// Lightness steps here mirror the base palette in app.css, so a custom brand
// colour produces the same surface contrast the default theme has.
function tokensFromHex(hex) {
    const [h, s, l] = hexToHsl(hex);
    const neutralS = Math.max(6, Math.min(20, Math.round(s * 0.25)));
    const sidebarS = Math.max(18, Math.min(38, Math.round(s * 0.5)));
    const hsl = (hh, ss, ll) => `${hh} ${Math.max(0, Math.min(100, ss))}% ${Math.max(0, Math.min(100, ll))}%`;
    return {
        primary: hsl(h, s, l),
        primary_deep: hsl(h, Math.min(80, s + 4), Math.max(28, l - 12)),
        ring: hsl(h, s, l),
        accent: hsl(h, Math.min(80, s + 4), 97),
        accent_foreground: hsl(h, Math.min(72, s), Math.max(32, l - 10)),
        background: hsl(h, neutralS, 98),
        secondary: hsl(h, neutralS, 96),
        secondary_foreground: hsl(h, Math.min(26, s), 13),
        muted: hsl(h, neutralS, 96),
        muted_foreground: hsl(h, Math.max(7, Math.round(neutralS * 0.6)), 46),
        border: hsl(h, neutralS, 91),
        input: hsl(h, neutralS, 88),
        sidebar_background: hsl(h, sidebarS, 10),
        sidebar_foreground: hsl(h, Math.max(11, Math.round(sidebarS * 0.45)), 68),
        sidebar_border: hsl(h, sidebarS, 18),
        sidebar_accent: hsl(h, sidebarS, 15),
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
                    <div class="card-header-bordered">
                        <h2 class="card-title">Shop details</h2>
                        <p class="card-description">Shown to clients on your booking page and in the CRM.</p>
                    </div>
                    <div class="grid gap-4 px-4 py-4 sm:grid-cols-2 sm:px-5 sm:py-5">
                        <FormInput v-model="form.name" class="sm:col-span-2" label="Shop name" name="name" required :error="form.errors.name" />
                        <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                        <FormInput v-model="form.city" label="City" name="city" optional :error="form.errors.city" />
                        <FormInput v-model="form.address" class="sm:col-span-2" label="Address" name="address" optional :error="form.errors.address" />
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-header-bordered">
                        <h2 class="card-title">Booking link</h2>
                        <p class="card-description">Let clients book themselves with your private link.</p>
                    </div>
                    <div class="form-card-body">
                        <label class="form-check">
                            <input v-model="form.public_booking_enabled" type="checkbox" class="form-checkbox">
                            <span>
                                <span class="block font-medium text-foreground">Public booking enabled</span>
                                <span class="mt-0.5 block text-[12px] text-muted-foreground">When off, your booking link returns a 404.</span>
                            </span>
                        </label>
                        <FormInput v-model="form.slug" label="Link slug" name="slug" required :error="form.errors.slug" />
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <code class="block flex-1 break-all rounded-lg border border-border bg-secondary/70 px-3 py-2 text-[12px] text-muted-foreground">{{ bookingUrlPreview }}</code>
                            <button type="button" class="btn-secondary shrink-0" @click="copyBookingLink">
                                <Icon :name="bookingCopied ? 'check' : 'copy'" :size="15" />
                                {{ bookingCopied ? 'Copied' : 'Copy' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-header-bordered">
                        <h2 class="card-title">Opening hours</h2>
                        <p class="card-description">Controls available times on your public booking page.</p>
                    </div>
                    <div class="divide-y divide-border">
                        <div
                            v-for="day in weekdays"
                            :key="day.value"
                            class="grid gap-2.5 px-4 py-2.5 sm:grid-cols-[5.5rem_auto_1fr_auto_1fr] sm:items-center sm:gap-3 sm:px-5"
                            :class="form.opening_hours[day.value].closed ? 'bg-secondary/30' : ''"
                        >
                            <div class="flex items-center justify-between gap-3 sm:contents">
                                <p class="text-[13px] font-medium">{{ day.label }}</p>
                                <label class="flex cursor-pointer items-center gap-2 text-[12px] text-muted-foreground">
                                    <input v-model="form.opening_hours[day.value].closed" type="checkbox" class="form-checkbox">
                                    Closed
                                </label>
                            </div>
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 sm:contents">
                                <input
                                    v-model="form.opening_hours[day.value].open"
                                    type="time"
                                    class="form-input"
                                    :disabled="form.opening_hours[day.value].closed"
                                >
                                <span class="text-[12px] text-muted-foreground">to</span>
                                <input
                                    v-model="form.opening_hours[day.value].close"
                                    type="time"
                                    class="form-input"
                                    :disabled="form.opening_hours[day.value].closed"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-header-bordered">
                        <h2 class="card-title">Booking rules</h2>
                        <p class="card-description">How far ahead clients can book and how slots are spaced.</p>
                    </div>
                    <div class="grid gap-4 px-4 py-4 sm:grid-cols-3 sm:px-5 sm:py-5">
                        <div class="space-y-1.5">
                            <label for="slot_interval_minutes" class="form-label">Slot interval</label>
                            <select id="slot_interval_minutes" v-model="form.slot_interval_minutes" class="form-select">
                                <option :value="5">Every 5 minutes</option>
                                <option :value="10">Every 10 minutes</option>
                                <option :value="15">Every 15 minutes</option>
                                <option :value="20">Every 20 minutes</option>
                                <option :value="30">Every 30 minutes</option>
                                <option :value="60">Every 60 minutes</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label for="booking_lead_minutes" class="form-label">Minimum notice</label>
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
                        <div class="space-y-1.5">
                            <label for="booking_horizon_days" class="form-label">Book up to</label>
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
                    <div class="card-header-bordered">
                        <h2 class="card-title">Brand colour</h2>
                        <p class="card-description">Sidebar, buttons and booking page accents.</p>
                    </div>
                    <div class="form-card-body">
                        <div class="flex flex-wrap items-center gap-3.5">
                            <label class="relative flex h-12 w-12 cursor-pointer items-center justify-center rounded-lg border border-border bg-card shadow-xs">
                                <span class="absolute inset-1 rounded-md" :style="{ backgroundColor: selected }" />
                                <input v-model="form.primary_color" type="color" class="absolute inset-0 cursor-pointer opacity-0">
                            </label>
                            <div>
                                <p class="text-[13px] font-medium">Primary colour</p>
                                <p class="mt-0.5 font-mono text-[12px] uppercase text-muted-foreground">{{ selected }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="color in presets"
                                :key="color"
                                type="button"
                                class="h-8 w-8 rounded-md border border-border transition-transform hover:scale-105"
                                :class="{ 'ring-2 ring-foreground ring-offset-2 ring-offset-card': selected === color.toUpperCase() }"
                                :style="{ backgroundColor: color }"
                                :aria-label="`Use ${color}`"
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

            <div class="card overflow-hidden">
                <div class="card-header-bordered">
                    <h2 class="card-title">Subscription</h2>
                    <p class="card-description">Your Cutcost plan billing.</p>
                </div>
                <div class="form-card-body">
                    <dl class="grid gap-3 text-[13px] sm:grid-cols-2">
                        <div>
                            <dt class="text-muted-foreground">Plan</dt>
                            <dd class="mt-0.5 font-medium">{{ subscription.plan || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Status</dt>
                            <dd class="mt-0.5 font-medium">{{ subscription.status_label }}</dd>
                        </div>
                    </dl>

                    <p v-if="subscription.cancel_at_label" class="rounded-lg border border-warning/25 bg-warning/[0.06] px-3.5 py-2.5 text-[13px] leading-relaxed text-foreground">
                        Cancellation scheduled. Access continues until {{ subscription.cancel_at_label }}.
                    </p>

                    <p v-if="cancelForm.errors.subscription" class="form-error">{{ cancelForm.errors.subscription }}</p>

                    <button
                        v-if="subscription.can_cancel"
                        type="button"
                        class="btn-secondary text-destructive hover:border-destructive/40 hover:bg-destructive/[0.05]"
                        @click="confirmCancel = true"
                    >
                        Cancel subscription
                    </button>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="confirmCancel"
            title="Cancel your subscription?"
            description="You'll keep full access until the end of your current billing period, then the shop becomes read-only."
            confirm-label="Yes, cancel"
            cancel-label="Keep plan"
            :processing="cancelForm.processing"
            @cancel="confirmCancel = false"
            @confirm="submitCancel"
        />
    </AppLayout>
</template>
