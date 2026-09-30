<script setup>
defineProps({
    label: { type: String, required: true },
    name: { type: String, required: true },
    error: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    rows: { type: Number, default: 4 },
    optional: { type: Boolean, default: false },
    hint: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-1.5">
        <label :for="name" class="form-label">
            {{ label }}
            <span v-if="optional" class="font-normal text-muted-foreground">(optional)</span>
        </label>
        <textarea
            :id="name"
            :name="name"
            :rows="rows"
            :value="modelValue"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? `${name}-error` : undefined"
            class="form-textarea"
            :class="error ? 'form-input-error' : ''"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="hint && !error" class="form-hint">{{ hint }}</p>
        <p v-if="error" :id="`${name}-error`" class="form-error">{{ error }}</p>
    </div>
</template>
