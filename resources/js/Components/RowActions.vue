<script setup>
import Icon from '@/Components/Icon.vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const open = ref(false);
const root = ref(null);

function close() {
    open.value = false;
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        close();
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="root" class="relative inline-flex">
        <button
            type="button"
            class="icon-btn icon-btn-sm"
            :class="open ? 'bg-secondary text-foreground' : ''"
            aria-label="Row actions"
            :aria-expanded="open"
            @click.stop="open = !open"
        >
            <Icon name="more" :size="16" :stroke-width="2.5" />
        </button>

        <div v-if="open" class="menu absolute right-0 top-full mt-1" @click="close">
            <slot />
        </div>
    </div>
</template>
