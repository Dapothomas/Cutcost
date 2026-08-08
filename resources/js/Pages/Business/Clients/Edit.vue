<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    client: { type: Object, required: true },
});

const form = useForm({
    name: props.client.name,
    email: props.client.email ?? '',
    phone: props.client.phone ?? '',
    notes: props.client.notes ?? '',
});

function submit() {
    form.put(`/business/clients/${props.client.id}`);
}
</script>

<template>
    <AppLayout title="Edit client" :subtitle="client.name">
        <template #actions>
            <Link href="/business/clients" class="btn-secondary">Back to clients</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card space-y-4 p-6" @submit.prevent="submit">
                <FormInput v-model="form.name" label="Name" name="name" required :error="form.errors.name" />
                <FormInput v-model="form.email" label="Email" name="email" type="email" optional :error="form.errors.email" />
                <FormInput v-model="form.phone" label="Phone" name="phone" optional :error="form.errors.phone" />
                <FormTextarea v-model="form.notes" label="Notes" name="notes" optional :error="form.errors.notes" />
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="btn-primary" :disabled="form.processing">Save changes</button>
                    <Link href="/business/clients" class="btn-secondary">Cancel</Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
