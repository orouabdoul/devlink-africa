<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({ q: String, posts: Object, developers: Array });
const term = ref(props.q);
const submit = () => router.get(route('search'), { q: term.value });
const suggestions = ['Laravel', 'Flutter', 'Mobile Money', 'Docker', 'React'];
</script>

<template>
    <Head :title="q ? `Recherche : ${q}` : 'Recherche'" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <form @submit.prevent="submit" class="card flex items-center gap-2 p-2" role="search">
                <Search class="ml-2 h-5 w-5 text-slate-400" />
                <input v-model="term" type="search" autofocus class="flex-1 border-0 text-base focus:ring-0" placeholder="Mot-clé : Laravel, Flutter, déploiement…" aria-label="Rechercher" />
                <button class="btn-primary">Rechercher</button>
            </form>

            <template v-if="!q">
                <div class="card p-6">
                    <p class="text-sm font-semibold text-night">Recherches suggérées</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Link v-for="s in suggestions" :key="s" :href="route('search', { q: s })" class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-700">{{ s }}</Link>
                    </div>
                </div>
            </template>

            <template v-else>
                <section v-if="developers.length">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-muted">Développeurs</h2>
                    <div class="flex gap-3 overflow-x-auto pb-1">
                        <Link v-for="d in developers" :key="d.id" :href="route('developers.show', d.id)" class="card flex min-w-[220px] items-center gap-3 p-3 hover:border-brand-200">
                            <UserAvatar :user="d" size="sm" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-night">{{ d.name }}</p>
                                <p class="truncate text-xs text-muted">{{ d.profile?.headline || d.profile?.country }}</p>
                            </div>
                        </Link>
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-muted">{{ posts.total }} discussion(s) pour « {{ q }} »</h2>
                    <div v-if="posts.data.length" class="space-y-4">
                        <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
                    </div>
                    <EmptyState v-else title="Aucun résultat" :text="`Personne n'a encore parlé de « ${q} ». Et si vous posiez la question ?`">
                        <Link :href="route('posts.create', { type: 'question' })" class="btn-primary">Poser la question</Link>
                    </EmptyState>
                    <Pagination :paginator="posts" />
                </section>
            </template>
        </div>
    </AuthenticatedLayout>
</template>
