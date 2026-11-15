<script setup>
import { computed, ref } from 'vue';
import { FileUp, X } from 'lucide-vue-next';
import DocumentIcon from '@/Components/DocumentIcon.vue';
import { formatSize, DOCUMENT_EXTENSIONS } from '@/utils';
import { t } from '@/i18n';

/** Sélection de documents (PDF, Word, Excel, PowerPoint, Markdown, logs…). */
const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    existing: { type: Array, default: () => [] },
    removed: { type: Array, default: () => [] },
    max: { type: Number, default: 3 },
    error: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue', 'update:removed']);

const MAX_BYTES = 5 * 1024 * 1024;
const input = ref(null);
const localError = ref('');
const kept = computed(() => props.existing.filter((a) => !props.removed.includes(a.id)));
const count = computed(() => kept.value.length + props.modelValue.length);
const ext = (name) => name.split('.').pop().toLowerCase();

function addFiles(files) {
    localError.value = '';
    const ok = [];
    for (const f of files) {
        if (!DOCUMENT_EXTENSIONS.includes(ext(f.name))) {
            localError.value = t('Format non accepté : {name}. Formats possibles : PDF, Word, Excel, PowerPoint, OpenDocument, TXT, MD, CSV, JSON, LOG.', { name: f.name });
            continue;
        }
        if (f.size > MAX_BYTES) {
            localError.value = t('{name} dépasse 5 Mo.', { name: f.name });
            continue;
        }
        ok.push(f);
    }
    const room = props.max - count.value;
    if (ok.length > room) localError.value = t('{n} document(s) maximum.', { n: props.max });
    if (ok.length && room > 0) emit('update:modelValue', [...props.modelValue, ...ok.slice(0, room)]);
}

function removeNew(i) {
    const list = [...props.modelValue];
    list.splice(i, 1);
    emit('update:modelValue', list);
}

defineExpose({ addFiles });
</script>

<template>
    <div>
        <ul v-if="count" class="mb-2 grid gap-2 sm:grid-cols-2">
            <li v-for="d in kept" :key="'e' + d.id" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-2.5">
                <DocumentIcon :ext="d.extension" />
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-night">{{ d.original_name }}</span>
                <button type="button" @click="emit('update:removed', [...removed, d.id])" class="rounded-lg p-1.5 text-muted hover:bg-rose-50 hover:text-rose-600" :aria-label="$t('Retirer {name}', { name: d.original_name })">
                    <X class="h-4 w-4" />
                </button>
            </li>
            <li v-for="(f, i) in modelValue" :key="'n' + i" class="flex items-center gap-3 rounded-xl border-2 border-brand-500 bg-brand-50/40 p-2.5">
                <DocumentIcon :ext="ext(f.name)" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-night">{{ f.name }}</span>
                    <span class="text-xs text-muted">{{ formatSize(f.size) }}</span>
                </span>
                <button type="button" @click="removeNew(i)" class="rounded-lg p-1.5 text-muted hover:bg-rose-50 hover:text-rose-600" :aria-label="$t('Retirer {name}', { name: f.name })">
                    <X class="h-4 w-4" />
                </button>
            </li>
        </ul>
        <div class="flex flex-wrap items-center gap-3">
            <button v-if="count < max" type="button" @click="input.click()" class="inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm font-semibold text-brand-700 hover:border-brand-500 hover:bg-brand-50">
                <FileUp class="h-4 w-4" /> {{ $t('Ajouter un document') }}
            </button>
            <span class="text-xs text-muted">{{ $t('PDF, Word, Excel, PowerPoint, Markdown, logs… 5 Mo max.') }} ({{ count }}/{{ max }})</span>
            <input ref="input" type="file" multiple class="sr-only" :accept="DOCUMENT_EXTENSIONS.map((e) => '.' + e).join(',')" @change="addFiles($event.target.files); $event.target.value = ''" />
        </div>
        <p v-if="localError || error" class="mt-1 text-xs text-rose-600">{{ localError || error }}</p>
    </div>
</template>
