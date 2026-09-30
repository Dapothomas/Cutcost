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
            <form class="card overflow-hidden" @submit.prevent="updateProfile">
                <div class="card-header-bordered">
                    <h2 class="card-title">Profile information</h2>
                    <p class="card-description">Update your name and email address.</p>
                </div>
                <div class="form-card-body">
                    <FormInput v-model="profileForm.name" label="Name" name="name" required :error="profileForm.errors.name" />
                    <FormInput v-model="profileForm.email" label="Email" name="email" type="email" required :error="profileForm.errors.email" />
                </div>
                <div class="card-footer justify-end">
                    <p v-if="page.props.flash?.status === 'profile-updated'" class="mr-auto text-[13px] font-medium text-success">Saved.</p>
                    <button type="submit" class="btn-primary" :disabled="profileForm.processing">
                        {{ profileForm.processing ? 'Saving…' : 'Save changes' }}
                    </button>
                </div>
            </form>

            <form class="card overflow-hidden" @submit.prevent="updatePassword">
                <div class="card-header-bordered">
                    <h2 class="card-title">Update password</h2>
                    <p class="card-description">Use a long, random password to stay secure.</p>
                </div>
                <div class="form-card-body">
                    <FormInput v-model="passwordForm.current_password" label="Current password" name="current_password" type="password" :error="passwordForm.errors.current_password" />
                    <FormInput v-model="passwordForm.password" label="New password" name="password" type="password" :error="passwordForm.errors.password" />
                    <FormInput v-model="passwordForm.password_confirmation" label="Confirm password" name="password_confirmation" type="password" />
                </div>
                <div class="card-footer justify-end">
                    <p v-if="page.props.flash?.status === 'password-updated'" class="mr-auto text-[13px] font-medium text-success">Saved.</p>
                    <button type="submit" class="btn-primary" :disabled="passwordForm.processing">
                        {{ passwordForm.processing ? 'Saving…' : 'Update password' }}
                    </button>
                </div>
            </form>

            <div class="card overflow-hidden">
                <div class="card-header-bordered">
                    <h2 class="card-title">Delete account</h2>
                    <p class="card-description">Once deleted, all of your shop data is permanently removed.</p>
                </div>
                <div class="form-card-body">
                    <button
                        v-if="!showDeleteConfirm"
                        type="button"
                        class="btn-secondary text-destructive hover:border-destructive/40 hover:bg-destructive/[0.05]"
                        @click="showDeleteConfirm = true"
                    >
                        Delete account
                    </button>

                    <form v-else class="space-y-3.5 rounded-lg border border-destructive/25 bg-destructive/[0.04] p-3.5" @submit.prevent="deleteAccount">
                        <p class="text-[13px] leading-relaxed text-foreground">
                            This can't be undone. Enter your password to confirm.
                        </p>
                        <FormInput v-model="deleteForm.password" label="Password" name="password" type="password" :error="deleteForm.errors.password" />
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="btn-destructive" :disabled="deleteForm.processing">
                                {{ deleteForm.processing ? 'Deleting…' : 'Permanently delete' }}
                            </button>
                            <button type="button" class="btn-secondary" @click="showDeleteConfirm = false; deleteForm.reset()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
