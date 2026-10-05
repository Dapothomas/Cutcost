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
    <AppLayout title="Payments" :subtitle="`${payments.provider_label} setup and booking revenue`">
        <div class="page-shell max-w-3xl space-y-4">
            <div class="card overflow-hidden">
                <div class="card-header-bordered flex-row flex-wrap items-center justify-between gap-3 space-y-0">
                    <div class="min-w-0">
                        <h2 class="card-title">Earnings</h2>
                        <p class="card-description">Paid client bookings in {{ earnings.label.toLowerCase() }}</p>
                    </div>
                    <div class="-mx-0.5 min-w-0 max-w-full overflow-x-auto px-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <div class="seg w-max">
                            <button
                                v-for="option in earningsPeriods"
                                :key="option.value"
                                type="button"
                                class="seg-item"
                                :class="earnings.period === option.value ? 'seg-item-active' : ''"
                                @click="setPeriod(option.value)"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-b border-border bg-secondary/40 px-4 py-4 sm:px-5">
                    <p class="stat-label">Total earned · {{ earnings.label }}</p>
                    <p class="mt-2 font-display text-[2.125rem] font-semibold leading-none tracking-[-0.03em] tabular-nums text-foreground">
                        {{ earnings.amount_label }}
                    </p>
                    <p class="mt-2 text-[13px] text-muted-foreground">
                        {{ earnings.paid_bookings_count }} paid booking{{ earnings.paid_bookings_count === 1 ? '' : 's' }}
                    </p>
                </div>

                <template v-if="recentPaidBookings.length">
                    <p class="section-title px-4 pb-1 pt-3.5 sm:px-5">Recent payments</p>
                    <div class="divide-y divide-border/70">
                        <div
                            v-for="(booking, index) in recentPaidBookings"
                            :key="index"
                            class="flex items-center justify-between gap-4 px-4 py-2.5 transition-colors hover:bg-secondary/60 sm:px-5"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-foreground">{{ booking.client_name }}</p>
                                <p class="truncate text-[12px] text-muted-foreground">{{ booking.service_name }} · {{ booking.paid_at_label }}</p>
                            </div>
                            <span class="shrink-0 text-[13px] font-semibold tabular-nums text-success">{{ booking.amount_label }}</span>
                        </div>
                    </div>
                </template>

                <EmptyState
                    v-else
                    icon="revenue"
                    title="No paid bookings yet"
                    description="Revenue from client checkout will show up here."
                />
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">{{ payments.provider_label }}</h2>
                    <p class="card-description">
                        Client booking payments go to your {{ payments.provider_label }} account. Cutcost only charges your monthly subscription separately.
                    </p>
                </div>
                <div class="card-content space-y-3.5">
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-border bg-secondary/50 px-3.5 py-3">
                        <div>
                            <p class="text-[13px] font-semibold text-foreground">Status</p>
                            <p class="text-[12px] text-muted-foreground">Required before clients can pay online</p>
                        </div>
                        <span :class="badgeClass">{{ payments.label }}</span>
                    </div>

                    <dl v-if="payments.account_id" class="divide-y divide-border rounded-lg border border-border text-[13px]">
                        <div class="flex items-center justify-between gap-4 px-3.5 py-2.5">
                            <dt class="text-muted-foreground">{{ payments.provider_label }} account</dt>
                            <dd class="rounded-md bg-secondary px-2 py-1 font-mono text-[11.5px] text-muted-foreground">{{ payments.account_id }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-3.5 py-2.5">
                            <dt class="text-muted-foreground">Card payments</dt>
                            <dd :class="payments.charges_enabled ? 'badge-success' : 'badge-muted'">{{ payments.charges_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-3.5 py-2.5">
                            <dt class="text-muted-foreground">Payouts</dt>
                            <dd :class="payments.payouts_enabled ? 'badge-success' : 'badge-muted'">{{ payments.payouts_enabled ? 'Enabled' : 'Not enabled' }}</dd>
                        </div>
                        <div v-if="payments.platform_fee_percent > 0" class="flex items-center justify-between gap-4 px-3.5 py-2.5">
                            <dt class="text-muted-foreground">Cutcost fee</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ payments.platform_fee_percent }}% per booking</dd>
                        </div>
                    </dl>

                    <p v-if="payments.bypass_enabled" class="rounded-lg border border-dashed border-border px-3.5 py-2.5 text-[13px] leading-relaxed text-muted-foreground">
                        Payment bypass is on in this environment, so bookings confirm without connecting {{ payments.provider_label }}.
                    </p>

                    <p v-else-if="!payments.ready && !payments.account_id" class="rounded-lg border border-dashed border-border px-3.5 py-2.5 text-[13px] leading-relaxed text-muted-foreground">
                        First time? The Cutcost {{ payments.provider_label }} account must have connected accounts enabled before shop owners can connect.
                    </p>

                    <form
                        v-if="!payments.ready && !payments.bypass_enabled"
                        method="post"
                        action="/business/payments/connect"
                    >
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="btn-primary">
                            {{ payments.account_id ? `Continue ${payments.provider_label} setup` : `Connect ${payments.provider_label}` }}
                        </button>
                    </form>

                    <form
                        v-else-if="payments.account_id && !payments.bypass_enabled"
                        method="post"
                        action="/business/payments/connect"
                    >
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="btn-secondary">
                            Update {{ payments.provider_label }} details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
