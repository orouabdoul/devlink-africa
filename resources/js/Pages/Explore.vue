<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({ posts: Object, categories: Array, filters: Object });

function apply(changes) {
    const params = { ...props.filters, ...changes };
    Object.keys(params).forEach((k) => (params[k] === null || params[k] === '' || (k === 'sort' && params[k] === 'recent')) && delete params[k]);
    router.get(route('explore'), params, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head :title="$t(`Explorer`)" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-extrabold text-night">{{ $t('Explorer') }}</h1>
                <p class="text-sm text-muted">{{ $t('Parcourez les discussions par technologie.') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="apply({ category: filters.category === cat.slug ? null : cat.slug })"
                    class="card relative overflow-hidden p-3 text-left transition hover:shadow-md"
                    :class="filters.category === cat.slug ? '!border-brand-600 ring-2 ring-brand-600' : ''"
                >
                    <span class="absolute inset-x-0 top-0 h-1" :style="{ backgroundColor: cat.color }" />
                    <p class="text-sm font-bold leading-tight text-night">{{ $t(cat.name) }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $t('{n} publication(s)', { n: cat.posts_count }) }}</p>
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <select :value="filters.type ?? ''" @change="apply({ type: $event.target.value })" class="input !w-auto">
                    <option value="">{{ $t('Tous les types') }}</option>
                    <option value="question">{{ $t('Questions') }}</option>
                    <option value="discussion">{{ $t('Discussions') }}</option>
                    <option value="resource">{{ $t('Ressources') }}</option>
                </select>
                <select :value="filters.sort" @change="apply({ sort: $event.target.value })" class="input !w-auto">
                    <option value="recent">{{ $t('Plus récentes') }}</option>
                    <option value="popular">{{ $t('Plus populaires') }}</option>
                    <option value="unanswered">{{ $t('Sans réponse') }}</option>
                </select>
                <Link v-if="filters.category || filters.type || filters.sort !== 'recent'" :href="route('explore')" class="text-sm font-semibold text-brand-600 hover:underline">
                    {{ $t('Réinitialiser') }}
                </Link>
                <span class="ml-auto text-sm text-muted">{{ $t('{n} résultat(s)', { n: posts.total }) }}</span>
            </div>

            <div v-if="posts.data.length" class="space-y-4">
                <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
            </div>
            <EmptyState v-else :title="$t(`Aucune publication ici`)" :text="$t(`Aucune discussion ne correspond à ces filtres pour le moment.`)">
                <Link :href="route('posts.create')" class="btn-primary">{{ $t('Lancer la discussion') }}</Link>
            </EmptyState>

            <Pagination :paginator="posts" />
        </div>
    </AuthenticatedLayout>
</template>
