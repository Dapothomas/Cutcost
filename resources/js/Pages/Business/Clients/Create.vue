<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    notes: '',
});

function submit() {
    form.post('/business/clients');
}
</script>

<template>
    <AppLayout title="Add client" subtitle="Save a new contact to your CRM">
        <template #actions>
            <Link href="/business/clients" class="btn-secondary">Back to clients</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card overflow-hidden" @submit.prevent="submit">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Client details</h2>
                    <p class="card-description">Name is required — everything else is optional.</p>
                </div>
                <div class="space-y-4 p-6">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <FormInput v-model="form.email" label="Email" name="email" type="email" optional :error="form.errors.email" />
                    <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                    <FormTextarea v-model="form.notes" label="Notes" name="notes" optional :error="form.errors.notes" />
                    <div class="flex flex-wrap gap-2 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="form.processing">Save client</button>
                        <Link href="/business/clients" class="btn-secondary">Cancel</Link>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
