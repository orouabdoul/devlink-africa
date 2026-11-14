<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { House, Compass, FileText, Users, UserRound, Search, Plus, LogOut, Settings } from 'lucide-vue-next';
import AppLogo from '@/Components/AppLogo.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import Dropdown from '@/Components/Dropdown.vue';
import FlashToast from '@/Components/FlashToast.vue';

defineProps({ wide: { type: Boolean, default: false } });

const page = usePage();
const user = computed(() => page.props.auth.user);
const q = ref(new URLSearchParams(window.location.search).get('q') ?? '');

const nav = computed(() => [
    { label: 'Accueil', route: 'dashboard', icon: House, active: route().current('dashboard') },
    { label: 'Explorer', route: 'explore', icon: Compass, active: route().current('explore') },
    { label: 'Mes discussions', short: 'Mes posts', route: 'posts.mine', icon: FileText, active: route().current('posts.mine') },
    { label: 'Développeurs', short: 'Devs', route: 'developers.index', icon: Users, active: route().current('developers.index') },
    {
        label: 'Mon profil',
        short: 'Profil',
        href: route('developers.show', user.value.id),
        icon: UserRound,
        active: route().current('developers.show', { user: user.value.id }) || route().current('profile.edit'),
    },
]);

function search() {
    router.get(route('search'), { q: q.value });
}
</script>

<template>
    <div class="min-h-screen bg-canvas pb-20 lg:pb-0">
        <div class="kente-stripe h-1 w-full" />
        <!-- Barre supérieure -->
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6">
                <Link :href="route('dashboard')" class="shrink-0" aria-label="Accueil DevLink Africa">
                    <AppLogo class="hidden sm:inline-flex" />
                    <AppLogo compact class="sm:hidden" />
                </Link>

                <form @submit.prevent="search" class="relative flex-1 md:max-w-md" role="search">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="q"
                        type="search"
                        placeholder="Rechercher une discussion, une techno…"
                        class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 text-sm focus:border-brand-500 focus:bg-white focus:ring-brand-500"
                        aria-label="Rechercher"
                    />
                </form>

                <div class="ml-auto flex items-center gap-2">
                    <Link :href="route('posts.create')" class="btn-primary hidden !py-2 sm:inline-flex">
                        <Plus class="h-4 w-4" /> Publier
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center rounded-full focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2" aria-label="Menu du compte">
                                <UserAvatar :user="user" size="sm" />
                            </button>
                        </template>
                        <template #content>
                            <div class="border-b border-slate-100 px-4 py-3">
                                <p class="truncate text-sm font-bold text-night">{{ user.name }}</p>
                                <p class="truncate text-xs text-muted">{{ user.email }}</p>
                            </div>
                            <Link :href="route('developers.show', user.id)" class="flex items-center gap-2 px-4 py-2 text-sm text-night hover:bg-slate-50">
                                <UserRound class="h-4 w-4" /> Mon profil public
                            </Link>
                            <Link :href="route('profile.edit')" class="flex items-center gap-2 px-4 py-2 text-sm text-night hover:bg-slate-50">
                                <Settings class="h-4 w-4" /> Modifier mon profil
                            </Link>
                            <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">
                                <LogOut class="h-4 w-4" /> Déconnexion
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6 sm:px-6">
            <!-- Menu gauche -->
            <aside class="sticky top-24 hidden h-fit w-56 shrink-0 lg:block">
                <nav class="space-y-1">
                    <Link
                        v-for="item in nav"
                        :key="item.label"
                        :href="item.href ?? route(item.route)"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="item.active ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-night'"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="mt-6 rounded-2xl bg-night p-4 text-white">
                    <p class="text-sm font-bold">Une question bloquante ?</p>
                    <p class="mt-1 text-xs text-slate-300">La communauté est là pour vous aider.</p>
                    <Link :href="route('posts.create', { type: 'question' })" class="mt-3 inline-flex w-full items-center justify-center gap-1 rounded-lg bg-brand-600 px-3 py-2 text-xs font-bold hover:bg-brand-700">
                        <Plus class="h-3.5 w-3.5" /> Poser une question
                    </Link>
                </div>
            </aside>

            <!-- Contenu principal -->
            <main class="min-w-0 flex-1">
                <slot />
            </main>

            <!-- Colonne droite -->
            <aside v-if="$slots.aside" class="hidden w-72 shrink-0 space-y-5 xl:block">
                <slot name="aside" />
            </aside>
        </div>

        <!-- Navigation mobile -->
        <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden">
            <Link
                v-for="item in nav"
                :key="item.label"
                :href="item.href ?? route(item.route)"
                class="flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-semibold"
                :class="item.active ? 'text-brand-600' : 'text-slate-500'"
            >
                <component :is="item.icon" class="h-5 w-5" />
                {{ item.short ?? item.label }}
            </Link>
        </nav>

        <Link :href="route('posts.create')" class="fixed bottom-20 right-4 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-lg sm:hidden" aria-label="Publier">
            <Plus class="h-6 w-6" />
        </Link>

        <FlashToast />
    </div>
</template>
