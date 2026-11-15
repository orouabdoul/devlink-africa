<script setup>
import { computed, ref, watch } from 'vue';
import { Languages, LoaderCircle } from 'lucide-vue-next';
import { t, currentLocale } from '@/i18n';

/**
 * Traduit le contenu d'un membre (publication ou réponse) dans la langue de l'interface.
 * 1. Chrome ordinateur (138+) : traduction intégrée au navigateur, sur l'appareil, gratuite.
 *    Seul le texte est traduit : les blocs de code restent intacts.
 * 2. Sinon (mobile, Firefox, Safari…) : ouverture de Google Traduction dans un nouvel onglet.
 */
const props = defineProps({
    target: { type: Function, required: true }, // () => élément HTML qui contient le contenu affiché
    watchKey: { default: null }, // change quand le contenu change (réinitialise la traduction)
});

const state = ref('idle'); // idle | working | done
const progress = ref(0);
const note = ref('');
let originals = [];

const to = computed(() => currentLocale());
const label = computed(() => {
    if (state.value === 'working') return progress.value ? t('Téléchargement du traducteur… {p} %', { p: progress.value }) : t('Traduction…');
    if (state.value === 'done') return t("Voir l'original");
    return to.value === 'en' ? 'Translate to English' : 'Traduire en français';
});

watch(() => props.watchKey, () => {
    state.value = 'idle';
    originals = [];
});

/** Nœuds de texte à traduire : tout sauf le code. */
function textNodes(root) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode: (n) =>
            !n.nodeValue.trim() || n.parentElement.closest('pre, code, button') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT,
    });
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    return nodes;
}

function openGoogle(root) {
    // Texte lisible sans les blocs de code, un paragraphe par ligne.
    const clone = root.cloneNode(true);
    clone.querySelectorAll('pre, button').forEach((el) => el.remove());
    clone.querySelectorAll('p, li, h1, h2, h3, h4, blockquote').forEach((el) => el.append('\n'));
    const text = clone.textContent.replace(/[ \t]+/g, ' ').replace(/\n\s*\n+/g, '\n').trim().slice(0, 4500);
    const url = `https://translate.google.com/?sl=auto&tl=${to.value}&op=translate&text=${encodeURIComponent(text)}`;
    window.open(url, '_blank', 'noopener');
}

async function detectSource(text) {
    try {
        if ('LanguageDetector' in self) {
            const detector = await self.LanguageDetector.create();
            const [best] = await detector.detect(text);
            if (best?.detectedLanguage && best.detectedLanguage !== 'und' && best.confidence > 0.5) {
                return best.detectedLanguage.slice(0, 2);
            }
        }
    } catch {
        /* détection indisponible : on suppose l'autre langue de la plateforme */
    }
    return to.value === 'en' ? 'fr' : 'en';
}

async function toggle() {
    const root = props.target();
    if (!root) return;
    note.value = '';

    if (state.value === 'done') {
        textNodes(root).forEach((n, i) => originals[i] !== undefined && (n.nodeValue = originals[i]));
        state.value = 'idle';
        return;
    }

    if (!('Translator' in self)) return openGoogle(root);

    state.value = 'working';
    progress.value = 0;
    try {
        const nodes = textNodes(root);
        const from = await detectSource(nodes.map((n) => n.nodeValue).join(' ').slice(0, 2000));
        if (from === to.value) {
            note.value = t('Ce texte est déjà dans votre langue.');
            state.value = 'idle';
            return;
        }
        const options = { sourceLanguage: from, targetLanguage: to.value };
        if ((await self.Translator.availability(options)) === 'unavailable') throw new Error('unavailable');
        const translator = await self.Translator.create({
            ...options,
            monitor(m) {
                m.addEventListener('downloadprogress', (e) => (progress.value = Math.round(e.loaded * 100)));
            },
        });
        originals = nodes.map((n) => n.nodeValue);
        for (const n of nodes) {
            const raw = n.nodeValue;
            const lead = raw.match(/^\s*/)[0];
            const trail = raw.match(/\s*$/)[0];
            n.nodeValue = lead + (await translator.translate(raw.trim())) + trail;
        }
        state.value = 'done';
    } catch {
        // Traducteur intégré indisponible : solution de secours.
        state.value = 'idle';
        openGoogle(root);
    }
}
</script>

<template>
    <span class="inline-flex items-center gap-2">
        <button
            type="button"
            @click="toggle"
            :disabled="state === 'working'"
            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 disabled:opacity-60"
        >
            <LoaderCircle v-if="state === 'working'" class="h-3.5 w-3.5 animate-spin" />
            <Languages v-else class="h-3.5 w-3.5" />
            {{ label }}
        </button>
        <span v-if="note" class="text-xs text-muted">{{ note }}</span>
    </span>
</template>
