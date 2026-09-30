<script setup>
defineProps({
    label: { type: String, required: true },
    name: { type: String, required: true },
    error: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
    required: { type: Boolean, default: false },
    optional: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    hint: { type: String, default: '' },
    /** [{ value, label }] */
    options: { type: Array, default: () => [] },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-1.5">
        <label :for="name" class="form-label">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
            <span v-if="optional" class="font-normal text-muted-foreground">(optional)</span>
        </label>
        <select
            :id="name"
            :name="name"
            :value="modelValue"
            :required="required"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? `${name}-error` : undefined"
            class="form-select"
            :class="error ? 'form-input-error' : ''"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="hint && !error" class="form-hint">{{ hint }}</p>
        <p v-if="error" :id="`${name}-error`" class="form-error">{{ error }}</p>
        <slot name="footer" />
    </div>
</template>
