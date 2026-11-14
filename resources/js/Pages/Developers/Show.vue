<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, Github, Linkedin, Pencil, CalendarDays } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import PostCard from '@/Components/PostCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { formatDate } from '@/utils';

const props = defineProps({ developer: Object, posts: Array, likesReceived: Number, isMe: Boolean });
const p = props.developer.profile ?? {};
</script>

<template>
    <Head :title="developer.name" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <section class="card overflow-hidden">
                <div class="relative h-32 bg-night sm:h-40">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(37,99,235,0.6),transparent_55%)]" />
                    <div class="kente-stripe absolute inset-x-0 bottom-0 h-1.5" />
                </div>
                <div class="px-6 pb-6">
                    <div class="relative z-10 -mt-14 flex flex-wrap items-end justify-between gap-4">
                        <UserAvatar :user="developer" size="xl" class="!ring-4" />
                        <Link v-if="isMe" :href="route('profile.edit')" class="btn-secondary"><Pencil class="h-4 w-4" /> Modifier mon profil</Link>
                    </div>
                    <h1 class="mt-4 text-2xl font-extrabold text-night">{{ developer.name }}</h1>
                    <p v-if="p.headline" class="text-base font-medium text-slate-600">{{ p.headline }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted">
                        <span v-if="p.country" class="inline-flex items-center gap-1"><MapPin class="h-4 w-4 text-brand-600" />{{ p.country }}</span>
                        <span class="inline-flex items-center gap-1"><CalendarDays class="h-4 w-4" />Membre depuis le {{ formatDate(developer.created_at) }}</span>
                    </div>

                    <p v-if="p.bio" class="mt-4 max-w-2xl whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ p.bio }}</p>
                    <p v-else-if="isMe" class="mt-4 text-sm italic text-muted">Ajoutez une présentation pour que la communauté vous connaisse mieux.</p>

                    <div v-if="p.skills?.length" class="mt-4 flex flex-wrap gap-2">
                        <span v-for="s in p.skills" :key="s" class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700 ring-1 ring-inset ring-brand-200">{{ s }}</span>
                    </div>

                    <div v-if="p.github_url || p.linkedin_url" class="mt-4 flex flex-wrap gap-2">
                        <a v-if="p.github_url" :href="p.github_url" target="_blank" rel="noopener noreferrer" class="btn-secondary !py-2"><Github class="h-4 w-4" /> GitHub</a>
                        <a v-if="p.linkedin_url" :href="p.linkedin_url" target="_blank" rel="noopener noreferrer" class="btn-secondary !py-2"><Linkedin class="h-4 w-4" /> LinkedIn</a>
                    </div>

                    <dl class="mt-6 grid grid-cols-3 divide-x divide-slate-100 rounded-xl bg-slate-50 py-3 text-center">
                        <div><dt class="text-[11px] font-medium text-muted">Publications</dt><dd class="text-lg font-extrabold text-night">{{ developer.posts_count }}</dd></div>
                        <div><dt class="text-[11px] font-medium text-muted">Réponses</dt><dd class="text-lg font-extrabold text-night">{{ developer.comments_count }}</dd></div>
                        <div><dt class="text-[11px] font-medium text-muted">J'aime reçus</dt><dd class="text-lg font-extrabold text-night">{{ likesReceived }}</dd></div>
                    </dl>
                </div>
            </section>

            <h2 class="text-lg font-extrabold text-night">Publications récentes</h2>
            <div v-if="posts.length" class="space-y-4">
                <PostCard v-for="post in posts" :key="post.id" :post="post" />
            </div>
            <EmptyState v-else title="Aucune publication" :text="isMe ? 'Partagez votre première question ou ressource.' : 'Ce membre n\'a pas encore publié.'" />
        </div>
    </AuthenticatedLayout>
</template>
