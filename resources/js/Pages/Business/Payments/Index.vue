<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    payments: { type: Object, required: true },
    earnings: { type: Object, required: true },
    earningsPeriods: { type: Array, default: () => [] },
    recentPaidBookings: { type: Array, default: () => [] },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const badgeClass = computed(() => {
    if (props.payments.tone === 'success') return 'badge-success badge-dot';
    if (props.payments.tone === 'warning') return 'badge-warning badge-dot';
    return 'badge-muted badge-dot';
});

function setPeriod(period) {
    router.get('/business/payments', { period }, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Payments" subtitle="Stripe setup and booking revenue">
        <div class="page-shell max-w-3xl space-y-4">
            <div class="card">
                <div class="card-header flex-row items-center justify-between space-y-0">
                    <div>
                        <h2 class="card-title">Earnings</h2>
                        <p class="card-description">Paid client bookings in {{ earnings.label.toLowerCase() }}</p>
                    </div>
                    <div class="flex flex-wrap gap-1 rounded-xl bg-muted p-1">
                        <button
                            v-for="option in earningsPeriods"
                            :key="option.value"
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition-all duration-150"
                            :class="earnings.period === option.value
                                ? 'bg-card text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'"
                            @click="setPeriod(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
                <div class="card-content space-y-4">
                    <div class="relative overflow-hidden rounded-2xl px-5 py-5 text-white" style="background-image: linear-gradient(135deg, hsl(var(--primary)) 0%, hsl(var(--primary-deep)) 100%);">
                        <div class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full bg-white/10 blur-2xl" />
                        <p class="text-[13px] font-medium text-white/80">Total earned · {{ earnings.label }}</p>
                        <p class="mt-1.5 font-display text-4xl font-semibold tracking-tight">{{ earnings.amount_label }}</p>
                        <p class="mt-1.5 text-xs text-white/70">
                            {{ earnings.paid_bookings_count }} paid booking{{ earnings.paid_bookings_count === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <div v-if="recentPaidBookings.length" class="space-y-1">
                        <p class="px-1 pb-1 text-sm font-semibold text-foreground">Recent payments</p>
                        <div
                            v-for="(booking, index) in recentPaidBookings"
                            :key="index"
                            class="flex items-center justify-between gap-4 rounded-xl px-3 py-2.5 text-sm transition-colors hover:bg-primary/[0.03]"
                            :class="{ 'border-b border-border/50': index < recentPaidBookings.length - 1 }"
                        >
                            <div class="min-w-0">
                                <p class="truncate font-medium text-foreground">{{ booking.client_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ booking.service_name }} · {{ booking.paid_at_label }}</p>
                            </div>
                            <span class="shrink-0 font-semibold text-success">{{ booking.amount_label }}</span>
                        </div>
                    </div>

                    <EmptyState
                        v-else
                        title="No paid bookings yet"
                        description="Revenue from client checkout will appear here."
                    />
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Stripe Connect</h2>
                    <p class="card-description">
                        Client booking payments go to your Stripe account. Cutcost only charges your monthly subscription separately.
                    </p>
                </div>
                <div class="card-content space-y-4">
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-border/70 bg-muted/40 px-4 py-3.5">
                        <div>
                            <p class="text-sm font-semibold text-foreground">Status</p>
                            <p class="text-xs text-muted-foreground">Required before clients can pay online</p>
                        </div>
                        <span :class="badgeClass">{{ payments.label }}</span>
                    </div>

                    <dl v-if="payments.account_id" class="divide-y divide-border/60 rounded-xl border border-border/70 text-sm">
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <dt class="text-muted-foreground">Stripe account</dt>
                            <dd class="rounded-md bg-muted px-2 py-1 font-mono text-xs text-muted-foreground">{{ payments.account_id }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <dt class="text-muted-foreground">Card payments</dt>
                            <dd :class="payments.charges_enabled ? 'badge-success' : 'badge-muted'">{{ payments.charges_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <dt class="text-muted-foreground">Payouts</dt>
                            <dd :class="payments.payouts_enabled ? 'badge-success' : 'badge-muted'">{{ payments.payouts_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div v-if="payments.platform_fee_percent > 0" class="flex items-center justify-between gap-4 px-4 py-3">
                            <dt class="text-muted-foreground">Cutcost fee</dt>
                            <dd class="font-medium text-foreground">{{ payments.platform_fee_percent }}% per booking</dd>
                        </div>
                    </dl>

                    <p v-if="payments.bypass_enabled" class="rounded-xl border border-dashed px-4 py-3 text-sm text-muted-foreground">
                        Payment bypass is on in this environment, so bookings confirm without Stripe Connect.
                    </p>

                    <p v-else-if="!payments.ready && !payments.account_id" class="rounded-xl border border-dashed px-4 py-3 text-sm text-muted-foreground">
                        First time? The Cutcost platform Stripe account must have Connect enabled (Stripe Dashboard → Connect → Get started) before shop owners can connect.
                    </p>

                    <form
                        v-if="!payments.ready && !payments.bypass_enabled"
                        method="post"
                        action="/business/payments/connect"
                    >
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="btn-primary">
                            {{ payments.account_id ? 'Continue Stripe setup' : 'Connect Stripe' }}
                        </button>
                    </form>

                    <form
                        v-else-if="payments.account_id && !payments.bypass_enabled"
                        method="post"
                        action="/business/payments/connect"
                    >
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="btn-secondary">
                            Update Stripe details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
