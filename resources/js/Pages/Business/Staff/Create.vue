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
    <AppLayout title="Add barber" subtitle="Create a login for a team member">
        <template #actions>
            <Link href="/business/staff" class="btn-secondary">Back to staff</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card overflow-hidden" @submit.prevent="submit">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Barber account</h2>
                    <p class="card-description">They’ll use this email and password to log in.</p>
                </div>
                <div class="space-y-4 p-6">
                    <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                    <FormInput v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                    <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormInput v-model="form.password" label="Password" name="password" type="password" required :error="form.errors.password" />
                        <FormInput v-model="form.password_confirmation" label="Confirm password" name="password_confirmation" type="password" required />
                    </div>
                    <div class="flex flex-wrap gap-2 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="form.processing">Add barber</button>
                        <Link href="/business/staff" class="btn-secondary">Cancel</Link>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
