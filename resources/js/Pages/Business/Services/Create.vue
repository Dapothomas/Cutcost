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
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Service details</h2>
                    <p class="card-description">Name, duration, and price shown on your booking page.</p>
                </div>
                <div class="space-y-4 p-6">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormInput v-model="form.duration_minutes" label="Duration (minutes)" name="duration_minutes" type="number" required :error="form.errors.duration_minutes" />
                        <FormInput v-model="form.price" label="Price (£)" name="price" type="number" required :error="form.errors.price" />
                    </div>
                    <label class="flex items-center gap-2.5 rounded-xl border border-border/70 bg-muted/30 px-3.5 py-3 text-sm">
                        <input v-model="form.is_active" type="checkbox" name="is_active" class="h-4 w-4 rounded border-input text-primary focus:ring-primary" />
                        <span>
                            <span class="font-medium text-foreground">Active</span>
                            <span class="block text-xs text-muted-foreground">Visible for booking</span>
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-2 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="form.processing">Save service</button>
                        <Link href="/business/services" class="btn-secondary">Cancel</Link>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
