<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateDeveloperProfileForm from './Partials/UpdateDeveloperProfileForm.vue';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    developerProfile: Object,
    countries: Array,
});
</script>

<template>
    <Head title="Mon profil" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <h1 class="text-2xl font-extrabold text-night">Mon profil</h1>

            <div class="card p-6 sm:p-8">
                <UpdateDeveloperProfileForm :profile="developerProfile" :countries="countries" />
            </div>

            <div class="card p-6 sm:p-8">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </div>

            <div class="card p-6 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="card p-6 sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
