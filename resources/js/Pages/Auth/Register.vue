<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

const submit = () => form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <GuestLayout>
        <Head title="Inscription" />

        <h1 class="text-2xl font-extrabold text-night">Rejoignez DevLink Africa</h1>
        <p class="mt-1 text-sm text-muted">Gratuit. Posez vos questions, partagez votre savoir, trouvez des collaborateurs.</p>

        <form @submit.prevent="submit" class="mt-6 space-y-5">
            <div>
                <label for="name" class="label">Nom complet</label>
                <input id="name" type="text" class="input" v-model="form.name" required autofocus autocomplete="name" placeholder="Ex. : Awa Traoré" />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="label">Adresse e-mail</label>
                <input id="email" type="email" class="input" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">Mot de passe</label>
                    <input id="password" type="password" class="input" v-model="form.password" required autocomplete="new-password" />
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirmation</label>
                    <input id="password_confirmation" type="password" class="input" v-model="form.password_confirmation" required autocomplete="new-password" />
                </div>
            </div>
            <InputError class="-mt-3" :message="form.errors.password || form.errors.password_confirmation" />
            <p class="-mt-3 text-xs text-muted">8 caractères minimum.</p>

            <button class="btn-primary w-full !py-3" :disabled="form.processing">Créer mon compte</button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            Déjà membre ?
            <Link :href="route('login')" class="font-semibold text-brand-600 hover:underline">Se connecter</Link>
        </p>
    </GuestLayout>
</template>
