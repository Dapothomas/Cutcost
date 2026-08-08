<script setup>
defineProps({
    label: { type: String, required: true },
    name: { type: String, required: true },
    error: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    rows: { type: Number, default: 4 },
    optional: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="space-y-2">
        <label :for="name" class="text-sm font-medium leading-none">
            {{ label }}
            <span v-if="optional" class="font-normal text-muted-foreground">(optional)</span>
        </label>
        <textarea
            :id="name"
            :name="name"
            :rows="rows"
            :value="modelValue"
            class="form-textarea"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
    </div>
</template>
