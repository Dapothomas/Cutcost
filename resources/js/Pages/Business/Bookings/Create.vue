<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    clients: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    barbers: { type: Array, default: () => [] },
});

const form = useForm({
    client_id: '',
    service_id: '',
    barber_id: '',
    starts_at: '',
    notes: '',
});

function submit() {
    form.post('/business/bookings');
}
</script>

<template>
    <AppLayout title="Book appointment" subtitle="Schedule a new visit">
        <template #actions>
            <Link href="/business/bookings" class="btn-secondary">Back to bookings</Link>
        </template>

        <div class="page-shell max-w-xl">
            <form class="card overflow-hidden" @submit.prevent="submit">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Appointment</h2>
                    <p class="card-description">Pick who, what, when — then save.</p>
                </div>
                <div class="space-y-4 p-6">
                    <div class="space-y-2">
                        <label for="client_id" class="text-sm font-medium">Client <span class="text-destructive">*</span></label>
                        <select id="client_id" v-model="form.client_id" name="client_id" required class="form-select">
                            <option value="" disabled>Select client</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                        </select>
                        <p v-if="form.errors.client_id" class="text-sm text-destructive">{{ form.errors.client_id }}</p>
                        <Link href="/business/clients/create" class="inline-flex text-sm font-medium text-primary hover:underline">+ Add new client</Link>
                    </div>

                    <div class="space-y-2">
                        <label for="service_id" class="text-sm font-medium">Service <span class="text-destructive">*</span></label>
                        <select id="service_id" v-model="form.service_id" name="service_id" required class="form-select">
                            <option value="" disabled>Select service</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">
                                {{ service.name }} ({{ service.duration_minutes }} min · {{ service.price_label }})
                            </option>
                        </select>
                        <p v-if="form.errors.service_id" class="text-sm text-destructive">{{ form.errors.service_id }}</p>
                    </div>

                    <div class="space-y-2">
                        <label for="barber_id" class="text-sm font-medium">Stylist <span class="text-destructive">*</span></label>
                        <select id="barber_id" v-model="form.barber_id" name="barber_id" required class="form-select">
                            <option value="" disabled>Select stylist</option>
                            <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
                        </select>
                        <p v-if="form.errors.barber_id" class="text-sm text-destructive">{{ form.errors.barber_id }}</p>
                    </div>

                    <FormInput v-model="form.starts_at" label="Date & time" name="starts_at" type="datetime-local" required :error="form.errors.starts_at" />
                    <FormTextarea v-model="form.notes" label="Notes" name="notes" optional :error="form.errors.notes" />

                    <div class="flex flex-wrap gap-2 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="form.processing">Book appointment</button>
                        <Link href="/business/bookings" class="btn-secondary">Cancel</Link>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
