<script setup>
import Icon from '@/Components/Icon.vue';
import { nextTick, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Are you sure?' },
    description: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Confirm' },
    cancelLabel: { type: String, default: 'Cancel' },
    destructive: { type: Boolean, default: true },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel']);

const confirmRef = ref(null);

watch(
    () => props.open,
    async (open) => {
        if (!open) return;
        await nextTick();
        confirmRef.value?.focus();
    },
);

function onKeydown(event) {
    if (event.key === 'Escape') {
        emit('cancel');
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="dialog-overlay flex items-end justify-center p-4 sm:items-center"
            role="dialog"
            aria-modal="true"
            @click.self="emit('cancel')"
            @keydown="onKeydown"
        >
            <div class="dialog-panel">
                <div class="flex items-start gap-3.5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                        :class="destructive ? 'bg-destructive/[0.09] text-destructive' : 'bg-primary/[0.09] text-primary'"
                    >
                        <Icon :name="destructive ? 'alert' : 'bell'" :size="17" />
                    </span>
                    <div class="min-w-0 pt-0.5">
                        <h2 class="font-display text-[15px] font-semibold text-foreground">{{ title }}</h2>
                        <p v-if="description" class="mt-1 text-[13px] leading-relaxed text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn-secondary justify-center" @click="emit('cancel')">
                        {{ cancelLabel }}
                    </button>
                    <button
                        ref="confirmRef"
                        type="button"
                        :class="destructive ? 'btn-destructive' : 'btn-primary'"
                        class="justify-center"
                        :disabled="processing"
                        @click="emit('confirm')"
                    >
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
