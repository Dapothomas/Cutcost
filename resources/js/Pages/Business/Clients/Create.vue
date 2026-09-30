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
                <div class="card-header-bordered">
                    <h2 class="card-title">Client details</h2>
                    <p class="card-description">Name is required — everything else is optional.</p>
                </div>
                <div class="form-card-body">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <FormInput v-model="form.email" label="Email" name="email" type="email" optional :error="form.errors.email" />
                    <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                    <FormTextarea
                        v-model="form.notes"
                        label="Notes"
                        name="notes"
                        optional
                        hint="Preferences, allergies, anything worth remembering next visit."
                        :error="form.errors.notes"
                    />
                </div>
                <div class="card-footer justify-end">
                    <Link href="/business/clients" class="btn-secondary">Cancel</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Saving…' : 'Save client' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
