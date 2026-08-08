<script setup>
defineProps({
    label: { type: String, required: true },
    name: { type: String, required: true },
    type: { type: String, default: 'text' },
    error: { type: String, default: '' },
    modelValue: { type: [String, Number, Boolean], default: '' },
    required: { type: Boolean, default: false },
    optional: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-2">
        <label :for="name" class="text-sm font-medium leading-none">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
            <span v-if="optional" class="font-normal text-muted-foreground">(optional)</span>
        </label>
        <input
            :id="name"
            :name="name"
            :type="type"
            :value="modelValue"
            :required="required"
            class="form-input"
            :class="error ? 'border-destructive focus-visible:border-destructive focus-visible:ring-destructive/15' : ''"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>
