<script setup>
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { MessagesSquare, Users, Search } from 'lucide-vue-next';
</script>

<template>
    <div class="flex min-h-screen bg-canvas">
        <!-- Panneau de marque -->
        <div class="relative hidden w-[44%] flex-col justify-between overflow-hidden bg-night p-12 text-white lg:flex">
            <div class="kente-stripe absolute inset-x-0 top-0 h-1.5" />
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl" />
            <Link href="/"><AppLogo dark /></Link>
            <div class="relative">
                <p class="text-sm font-bold uppercase tracking-widest text-brand-200">Connect. Share. Build together.</p>
                <h1 class="mt-3 text-4xl font-extrabold leading-tight">{{ $t('Ne perdez plus les bonnes réponses dans des centaines de messages.') }}</h1>
                <ul class="mt-8 space-y-4 text-slate-300">
                    <li class="flex items-center gap-3"><MessagesSquare class="h-5 w-5 text-brand-500" /> {{ $t('Questions et discussions classées par technologie') }}</li>
                    <li class="flex items-center gap-3"><Search class="h-5 w-5 text-brand-500" /> {{ $t('Des réponses retrouvables en quelques secondes') }}</li>
                    <li class="flex items-center gap-3"><Users class="h-5 w-5 text-brand-500" /> {{ $t('Des développeurs de tout le continent avec qui collaborer') }}</li>
                </ul>
            </div>
            <p class="text-xs text-slate-500">© {{ new Date().getFullYear() }} DevLink Africa</p>
        </div>

        <!-- Formulaire -->
        <div class="relative flex flex-1 flex-col items-center justify-center px-4 py-12 sm:px-8">
            <LanguageSwitcher class="absolute right-4 top-4 sm:right-8 sm:top-6" />
            <Link href="/" class="mb-8 lg:hidden"><AppLogo /></Link>
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>
    </div>
</template>
