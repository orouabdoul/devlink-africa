<script setup>
import { nextTick, ref } from 'vue';
import { Code, Terminal, ClipboardList } from 'lucide-vue-next';
import { t } from '@/i18n';

/**
 * Barre d'outils au-dessus d'une zone de texte : insérer un bloc de code (avec langage),
 * un bloc d'erreur, et — pour les questions — le modèle « question d'erreur ».
 */
const props = defineProps({
    modelValue: { type: String, default: '' },
    target: { type: Object, default: null }, // la <textarea>
    showTemplate: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue']);

const languages = [
    ['php', 'PHP'], ['javascript', 'JavaScript'], ['typescript', 'TypeScript'], ['dart', 'Dart / Flutter'],
    ['python', 'Python'], ['java', 'Java'], ['kotlin', 'Kotlin'], ['sql', 'SQL'], ['bash', 'Terminal / Bash'],
    ['json', 'JSON'], ['html', 'HTML / Blade'], ['css', 'CSS'], ['yaml', 'YAML'], ['text', t('Texte brut')],
];
const lang = ref('php');

/** Insère `block` à la position du curseur et place le curseur à `caret` caractères du début du bloc. */
async function insert(block, caret) {
    const el = props.target;
    const value = props.modelValue ?? '';
    const start = el ? el.selectionStart : value.length;
    const end = el ? el.selectionEnd : value.length;
    const selected = value.slice(start, end);
    const before = value.slice(0, start);
    const lead = before && !before.endsWith('\n\n') ? (before.endsWith('\n') ? '\n' : '\n\n') : '';
    const text = typeof block === 'function' ? block(selected) : block;
    emit('update:modelValue', before + lead + text + value.slice(end));
    await nextTick();
    if (el) {
        const pos = start + lead.length + (typeof caret === 'function' ? caret(selected) : caret);
        el.focus();
        el.setSelectionRange(pos, pos);
    }
}

function insertCode() {
    const fence = '```' + lang.value + '\n';
    insert((sel) => fence + sel + '\n```\n', (sel) => fence.length + sel.length);
}

function insertError() {
    const head = '```text\n';
    insert((sel) => head + (sel || '') + '\n```\n', (sel) => head.length + sel.length);
}

function insertTemplate() {
    const tpl = [
        `**${t("Ce que j'essaie de faire")}**`,
        '',
        '',
        '',
        `**${t("L'erreur obtenue")}**`,
        '',
        '```text',
        t("(collez ici le message d'erreur complet)"),
        '```',
        '',
        `**${t("Ce que j'ai déjà essayé")}**`,
        '',
        `1. ${t('…')}`,
        '',
        `**${t('Mon environnement')}**`,
        '',
        `- ${t('Langage / framework et version :')} `,
        `- ${t('Système (Windows, Linux, Android…) :')} `,
        '',
    ].join('\n');
    insert(tpl, tpl.indexOf('\n\n') + 2);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-1.5 rounded-t-xl border border-b-0 border-slate-200 bg-slate-50 px-2 py-1.5">
        <select v-model="lang" class="rounded-lg border-slate-200 py-1 pl-2 pr-7 text-xs font-semibold focus:border-brand-500 focus:ring-brand-500" :aria-label="$t('Langage du code')">
            <option v-for="[code, label] in languages" :key="code" :value="code">{{ label }}</option>
        </select>
        <button type="button" @click="insertCode" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-night hover:bg-white">
            <Code class="h-4 w-4 text-brand-600" /> {{ $t('Insérer du code') }}
        </button>
        <button type="button" @click="insertError" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-night hover:bg-white">
            <Terminal class="h-4 w-4 text-rose-600" /> {{ $t("Bloc d'erreur / log") }}
        </button>
        <button v-if="showTemplate" type="button" @click="insertTemplate" class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-bold text-amber-800 hover:bg-amber-200">
            <ClipboardList class="h-4 w-4" /> {{ $t('Modèle de question d’erreur') }}
        </button>
    </div>
</template>
