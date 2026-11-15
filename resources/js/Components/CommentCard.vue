<script setup>
import { t } from '@/i18n';
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import TranslateButton from '@/Components/TranslateButton.vue';
import DocumentList from '@/Components/DocumentList.vue';
import { timeAgo } from '@/utils';

const props = defineProps({
    comment: { type: Object, required: true },
    isAuthor: { type: Boolean, default: false },
});

const editing = ref(false);
const body = ref(null);
const form = useForm({ content: props.comment.content });

function save() {
    form.put(route('comments.update', props.comment.id), {
        preserveScroll: true,
        onSuccess: () => (editing.value = false),
    });
}

function destroy() {
    if (confirm(t('Supprimer définitivement cette réponse ?'))) {
        router.delete(route('comments.destroy', props.comment.id), { preserveScroll: true });
    }
}
</script>

<template>
    <div class="flex gap-3">
        <Link :href="route('developers.show', comment.user.id)" class="shrink-0">
            <UserAvatar :user="comment.user" size="sm" />
        </Link>
        <div class="min-w-0 flex-1 rounded-2xl bg-slate-50 px-4 py-3">
            <div class="flex flex-wrap items-center gap-x-2">
                <Link :href="route('developers.show', comment.user.id)" class="text-sm font-bold text-night hover:text-brand-600">{{ comment.user.name }}</Link>
                <span v-if="isAuthor" class="rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-brand-700">{{ $t('Auteur') }}</span>
                <span class="text-xs text-muted">{{ timeAgo(comment.created_at) }}</span>
                <div v-if="comment.can?.update || comment.can?.delete" class="ml-auto flex gap-1">
                    <button v-if="comment.can.update && !editing" @click="editing = true" class="rounded-lg p-1.5 text-muted hover:bg-white hover:text-night" :title="$t(`Modifier`)">
                        <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button v-if="comment.can.delete" @click="destroy" class="rounded-lg p-1.5 text-muted hover:bg-white hover:text-rose-600" :title="$t(`Supprimer`)">
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <form v-if="editing" @submit.prevent="save" class="mt-2 space-y-2">
                <textarea v-model="form.content" rows="3" class="input" />
                <p v-if="form.errors.content" class="text-xs text-rose-600">{{ form.errors.content }}</p>
                <div class="flex gap-2">
                    <button class="btn-primary !py-1.5" :disabled="form.processing">{{ $t('Enregistrer') }}</button>
                    <button type="button" class="btn-secondary !py-1.5" @click="editing = false; form.reset()">{{ $t('Annuler') }}</button>
                </div>
            </form>
            <template v-else>
                <div ref="body" class="prose-devlink prose-sm mt-1 prose-p:my-1" v-highlight v-html="comment.content_html" />
                <TranslateButton :target="() => body" :watch-key="comment.updated_at" class="-ml-2 mt-1" />
            </template>
            <ImageGallery :images="(comment.attachments ?? []).filter((a) => a.kind !== 'document')" size="sm" class="mt-2" />
            <DocumentList :documents="(comment.attachments ?? []).filter((a) => a.kind === 'document')" compact class="mt-2" />
        </div>
    </div>
</template>
