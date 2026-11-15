<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { X, ChevronLeft, ChevronRight, Expand } from 'lucide-vue-next';

const props = defineProps({
    images: { type: Array, default: () => [] },
    size: { type: String, default: 'lg' }, // lg = publication, sm = réponse / miniature
});

const open = ref(-1);

function onKey(e) {
    if (open.value < 0) return;
    if (e.key === 'Escape') open.value = -1;
    if (e.key === 'ArrowRight') next(1);
    if (e.key === 'ArrowLeft') next(-1);
}
function next(step) {
    open.value = (open.value + step + props.images.length) % props.images.length;
}
watch(open, (v) => {
    document.body.style.overflow = v >= 0 ? 'hidden' : '';
    v >= 0 ? window.addEventListener('keydown', onKey) : window.removeEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
});
</script>

<template>
    <div v-if="images.length">
        <div class="grid gap-2" :class="size === 'lg' && images.length > 1 ? 'sm:grid-cols-2' : 'grid-cols-1'">
            <button
                v-for="(img, i) in images"
                :key="img.id"
                type="button"
                @click="open = i"
                class="group relative block overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200"
                :class="size === 'lg' ? (images.length > 1 ? 'aspect-video' : 'max-h-[28rem]') : 'h-28 w-44'"
                :aria-label="$t('Agrandir la capture {n}', { n: i + 1 })"
            >
                <img :src="img.url" alt="" loading="lazy" class="h-full w-full" :class="size === 'lg' && images.length === 1 ? 'object-contain' : 'object-cover object-top'" />
                <span class="absolute right-2 top-2 rounded-lg bg-night/70 p-1.5 text-white opacity-0 transition group-hover:opacity-100"><Expand class="h-4 w-4" /></span>
            </button>
        </div>

        <Teleport to="body">
            <div v-if="open >= 0" class="fixed inset-0 z-[60] flex items-center justify-center bg-night/90 p-4" @click.self="open = -1" role="dialog" aria-modal="true">
                <img :src="images[open].url" alt="" class="max-h-full max-w-full rounded-lg bg-white object-contain shadow-2xl" />
                <button type="button" @click="open = -1" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" :aria-label="$t('Fermer')"><X class="h-6 w-6" /></button>
                <template v-if="images.length > 1">
                    <button type="button" @click="next(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" :aria-label="$t('Précédente')"><ChevronLeft class="h-6 w-6" /></button>
                    <button type="button" @click="next(1)" class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" :aria-label="$t('Suivante')"><ChevronRight class="h-6 w-6" /></button>
                    <span class="absolute bottom-4 text-sm font-semibold text-white">{{ open + 1 }} / {{ images.length }}</span>
                </template>
                <a :href="images[open].url" target="_blank" rel="noopener" class="absolute bottom-4 right-4 text-xs font-semibold text-slate-300 hover:text-white">{{ $t('Ouvrir l’original') }}</a>
            </div>
        </Teleport>
    </div>
</template>
