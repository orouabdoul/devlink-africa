<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { ImagePlus, X, LoaderCircle } from 'lucide-vue-next';
import { compressImage } from '@/images';
import { t } from '@/i18n';

/**
 * Sélection de captures : bouton, glisser-déposer ou Ctrl+V (via addFiles, appelé par le parent).
 * v-model = nouvelles images (File[]) ; v-model:removed = ids des captures existantes à retirer.
 */
const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    existing: { type: Array, default: () => [] },
    removed: { type: Array, default: () => [] },
    max: { type: Number, default: 4 },
    compact: { type: Boolean, default: false },
    error: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue', 'update:removed', 'other-files']);

const input = ref(null);
const dragging = ref(false);
const busy = ref(0);
const localError = ref('');
const previews = new Map();

const kept = computed(() => props.existing.filter((a) => !props.removed.includes(a.id)));
const count = computed(() => kept.value.length + props.modelValue.length);
const full = computed(() => count.value >= props.max);

function previewOf(file) {
    if (!previews.has(file)) previews.set(file, URL.createObjectURL(file));
    return previews.get(file);
}

async function addFiles(files) {
    localError.value = '';
    const images = [...files].filter((f) => f.type.startsWith('image/'));
    const others = [...files].filter((f) => !f.type.startsWith('image/'));
    if (others.length) emit('other-files', others); // PDF, Word… → sélection de documents
    if (!images.length) return;
    const room = props.max - count.value;
    if (images.length > room) {
        localError.value = t('{n} capture(s) maximum.', { n: props.max });
    }
    const added = [];
    for (const file of images.slice(0, Math.max(0, room))) {
        busy.value++;
        try {
            added.push(await compressImage(file));
        } catch {
            localError.value = t('Cette image ne peut pas être lue. Essayez une photo au format JPG ou PNG.');
        } finally {
            busy.value--;
        }
    }
    if (added.length) emit('update:modelValue', [...props.modelValue, ...added]);
}

function removeNew(i) {
    const list = [...props.modelValue];
    const [file] = list.splice(i, 1);
    if (previews.has(file)) {
        URL.revokeObjectURL(previews.get(file));
        previews.delete(file);
    }
    emit('update:modelValue', list);
}

function removeExisting(id) {
    emit('update:removed', [...props.removed, id]);
}

function onDrop(e) {
    dragging.value = false;
    addFiles(e.dataTransfer?.files ?? []);
}

onBeforeUnmount(() => previews.forEach((url) => URL.revokeObjectURL(url)));

defineExpose({ addFiles });
</script>

<template>
    <div
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop"
        class="rounded-xl border-2 border-dashed p-3 transition"
        :class="dragging ? 'border-brand-500 bg-brand-50' : 'border-slate-200'"
    >
        <div class="flex flex-wrap items-center gap-3">
            <div v-for="a in kept" :key="'e' + a.id" class="group relative">
                <img :src="a.url" alt="" class="object-cover ring-1 ring-slate-200" :class="compact ? 'h-14 w-20 rounded-lg' : 'h-20 w-28 rounded-lg'" />
                <button type="button" @click="removeExisting(a.id)" class="absolute -right-2 -top-2 rounded-full bg-night p-1 text-white shadow hover:bg-rose-600" :aria-label="$t('Retirer la capture')">
                    <X class="h-3 w-3" />
                </button>
            </div>
            <div v-for="(file, i) in modelValue" :key="'n' + i" class="group relative">
                <img :src="previewOf(file)" alt="" class="object-cover ring-2 ring-brand-500" :class="compact ? 'h-14 w-20 rounded-lg' : 'h-20 w-28 rounded-lg'" />
                <button type="button" @click="removeNew(i)" class="absolute -right-2 -top-2 rounded-full bg-night p-1 text-white shadow hover:bg-rose-600" :aria-label="$t('Retirer la capture')">
                    <X class="h-3 w-3" />
                </button>
            </div>
            <span v-if="busy" class="inline-flex items-center gap-1.5 text-xs text-muted"><LoaderCircle class="h-4 w-4 animate-spin" />{{ $t('Préparation…') }}</span>

            <button v-if="!full" type="button" @click="input.click()" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50">
                <ImagePlus class="h-4 w-4" /> {{ $t('Ajouter une capture') }}
            </button>
            <input ref="input" type="file" accept="image/png,image/jpeg,image/webp" multiple class="sr-only" @change="addFiles($event.target.files); $event.target.value = ''" />
            <span class="ml-auto text-xs text-muted">{{ count }}/{{ max }}</span>
        </div>
        <p v-if="!compact && !count" class="mt-1 text-xs text-muted">
            {{ $t('Glissez une image ou un document ici, ou collez une capture avec Ctrl+V dans le texte.') }}
        </p>
        <p v-if="localError || error" class="mt-1 text-xs text-rose-600">{{ localError || error }}</p>
    </div>
</template>
