<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCard from '@/Components/PostCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({ posts: Object });
</script>

<template>
    <Head :title="$t(`Mes discussions`)" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-night">{{ $t('Mes discussions') }}</h1>
                    <p class="text-sm text-muted">{{ $t('{n} publication(s)', { n: posts.total }) }}</p>
                </div>
                <Link :href="route('posts.create')" class="btn-primary"><Plus class="h-4 w-4" /> {{ $t('Nouvelle') }}</Link>
            </div>

            <div v-if="posts.data.length" class="space-y-4">
                <PostCard v-for="post in posts.data" :key="post.id" :post="post" />
            </div>
            <EmptyState v-else :title="$t(`Vous n'avez encore rien publié`)" :text="$t(`Posez votre première question ou partagez une ressource utile à la communauté.`)">
                <Link :href="route('posts.create')" class="btn-primary">{{ $t('Créer ma première publication') }}</Link>
            </EmptyState>

            <Pagination :paginator="posts" />
        </div>
    </AuthenticatedLayout>
</template>
