<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const profileForm = useForm({
    name: user.value?.name ?? '',
    email: user.value?.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm('userDeletion', { password: '' });
const showDeleteConfirm = ref(false);

function updateProfile() {
    profileForm.patch('/profile', { preserveScroll: true });
}

function updatePassword() {
    passwordForm.put('/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

function deleteAccount() {
    deleteForm.delete('/profile', {
        preserveScroll: true,
        onError: () => {
            showDeleteConfirm.value = true;
        },
    });
}
</script>

<template>
    <AppLayout title="Profile" subtitle="Manage your account settings">
        <div class="page-shell max-w-2xl space-y-4">
            <div class="card overflow-hidden">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Profile information</h2>
                    <p class="card-description">Update your name and email address.</p>
                </div>
                <form class="space-y-4 p-6" @submit.prevent="updateProfile">
                    <FormInput v-model="profileForm.name" label="Name" name="name" required :error="profileForm.errors.name" />
                    <FormInput v-model="profileForm.email" label="Email" name="email" type="email" required :error="profileForm.errors.email" />
                    <div class="flex items-center gap-3 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="profileForm.processing">Save</button>
                        <p v-if="page.props.flash?.status === 'profile-updated'" class="text-sm font-medium text-success">Saved.</p>
                    </div>
                </form>
            </div>

            <div class="card overflow-hidden">
                <div class="border-b border-border/60 px-6 py-5">
                    <h2 class="card-title">Update password</h2>
                    <p class="card-description">Use a long, random password to stay secure.</p>
                </div>
                <form class="space-y-4 p-6" @submit.prevent="updatePassword">
                    <FormInput v-model="passwordForm.current_password" label="Current password" name="current_password" type="password" :error="passwordForm.errors.current_password" />
                    <FormInput v-model="passwordForm.password" label="New password" name="password" type="password" :error="passwordForm.errors.password" />
                    <FormInput v-model="passwordForm.password_confirmation" label="Confirm password" name="password_confirmation" type="password" />
                    <div class="flex items-center gap-3 border-t border-border/60 pt-5">
                        <button type="submit" class="btn-primary" :disabled="passwordForm.processing">Save</button>
                        <p v-if="page.props.flash?.status === 'password-updated'" class="text-sm font-medium text-success">Saved.</p>
                    </div>
                </form>
            </div>

            <div class="card overflow-hidden border-destructive/25">
                <div class="border-b border-destructive/15 bg-destructive/[0.03] px-6 py-5">
                    <h2 class="card-title text-destructive">Delete account</h2>
                    <p class="card-description">Once deleted, all data is permanently removed.</p>
                </div>
                <div class="p-6">
                    <div v-if="!showDeleteConfirm">
                        <button type="button" class="btn-destructive" @click="showDeleteConfirm = true">Delete account</button>
                    </div>

                    <form v-else class="space-y-4 rounded-xl border border-destructive/20 bg-destructive/[0.04] p-4" @submit.prevent="deleteAccount">
                        <FormInput v-model="deleteForm.password" label="Password" name="password" type="password" :error="deleteForm.errors.password" />
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="btn-destructive" :disabled="deleteForm.processing">Confirm delete</button>
                            <button type="button" class="btn-secondary" @click="showDeleteConfirm = false; deleteForm.reset()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
