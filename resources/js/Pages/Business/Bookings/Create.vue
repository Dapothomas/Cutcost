<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import FormTextarea from '@/Components/FormTextarea.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
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

const clientOptions = computed(() => props.clients.map((c) => ({ value: c.id, label: c.name })));
const barberOptions = computed(() => props.barbers.map((b) => ({ value: b.id, label: b.name })));
const serviceOptions = computed(() =>
    props.services.map((s) => ({
        value: s.id,
        label: `${s.name} · ${s.duration_minutes} min · ${s.price_label}`,
    })),
);

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
                <div class="card-header-bordered">
                    <h2 class="card-title">Appointment</h2>
                    <p class="card-description">Pick who, what and when — then save.</p>
                </div>

                <div class="form-card-body">
                    <FormSelect
                        v-model="form.client_id"
                        label="Client"
                        name="client_id"
                        required
                        placeholder="Select client"
                        :options="clientOptions"
                        :error="form.errors.client_id"
                    >
                        <template #footer>
                            <Link href="/business/clients/create" class="inline-flex text-[13px] font-medium text-primary hover:underline">
                                Add a new client
                            </Link>
                        </template>
                    </FormSelect>

                    <FormSelect
                        v-model="form.service_id"
                        label="Service"
                        name="service_id"
                        required
                        placeholder="Select service"
                        :options="serviceOptions"
                        :error="form.errors.service_id"
                    />

                    <FormSelect
                        v-model="form.barber_id"
                        label="Stylist"
                        name="barber_id"
                        required
                        placeholder="Select stylist"
                        :options="barberOptions"
                        :error="form.errors.barber_id"
                    />

                    <FormInput
                        v-model="form.starts_at"
                        label="Date & time"
                        name="starts_at"
                        type="datetime-local"
                        required
                        :error="form.errors.starts_at"
                    />

                    <FormTextarea v-model="form.notes" label="Notes" name="notes" optional :error="form.errors.notes" />
                </div>

                <div class="card-footer justify-end">
                    <Link href="/business/bookings" class="btn-secondary">Cancel</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Booking…' : 'Book appointment' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
