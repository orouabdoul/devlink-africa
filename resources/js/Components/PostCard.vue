<script setup>
import { Link } from '@inertiajs/vue3';
import { MessageCircle, MapPin } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import TypeBadge from '@/Components/TypeBadge.vue';
import LikeButton from '@/Components/LikeButton.vue';
import { timeAgo } from '@/utils';

defineProps({ post: { type: Object, required: true } });
</script>

<template>
    <article class="card p-5 transition hover:border-brand-200 hover:shadow-md">
        <header class="flex items-start gap-3">
            <Link :href="route('developers.show', post.user.id)">
                <UserAvatar :user="post.user" size="sm" />
            </Link>
            <div class="min-w-0 flex-1">
                <Link :href="route('developers.show', post.user.id)" class="text-sm font-bold text-night hover:text-brand-600">
                    {{ post.user.name }}
                </Link>
                <p class="flex flex-wrap items-center gap-x-2 text-xs text-muted">
                    <span v-if="post.user.profile?.country" class="inline-flex items-center gap-0.5">
                        <MapPin class="h-3 w-3" />{{ post.user.profile.country }}
                    </span>
                    <span>{{ timeAgo(post.created_at) }}</span>
                </p>
            </div>
            <TypeBadge :type="post.type" />
        </header>

        <Link :href="route('posts.show', post.id)" class="mt-3 block group">
            <h2 class="text-lg font-bold leading-snug text-night group-hover:text-brand-600">{{ post.title }}</h2>
            <p class="mt-1.5 line-clamp-3 text-sm leading-relaxed text-slate-600">{{ post.excerpt }}</p>
        </Link>

        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <CategoryBadge :category="post.category" />
            <div class="flex items-center gap-1">
                <LikeButton :post-id="post.id" :liked="!!post.liked" :count="post.likes_count" />
                <Link
                    :href="route('posts.show', post.id) + '#reponses'"
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1.5 text-sm font-semibold text-muted transition hover:bg-slate-100 hover:text-night"
                >
                    <MessageCircle class="h-4 w-4" />
                    {{ post.comments_count }}
                </Link>
            </div>
        </footer>
    </article>
</template>
