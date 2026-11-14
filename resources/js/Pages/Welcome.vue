<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { MessagesSquare, Search, Users, CircleHelp, BookOpen, ArrowRight, Tags, UserRound } from 'lucide-vue-next';
import AppLogo from '@/Components/AppLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineProps({ stats: Object });

const problems = ['Groupes WhatsApp et Telegram', 'Communautés Facebook', 'Serveurs Discord', 'Forums éparpillés'];
const features = [
    { icon: CircleHelp, title: 'Posez vos questions', text: 'Décrivez votre blocage, ajoutez du code : la communauté vous répond.' },
    { icon: BookOpen, title: 'Partagez vos solutions', text: 'Tutoriels, retours d’expérience, outils : votre savoir reste accessible à tous.' },
    { icon: Tags, title: 'Classé par technologie', text: 'Flutter, Laravel, React, IA, DevOps… chaque discussion à sa place.' },
    { icon: Search, title: 'Retrouvez tout', text: 'Une recherche par mot-clé au lieu de remonter des centaines de messages.' },
    { icon: UserRound, title: 'Un profil développeur', text: 'Pays, compétences, GitHub, LinkedIn : montrez ce que vous savez faire.' },
    { icon: Users, title: 'Trouvez des collaborateurs', text: 'Découvrez des développeurs par pays et par compétence.' },
];
</script>

<template>
    <Head title="Connect. Share. Build together." />

    <div class="min-h-screen bg-canvas">
        <!-- En-tête -->
        <header class="bg-night">
            <div class="kente-stripe h-1" />
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
                <AppLogo dark />
                <nav class="flex items-center gap-2">
                    <LanguageSwitcher dark />
                    <Link :href="route('login')" class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-200 hover:text-white">{{ $t('Connexion') }}</Link>
                    <Link :href="route('register')" class="btn-primary !py-2">{{ $t("S'inscrire") }}</Link>
                </nav>
            </div>

            <!-- Hero -->
            <section class="relative overflow-hidden">
                <div class="pointer-events-none absolute -right-40 top-0 h-[480px] w-[480px] rounded-full bg-brand-600/25 blur-3xl" />
                <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 pb-20 pt-10 sm:px-6 lg:grid-cols-2 lg:pt-16">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-brand-200">
                            Connect · Share · Build together
                        </p>
                        <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] text-white sm:text-5xl">
                            {{ $t('La communauté des') }} <span class="text-brand-500">{{ $t('développeurs africains') }}</span>.
                        </h1>
                        <p class="mt-5 max-w-lg text-lg text-slate-300">
                            {{ $t('Ne perdez plus les bonnes réponses techniques dans des centaines de messages WhatsApp. Posez, partagez et retrouvez le savoir de tout le continent.') }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link :href="route('register')" class="btn-primary !px-6 !py-3 text-base">{{ $t('Rejoindre la communauté') }} <ArrowRight class="h-4 w-4" /></Link>
                            <Link :href="route('login')" class="inline-flex items-center rounded-xl border border-white/20 px-6 py-3 text-base font-semibold text-white hover:bg-white/10">{{ $t("J'ai déjà un compte") }}</Link>
                        </div>
                        <dl class="mt-10 flex gap-8">
                            <div><dt class="text-xs text-slate-400">{{ $t('Membres') }}</dt><dd class="text-2xl font-extrabold text-white">{{ stats.members }}</dd></div>
                            <div><dt class="text-xs text-slate-400">{{ $t('Discussions') }}</dt><dd class="text-2xl font-extrabold text-white">{{ stats.posts }}</dd></div>
                            <div><dt class="text-xs text-slate-400">{{ $t('Réponses') }}</dt><dd class="text-2xl font-extrabold text-white">{{ stats.answers }}</dd></div>
                        </dl>
                    </div>

                    <!-- Aperçu d'une discussion -->
                    <div class="relative hidden lg:block">
                        <div class="rotate-1 rounded-2xl bg-white p-6 shadow-2xl">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">AD</span>
                                <div>
                                    <p class="text-sm font-bold text-night">Aïcha Diallo</p>
                                    <p class="text-xs text-muted">{{ $t('Sénégal · il y a 2 heures') }}</p>
                                </div>
                                <span class="ml-auto rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">{{ $t('Question') }}</span>
                            </div>
                            <p class="mt-4 text-lg font-bold text-night">{{ $t('Comment gérer le mode hors-ligne dans une app Flutter ?') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $t('Mes utilisateurs perdent souvent la connexion. Hive, Isar ou sqflite ?') }}</p>
                            <div class="mt-4 rounded-xl bg-night p-3 font-mono text-xs text-slate-200">final box = await Hive.openBox('cache');</div>
                            <div class="mt-4 flex items-center gap-4 text-sm font-semibold text-muted">
                                <span class="text-rose-500">♥ 12</span><span>💬 {{ $t('{n} réponse(s)', { n: 5 }) }}</span>
                                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700"><span class="h-2 w-2 rounded-full bg-sky-500" />Flutter & Dart</span>
                            </div>
                        </div>
                        <div class="absolute -bottom-16 -left-8 -rotate-2 rounded-2xl bg-white p-4 shadow-xl">
                            <p class="text-xs font-bold text-night">{{ $t('Kwame Mensah a répondu') }}</p>
                            <p class="mt-1 max-w-[220px] text-xs text-slate-600">{{ $t('« Isar fonctionne très bien chez nous en production… »') }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </header>

        <!-- Problème -->
        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="grid gap-10 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-brand-600">{{ $t('Le problème') }}</p>
                    <h2 class="mt-2 text-3xl font-extrabold text-night">{{ $t('Le savoir technique est dispersé.') }}</h2>
                    <p class="mt-4 text-slate-600">
                        {{ $t('Les développeurs échangent partout à la fois. Les bonnes réponses se noient dans le flux, les discussions sont impossibles à retrouver et les collaborations difficiles à organiser.') }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="p in problems" :key="p" class="card flex items-center gap-3 p-4">
                        <MessagesSquare class="h-5 w-5 shrink-0 text-slate-400" />
                        <span class="text-sm font-semibold text-slate-700">{{ $t(p) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Solution -->
        <section class="bg-white py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <p class="text-center text-sm font-bold uppercase tracking-widest text-brand-600">{{ $t('Notre proposition') }}</p>
                <h2 class="mt-2 text-center text-3xl font-extrabold text-night">{{ $t('Un seul espace, structuré, pour apprendre et construire ensemble.') }}</h2>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="f in features" :key="f.title" class="rounded-2xl border border-slate-200 p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><component :is="f.icon" class="h-5 w-5" /></span>
                        <h3 class="mt-4 font-bold text-night">{{ $t(f.title) }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $t(f.text) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Appel à l'action -->
        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl bg-brand-600 px-8 py-14 text-center">
                <div class="kente-stripe absolute inset-x-0 bottom-0 h-1.5" />
                <h2 class="text-3xl font-extrabold text-white">{{ $t('Prêt à rejoindre la communauté ?') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-brand-100">{{ $t("L'inscription est gratuite et prend moins d'une minute.") }}</p>
                <Link :href="route('register')" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 font-bold text-brand-700 hover:bg-brand-50">{{ $t('Créer mon compte') }} <ArrowRight class="h-4 w-4" /></Link>
            </div>
        </section>

        <footer class="border-t border-slate-200 py-8 text-center text-sm text-muted">
            © {{ new Date().getFullYear() }} DevLink Africa — Connect. Share. Build together.
        </footer>
    </div>
</template>
