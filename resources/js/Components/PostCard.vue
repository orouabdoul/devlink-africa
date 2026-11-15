<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { MessageCircle, MapPin, Paperclip } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import TypeBadge from '@/Components/TypeBadge.vue';
import LikeButton from '@/Components/LikeButton.vue';
import { timeAgo } from '@/utils';

const props = defineProps({ post: { type: Object, required: true } });
const images = computed(() => (props.post.attachments ?? []).filter((a) => a.kind !== 'document'));
const docCount = computed(() => (props.post.attachments ?? []).filter((a) => a.kind === 'document').length);
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
                        <MapPin class="h-3 w-3" />{{ $t(post.user.profile.country) }}
                    </span>
                    <span>{{ timeAgo(post.created_at) }}</span>
                </p>
            </div>
            <TypeBadge :type="post.type" />
        </header>

        <Link :href="route('posts.show', post.id)" class="mt-3 block group">
            <h2 class="text-lg font-bold leading-snug text-night group-hover:text-brand-600">{{ post.title }}</h2>
            <p class="mt-1.5 line-clamp-3 text-sm leading-relaxed text-slate-600">{{ post.excerpt }}</p>
            <div v-if="images.length" class="mt-3 flex gap-2">
                <img
                    v-for="a in images.slice(0, 3)"
                    :key="a.id"
                    :src="a.url"
                    alt=""
                    loading="lazy"
                    class="h-20 w-32 rounded-lg object-cover object-top ring-1 ring-slate-200"
                />
                <span v-if="images.length > 3" class="flex h-20 w-14 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-muted">
                    +{{ images.length - 3 }}
                </span>
            </div>
        </Link>

        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <CategoryBadge :category="post.category" />
                <span v-if="docCount" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                    <Paperclip class="h-3.5 w-3.5" />{{ $t('{n} document(s)', { n: docCount }) }}
                </span>
            </div>
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
