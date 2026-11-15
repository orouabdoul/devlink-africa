<script setup>
import { computed, ref } from 'vue';
import { t } from '@/i18n';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2, MapPin, MessageCircle, Send } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserAvatar from '@/Components/UserAvatar.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import TypeBadge from '@/Components/TypeBadge.vue';
import LikeButton from '@/Components/LikeButton.vue';
import CommentCard from '@/Components/CommentCard.vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import TranslateButton from '@/Components/TranslateButton.vue';
import DocumentList from '@/Components/DocumentList.vue';
import DocumentPicker from '@/Components/DocumentPicker.vue';
import ImagePicker from '@/Components/ImagePicker.vue';
import EditorToolbar from '@/Components/EditorToolbar.vue';
import SecretWarning from '@/Components/SecretWarning.vue';
import { timeAgo, formatDate } from '@/utils';

const props = defineProps({ post: Object, comments: Array, can: Object, related: Array });
const me = usePage().props.auth.user;

const form = useForm({ content: '', images: [], documents: [] });
const docPicker = ref(null);
const images = computed(() => props.post.attachments.filter((a) => a.kind !== 'document'));
const documents = computed(() => props.post.attachments.filter((a) => a.kind === 'document'));
const docError = () => form.errors.documents || Object.entries(form.errors).find(([k]) => k.startsWith('documents.'))?.[1] || '';
const replyBox = ref(null);
const postBody = ref(null);
const picker = ref(null);
function onPaste(e) {
    const files = [...(e.clipboardData?.files ?? [])];
    if (!files.length) return;
    e.preventDefault();
    picker.value.addFiles(files);
}
const imageError = () => form.errors.images || Object.entries(form.errors).find(([k]) => k.startsWith('images.'))?.[1] || '';
function reply() {
    form.post(route('comments.store', props.post.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function destroy() {
    if (confirm(t('Supprimer définitivement cette publication et ses réponses ?'))) {
        router.delete(route('posts.destroy', props.post.id));
    }
}
</script>

<template>
    <Head :title="post.title" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <Link :href="route('dashboard')" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-night"><ArrowLeft class="h-4 w-4" /> {{ $t('Retour au fil') }}</Link>

            <article class="card p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <TypeBadge :type="post.type" />
                    <CategoryBadge :category="post.category" />
                </div>
                <h1 class="mt-4 text-2xl font-extrabold leading-tight text-night sm:text-3xl">{{ post.title }}</h1>

                <div class="mt-4 flex items-center gap-3 border-b border-slate-100 pb-5">
                    <Link :href="route('developers.show', post.user.id)"><UserAvatar :user="post.user" size="md" /></Link>
                    <div class="flex-1">
                        <Link :href="route('developers.show', post.user.id)" class="text-sm font-bold text-night hover:text-brand-600">{{ post.user.name }}</Link>
                        <p class="flex flex-wrap items-center gap-x-2 text-xs text-muted">
                            <span v-if="post.user.profile?.headline">{{ post.user.profile.headline }}</span>
                            <span v-if="post.user.profile?.country" class="inline-flex items-center gap-0.5"><MapPin class="h-3 w-3" />{{ $t(post.user.profile.country) }}</span>
                            <span :title="formatDate(post.created_at)">{{ timeAgo(post.created_at) }}</span>
                            <span v-if="new Date(post.updated_at) - new Date(post.created_at) > 60000" class="italic">{{ $t('(modifié)') }}</span>
                        </p>
                    </div>
                    <div v-if="can.update || can.delete" class="flex gap-1">
                        <Link v-if="can.update" :href="route('posts.edit', post.id)" class="btn-secondary !px-3 !py-2" :title="$t(`Modifier`)"><Pencil class="h-4 w-4" /><span class="hidden sm:inline">{{ $t('Modifier') }}</span></Link>
                        <button v-if="can.delete" @click="destroy" class="btn-secondary !px-3 !py-2 !text-rose-600 hover:!bg-rose-50" :title="$t(`Supprimer`)"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </div>

                <div class="mt-4 flex justify-end"><TranslateButton :target="() => postBody" :watch-key="post.updated_at" /></div>
                <div ref="postBody" class="prose-devlink mt-2" v-highlight v-html="post.content_html" />
                <ImageGallery :images="images" class="mt-6" />
                <DocumentList :documents="documents" class="mt-4" />

                <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-5">
                    <LikeButton :post-id="post.id" :liked="post.liked" :count="post.likes_count" large />
                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-muted"><MessageCircle class="h-4 w-4" /> {{ $t('{n} réponse(s)', { n: post.comments_count }) }}</span>
                </div>
            </article>

            <!-- Réponses -->
            <section id="reponses" class="card scroll-mt-24 p-6 sm:p-8">
                <h2 class="text-lg font-extrabold text-night">{{ $t('{n} réponse(s)', { n: comments.length }) }}</h2>

                <div v-if="comments.length" class="mt-5 space-y-4">
                    <CommentCard v-for="c in comments" :key="c.id" :comment="c" :is-author="c.user_id === post.user_id" />
                </div>
                <p v-else class="mt-3 text-sm text-muted">{{ $t("Aucune réponse pour l'instant. Partagez votre expertise !") }}</p>

                <form @submit.prevent="reply" class="mt-6 flex gap-3 border-t border-slate-100 pt-6">
                    <UserAvatar :user="me" size="sm" class="hidden sm:inline-flex" />
                    <div class="flex-1">
                        <label for="reply" class="sr-only">{{ $t('Votre réponse') }}</label>
                        <EditorToolbar v-model="form.content" :target="replyBox" />
                        <textarea id="reply" ref="replyBox" v-model="form.content" @paste="onPaste" rows="4" class="input !rounded-t-none" :placeholder="$t(`Écrivez une réponse utile… (Markdown pris en charge)`)" />
                        <p v-if="form.errors.content" class="mt-1 text-xs text-rose-600">{{ form.errors.content }}</p>
                        <SecretWarning v-model="form.content" class="mt-2" />
                        <ImagePicker ref="picker" v-model="form.images" :max="2" compact :error="imageError()" class="mt-2" @other-files="docPicker.addFiles($event)" />
                        <DocumentPicker ref="docPicker" v-model="form.documents" :max="1" :error="docError()" class="mt-2" />
                        <div class="mt-2 flex justify-end">
                            <button class="btn-primary" :disabled="form.processing || !form.content.trim()"><Send class="h-4 w-4" /> {{ $t('Répondre') }}</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>

        <template #aside>
            <section class="card p-5 text-center">
                <Link :href="route('developers.show', post.user.id)" class="inline-block"><UserAvatar :user="post.user" size="lg" /></Link>
                <p class="mt-2 font-bold text-night">{{ post.user.name }}</p>
                <p class="text-xs text-muted">{{ post.user.profile?.headline }}</p>
                <div v-if="post.user.profile?.skills?.length" class="mt-3 flex flex-wrap justify-center gap-1">
                    <span v-for="s in post.user.profile.skills.slice(0, 5)" :key="s" class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">{{ s }}</span>
                </div>
                <Link :href="route('developers.show', post.user.id)" class="btn-secondary mt-4 w-full !py-2">{{ $t('Voir le profil') }}</Link>
            </section>

            <section v-if="related.length" class="card p-5">
                <h3 class="text-sm font-bold text-night">{{ $t('Dans la même catégorie') }}</h3>
                <ul class="mt-3 space-y-3">
                    <li v-for="r in related" :key="r.id">
                        <Link :href="route('posts.show', r.id)" class="block text-sm font-semibold leading-snug text-slate-700 hover:text-brand-600">{{ r.title }}</Link>
                        <p class="text-xs text-muted">{{ $t('{n} réponse(s)', { n: r.comments_count }) }} · {{ timeAgo(r.created_at) }}</p>
                    </li>
                </ul>
            </section>
        </template>
    </AuthenticatedLayout>
</template>
