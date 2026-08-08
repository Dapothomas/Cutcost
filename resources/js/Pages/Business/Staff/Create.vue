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
            <form class="card space-y-4 p-6" @submit.prevent="submit">
                <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                <FormInput v-model="form.email" label="Email" name="email" type="email" required :error="form.errors.email" />
                <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                <FormInput v-model="form.password" label="Password" name="password" type="password" required :error="form.errors.password" />
                <FormInput v-model="form.password_confirmation" label="Confirm password" name="password_confirmation" type="password" required />
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary" :disabled="form.processing">Add barber</button>
                    <Link href="/business/staff" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
