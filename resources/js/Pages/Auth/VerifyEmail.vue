<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head :title="$t(`Vérification de l'e-mail`)" />

        <div class="mb-4 text-sm text-muted">
            {{ $t('Merci pour votre inscription ! Confirmez votre adresse en cliquant sur le lien que nous venons de vous envoyer.') }}
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            {{ $t('Un nouveau lien de vérification vient de vous être envoyé.') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t("Renvoyer l'e-mail") }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-muted underline hover:text-night focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                    >{{ $t('Déconnexion') }}</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
