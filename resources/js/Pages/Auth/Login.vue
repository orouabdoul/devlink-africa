<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    demo: Object,
});

const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') });

function fillDemo() {
    form.email = props.demo.email;
    form.password = props.demo.password;
}
</script>

<template>
    <GuestLayout>
        <Head :title="$t(`Connexion`)" />

        <h1 class="text-2xl font-extrabold text-night">{{ $t('Bon retour parmi nous 👋') }}</h1>
        <p class="mt-1 text-sm text-muted">{{ $t('Connectez-vous pour rejoindre les discussions.') }}</p>

        <div v-if="status" class="mt-4 rounded-xl bg-green-50 p-3 text-sm font-medium text-green-700">{{ status }}</div>

        <button v-if="demo" type="button" @click="fillDemo" class="mt-6 flex w-full items-center gap-3 rounded-xl border border-dashed border-brand-200 bg-brand-50 p-3 text-left transition hover:bg-brand-100">
            <Sparkles class="h-5 w-5 shrink-0 text-brand-600" />
            <span class="text-xs text-slate-700">
                <strong class="block text-sm text-night">{{ $t('Compte de démonstration') }}</strong>
                {{ demo.email }} · {{ demo.password }} — {{ $t('cliquez pour remplir') }}
            </span>
        </button>

        <form @submit.prevent="submit" class="mt-6 space-y-5">
            <div>
                <label for="email" class="label">{{ $t('Adresse e-mail') }}</label>
                <input id="email" type="email" class="input" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="text-sm font-semibold text-night">{{ $t('Mot de passe') }}</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-semibold text-brand-600 hover:underline">{{ $t('Mot de passe oublié ?') }}</Link>
                </div>
                <input id="password" type="password" class="input" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-slate-600">{{ $t('Se souvenir de moi') }}</span>
            </label>

            <button class="btn-primary w-full !py-3" :disabled="form.processing">{{ $t('Se connecter') }}</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            {{ $t('Pas encore membre ?') }}
            <Link :href="route('register')" class="font-semibold text-brand-600 hover:underline">{{ $t('Créer un compte') }}</Link>
        </p>
    </GuestLayout>
</template>
