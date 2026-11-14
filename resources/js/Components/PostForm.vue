<script setup>
import { CircleHelp, MessagesSquare, BookOpen } from 'lucide-vue-next';

const props = defineProps({
    form: { type: Object, required: true },
    categories: { type: Array, required: true },
    types: { type: Object, required: true },
    submitLabel: { type: String, default: 'Publier' },
});
const emit = defineEmits(['submit']);

const typeMeta = {
    question: { icon: CircleHelp, hint: 'Vous cherchez de l’aide sur un problème' },
    discussion: { icon: MessagesSquare, hint: 'Vous lancez un échange d’idées' },
    resource: { icon: BookOpen, hint: 'Vous partagez un tutoriel, un outil, un lien' },
};
</script>

<template>
    <form @submit.prevent="emit('submit')" class="card space-y-6 p-6">
        <fieldset>
            <legend class="label">{{ $t('Type de publication') }}</legend>
            <div class="grid gap-3 sm:grid-cols-3">
                <label
                    v-for="(label, key) in types"
                    :key="key"
                    class="flex cursor-pointer flex-col gap-1 rounded-xl border-2 p-3 transition"
                    :class="form.type === key ? 'border-brand-600 bg-brand-50' : 'border-slate-200 hover:border-slate-300'"
                >
                    <input type="radio" v-model="form.type" :value="key" class="sr-only" />
                    <span class="flex items-center gap-2 text-sm font-bold" :class="form.type === key ? 'text-brand-700' : 'text-night'">
                        <component :is="typeMeta[key].icon" class="h-4 w-4" />{{ $t(label) }}
                    </span>
                    <span class="text-xs text-muted">{{ $t(typeMeta[key].hint) }}</span>
                </label>
            </div>
            <p v-if="form.errors.type" class="mt-1 text-xs text-rose-600">{{ form.errors.type }}</p>
        </fieldset>

        <div>
            <label for="title" class="label">{{ $t('Titre') }}</label>
            <input id="title" v-model="form.title" type="text" maxlength="180" class="input" :placeholder="$t(`Ex. : Comment sécuriser une API Laravel avec Sanctum ?`)" />
            <div class="mt-1 flex justify-between text-xs">
                <span class="text-rose-600">{{ form.errors.title }}</span>
                <span class="text-muted">{{ form.title.length }}/180</span>
            </div>
        </div>

        <div>
            <label for="category" class="label">{{ $t('Catégorie') }}</label>
            <select id="category" v-model="form.category_id" class="input">
                <option :value="null" disabled>{{ $t('Choisissez une technologie') }}</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="form.errors.category_id" class="mt-1 text-xs text-rose-600">{{ form.errors.category_id }}</p>
        </div>

        <div>
            <label for="content" class="label">{{ $t('Contenu') }}</label>
            <textarea
                id="content"
                v-model="form.content"
                rows="12"
                class="input font-mono text-[13px] leading-relaxed"
                :placeholder="$t(`Décrivez votre contexte, ce que vous avez essayé, et le résultat attendu…`)"
            />
            <div class="mt-1 flex flex-wrap justify-between gap-2 text-xs">
                <span class="text-rose-600">{{ form.errors.content }}</span>
                <span class="text-muted">{{ $t("Markdown pris en charge : **gras**, `code`, ```bloc de code```, listes, liens.") }}</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
            <slot name="secondary" />
            <button type="submit" class="btn-primary" :disabled="form.processing">{{ form.processing ? $t('Envoi…') : submitLabel }}</button>
        </div>
    </form>
</template>
