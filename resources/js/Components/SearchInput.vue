<script setup>
import Icon from '@/Components/Icon.vue';
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    /** Index route the filtered request is issued against. */
    url: { type: String, required: true },
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search…' },
    /** Extra query params to preserve alongside the search term (e.g. status). */
    params: { type: Object, default: () => ({}) },
    debounce: { type: Number, default: 300 },
});

const term = ref(props.modelValue);
let timer;

watch(
    () => props.modelValue,
    (value) => {
        if (value !== term.value) term.value = value;
    },
);

function push(value) {
    const query = { ...props.params };

    if (value) {
        query.search = value;
    } else {
        delete query.search;
    }

    router.get(props.url, query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onInput(value) {
    term.value = value;
    window.clearTimeout(timer);
    timer = window.setTimeout(() => push(value.trim()), props.debounce);
}

function clear() {
    window.clearTimeout(timer);
    term.value = '';
    push('');
}

onBeforeUnmount(() => window.clearTimeout(timer));
</script>

<template>
    <div class="search-field">
        <Icon name="search" :size="16" />
        <input
            type="search"
            :value="term"
            :placeholder="placeholder"
            aria-label="Search"
            @input="onInput($event.target.value)"
            @keydown.escape="clear"
        />
        <button
            v-if="term"
            type="button"
            class="absolute right-1.5 inline-flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
            aria-label="Clear search"
            @click="clear"
        >
            <Icon name="close" :size="14" :stroke-width="2" />
        </button>
    </div>
</template>
