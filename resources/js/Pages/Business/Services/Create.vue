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
            <form class="card overflow-hidden" @submit.prevent="submit">
                <div class="card-header-bordered">
                    <h2 class="card-title">Service details</h2>
                    <p class="card-description">Name, duration and price shown on your booking page.</p>
                </div>
                <div class="form-card-body">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormInput v-model="form.duration_minutes" label="Duration (minutes)" name="duration_minutes" type="number" required :error="form.errors.duration_minutes" />
                        <FormInput v-model="form.price" label="Price (£)" name="price" type="number" required :error="form.errors.price" />
                    </div>
                    <label class="form-check">
                        <input v-model="form.is_active" type="checkbox" name="is_active" class="form-checkbox" />
                        <span>
                            <span class="font-medium text-foreground">Active</span>
                            <span class="block text-[12px] text-muted-foreground">Clients can book this service</span>
                        </span>
                    </label>
                </div>
                <div class="card-footer justify-end">
                    <Link href="/business/services" class="btn-secondary">Cancel</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save service' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
