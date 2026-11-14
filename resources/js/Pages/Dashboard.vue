<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CircleHelp, MessagesSquare, BookOpen, Sparkles, TrendingUp, ArrowRight } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import Pagination from '@/Components/Pagination.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    posts: Object,
    filters: Object,
    popularCategories: Array,
    suggestedDevelopers: Array,
    stats: Object,
    profileCompletion: Number,
});

const user = computed(() => usePage().props.auth.user);
const firstName = computed(() => user.value.name.split(' ')[0]);
const tabs = [
    { key: null, label: 'Tout' },
    { key: 'question', label: 'Questions' },
    { key: 'discussion', label: 'Discussions' },
    { key: 'resource', label: 'Ressources' },
];
</script>

<template>
    <Head title="Fil d'actualité" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Zone de publication -->
            <section class="card p-5">
                <p class="text-sm text-muted">Bonjour {{ firstName }} 👋</p>
                <div class="mt-3 flex items-center gap-3">
                    <UserAvatar :user="user" size="md" />
                    <Link
                        :href="route('posts.create')"
                        class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-400 transition hover:border-brand-200 hover:bg-white"
                    >
                        Partagez quelque chose avec la communauté…
                    </Link>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 pl-14">
                    <Link :href="route('posts.create', { type: 'question' })" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                        <CircleHelp class="h-4 w-4" /> Question
                    </Link>
                    <Link :href="route('posts.create', { type: 'discussion' })" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-50">
                        <MessagesSquare class="h-4 w-4" /> Discussion
                    </Link>
                    <Link :href="route('posts.create', { type: 'resource' })" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-50">
                        <BookOpen class="h-4 w-4" /> Ressource
                    </Link>
                </div>
            </section>

            <!-- Complétion du profil -->
            <Link
                v-if="profileCompletion < 100"
                :href="route('profile.edit')"
                class="flex items-center gap-4 rounded-2xl border border-brand-200 bg-brand-50 p-4 transition hover:bg-brand-100"
            >
                <div class="relative h-12 w-12 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-12 w-12 -rotate-90">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#DBEAFE" stroke-width="3.5" />
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#2563EB" stroke-width="3.5" stroke-linecap="round" :stroke-dasharray="`${profileCompletion} 100`" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold text-brand-700">{{ profileCompletion }}%</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-night">Complétez votre profil développeur</p>
                    <p class="text-xs text-slate-600">Ajoutez votre pays, vos compétences et vos liens pour être découvert par la communauté.</p>
                </div>
                <ArrowRight class="h-5 w-5 text-brand-600" />
            </Link>

            <!-- Filtres -->
            <div class="flex gap-1 overflow-x-auto rounded-xl bg-white p-1 ring-1 ring-slate-200">
                <Link
                    v-for="tab in tabs"
                    :key="tab.label"
                    :href="route('dashboard', tab.key ? { type: tab.key } : {})"
                    preserve-scroll
                    class="flex-1 whitespace-nowrap rounded-lg px-3 py-2 text-center text-sm font-semibold transition"
                    :class="filters.type === tab.key ? 'bg-night text-white' : 'text-slate-600 hover:bg-slate-50'"
                >
                    {{ tab.label }}
                </Link>
            </div>

            <!-- Publications -->
            <div v-if="posts.data.length" class="space-y-4">
                <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
            </div>
            <EmptyState v-else title="Aucune publication pour le moment" text="Soyez la première personne à lancer la discussion !">
                <Link :href="route('posts.create')" class="btn-primary">Créer une publication</Link>
            </EmptyState>

            <Pagination :paginator="posts" />
        </div>

        <template #aside>
            <section class="card overflow-hidden">
                <div class="kente-stripe h-1" />
                <div class="grid grid-cols-3 divide-x divide-slate-100 p-4 text-center">
                    <div>
                        <p class="text-xl font-extrabold text-night">{{ stats.members }}</p>
                        <p class="text-[11px] font-medium text-muted">Membres</p>
                    </div>
                    <div>
                        <p class="text-xl font-extrabold text-night">{{ stats.posts }}</p>
                        <p class="text-[11px] font-medium text-muted">Publications</p>
                    </div>
                    <div>
                        <p class="text-xl font-extrabold text-night">{{ stats.answers }}</p>
                        <p class="text-[11px] font-medium text-muted">Réponses</p>
                    </div>
                </div>
            </section>

            <section class="card p-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-night"><TrendingUp class="h-4 w-4 text-brand-600" /> Technologies populaires</h3>
                <ul class="mt-3 space-y-1">
                    <li v-for="cat in popularCategories" :key="cat.id">
                        <Link :href="route('explore', { category: cat.slug })" class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: cat.color }" />{{ cat.name }}
                            </span>
                            <span class="text-xs text-muted">{{ cat.posts_count }}</span>
                        </Link>
                    </li>
                </ul>
                <Link :href="route('explore')" class="mt-2 block text-xs font-semibold text-brand-600 hover:underline">Toutes les catégories →</Link>
            </section>

            <section class="card p-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-night"><Sparkles class="h-4 w-4 text-brand-600" /> Développeurs à découvrir</h3>
                <ul class="mt-3 space-y-3">
                    <li v-for="dev in suggestedDevelopers" :key="dev.id">
                        <Link :href="route('developers.show', dev.id)" class="flex items-center gap-3 rounded-lg hover:opacity-80">
                            <UserAvatar :user="dev" size="sm" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-night">{{ dev.name }}</p>
                                <p class="truncate text-xs text-muted">{{ dev.profile?.headline || dev.profile?.country || 'Membre' }}</p>
                            </div>
                        </Link>
                    </li>
                </ul>
                <Link :href="route('developers.index')" class="mt-3 block text-xs font-semibold text-brand-600 hover:underline">Voir tous les développeurs →</Link>
            </section>
        </template>
    </AuthenticatedLayout>
</template>
