<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';
import { timeAgo } from '@/utils';

const props = defineProps({
    comment: { type: Object, required: true },
    isAuthor: { type: Boolean, default: false },
});

const editing = ref(false);
const form = useForm({ content: props.comment.content });

function save() {
    form.put(route('comments.update', props.comment.id), {
        preserveScroll: true,
        onSuccess: () => (editing.value = false),
    });
}

function destroy() {
    if (confirm('Supprimer définitivement cette réponse ?')) {
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
                <span v-if="isAuthor" class="rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-brand-700">Auteur</span>
                <span class="text-xs text-muted">{{ timeAgo(comment.created_at) }}</span>
                <div v-if="comment.can?.update || comment.can?.delete" class="ml-auto flex gap-1">
                    <button v-if="comment.can.update && !editing" @click="editing = true" class="rounded-lg p-1.5 text-muted hover:bg-white hover:text-night" title="Modifier">
                        <Pencil class="h-3.5 w-3.5" />
                    </button>
                    <button v-if="comment.can.delete" @click="destroy" class="rounded-lg p-1.5 text-muted hover:bg-white hover:text-rose-600" title="Supprimer">
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>

            <form v-if="editing" @submit.prevent="save" class="mt-2 space-y-2">
                <textarea v-model="form.content" rows="3" class="input" />
                <p v-if="form.errors.content" class="text-xs text-rose-600">{{ form.errors.content }}</p>
                <div class="flex gap-2">
                    <button class="btn-primary !py-1.5" :disabled="form.processing">Enregistrer</button>
                    <button type="button" class="btn-secondary !py-1.5" @click="editing = false; form.reset()">Annuler</button>
                </div>
            </form>
            <div v-else class="prose-devlink prose-sm mt-1 prose-p:my-1" v-html="comment.content_html" />
        </div>
    </div>
</template>
