<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    duration_minutes: 30,
    price: '',
    is_active: true,
});

function submit() {
    form.post('/business/services');
}
</script>

<template>
    <AppLayout title="Add service" subtitle="Define a treatment clients can book">
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
                    <button type="submit" class="btn-primary" :disabled="form.processing">Save service</button>
                    <Link href="/business/services" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
