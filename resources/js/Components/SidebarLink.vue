<script setup>
import Icon from '@/Components/Icon.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
    label: { type: String, required: true },
    icon: { type: String, default: 'dashboard' },
});

const classes = computed(() =>
    props.active ? 'sidebar-link sidebar-link-active min-h-10' : 'sidebar-link min-h-10',
);

defineEmits(['click']);
</script>

<template>
    <Link
        :href="href"
        :class="classes"
        :aria-current="active ? 'page' : undefined"
        @click="$emit('click')"
    >
        <Icon :name="icon" :size="17" />
        {{ label }}
    </Link>
</template>
