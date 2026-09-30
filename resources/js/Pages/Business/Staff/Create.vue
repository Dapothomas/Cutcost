<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/business/staff');
}
</script>

<template>
    <AppLayout title="Add stylist" subtitle="Create a login for a team member">
        <template #actions>
            <Link href="/business/staff" class="btn-secondary">Back to stylists</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card overflow-hidden" @submit.prevent="submit">
                <div class="card-header-bordered">
                    <h2 class="card-title">Stylist account</h2>
                    <p class="card-description">They’ll use this email and password to log in.</p>
                </div>
                <div class="form-card-body">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <FormInput v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                    <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormInput v-model="form.password" label="Password" name="password" type="password" required :error="form.errors.password" />
                        <FormInput v-model="form.password_confirmation" label="Confirm password" name="password_confirmation" type="password" required />
                    </div>
                </div>
                <div class="card-footer justify-end">
                    <Link href="/business/staff" class="btn-secondary">Cancel</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Adding…' : 'Add stylist' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
