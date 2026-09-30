<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
    label: { type: String, default: '' },
});

// Booking and payment statuses share this map; the two vocabularies don't collide.
const variants = {
    scheduled: { cls: 'badge-outline', label: 'Scheduled' },
    completed: { cls: 'badge-success', label: 'Completed' },
    cancelled: { cls: 'badge-muted', label: 'Cancelled' },
    no_show: { cls: 'badge-danger', label: 'No show' },
    pending_payment: { cls: 'badge-warning', label: 'Awaiting payment' },
    pending: { cls: 'badge-warning', label: 'Awaiting payment' },
    paid: { cls: 'badge-success', label: 'Paid' },
    waived: { cls: 'badge-muted', label: 'No payment' },
    failed: { cls: 'badge-danger', label: 'Payment failed' },
    active: { cls: 'badge-success', label: 'Active' },
    hidden: { cls: 'badge-muted', label: 'Hidden' },
};

const variant = computed(() => variants[props.status] ?? { cls: 'badge-default', label: props.status });
const text = computed(() => props.label || variant.value.label);
</script>

<template>
    <span class="badge-dot" :class="variant.cls">{{ text }}</span>
</template>
