<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    payments: { type: Object, required: true },
    earnings: { type: Object, required: true },
    earningsPeriods: { type: Array, default: () => [] },
    recentPaidBookings: { type: Array, default: () => [] },
});

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const badgeClass = computed(() => {
    if (props.payments.tone === 'success') return 'badge-outline border-emerald-200 bg-emerald-50 text-emerald-800';
    if (props.payments.tone === 'warning') return 'badge-outline border-amber-200 bg-amber-50 text-amber-900';
    return 'badge-muted';
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
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in earningsPeriods"
                            :key="option.value"
                            type="button"
                            class="rounded-full px-3 py-1 text-xs font-medium transition"
                            :class="earnings.period === option.value
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground hover:text-foreground'"
                            @click="setPeriod(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
                <div class="card-content space-y-4">
                    <div class="rounded-xl border bg-muted/40 px-5 py-4">
                        <p class="text-sm text-muted-foreground">Total earned</p>
                        <p class="mt-1 text-3xl font-bold tracking-tight">{{ earnings.amount_label }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ earnings.paid_bookings_count }} paid booking{{ earnings.paid_bookings_count === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <div v-if="recentPaidBookings.length" class="space-y-3">
                        <p class="text-sm font-medium">Recent payments</p>
                        <div
                            v-for="(booking, index) in recentPaidBookings"
                            :key="index"
                            class="flex items-center justify-between gap-4 border-b pb-3 text-sm last:border-b-0 last:pb-0"
                        >
                            <div>
                                <p class="font-medium">{{ booking.client_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ booking.service_name }} · {{ booking.paid_at_label }}</p>
                            </div>
                            <span class="font-semibold">{{ booking.amount_label }}</span>
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
                    <div class="flex items-center justify-between gap-4 rounded-xl border bg-muted/40 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium">Status</p>
                            <p class="text-xs text-muted-foreground">Required before clients can pay online</p>
                        </div>
                        <span :class="badgeClass">{{ payments.label }}</span>
                    </div>

                    <dl v-if="payments.account_id" class="grid gap-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Stripe account</dt>
                            <dd class="font-mono text-xs">{{ payments.account_id }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Card payments</dt>
                            <dd>{{ payments.charges_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Payouts</dt>
                            <dd>{{ payments.payouts_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div v-if="payments.platform_fee_percent > 0" class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Cutcost fee</dt>
                            <dd>{{ payments.platform_fee_percent }}% per booking</dd>
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
