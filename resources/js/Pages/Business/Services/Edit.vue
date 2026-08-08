<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    service: { type: Object, required: true },
});

const form = useForm({
    name: props.service.name,
    duration_minutes: props.service.duration_minutes,
    price: props.service.price,
    is_active: props.service.is_active,
});

function submit() {
    form.put(`/business/services/${props.service.id}`);
}
</script>

<template>
    <AppLayout title="Edit service" :subtitle="service.name">
        <template #actions>
            <Link href="/business/services" class="btn-secondary">Back to services</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card space-y-4 p-6" @submit.prevent="submit">
                <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                <FormInput v-model="form.duration_minutes" label="Duration (minutes)" name="duration_minutes" type="number" required :error="form.errors.duration_minutes" />
                <FormInput v-model="form.price" label="Price (£)" name="price" type="number" required :error="form.errors.price" />
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" name="is_active" class="rounded border-input" />
                    Active (visible for booking)
                </label>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary" :disabled="form.processing">Save changes</button>
                    <Link href="/business/services" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
