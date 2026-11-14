<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Heart } from 'lucide-vue-next';

const props = defineProps({
    postId: { type: Number, required: true },
    liked: { type: Boolean, default: false },
    count: { type: Number, default: 0 },
    large: { type: Boolean, default: false },
});

// Mise à jour optimiste : l'interface réagit immédiatement, le serveur confirme ensuite.
const isLiked = ref(props.liked);
const total = ref(props.count);
const busy = ref(false);
watch(() => [props.liked, props.count], ([l, c]) => {
    isLiked.value = l;
    total.value = c;
});

function toggle() {
    if (busy.value) return;
    busy.value = true;
    isLiked.value = !isLiked.value;
    total.value += isLiked.value ? 1 : -1;
    router.post(route('posts.like', props.postId), {}, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            isLiked.value = !isLiked.value;
            total.value += isLiked.value ? 1 : -1;
        },
        onFinish: () => (busy.value = false),
    });
}
</script>

<template>
    <button
        type="button"
        @click="toggle"
        :aria-pressed="isLiked"
        :title="isLiked ? $t(`Retirer mon J'aime`) : $t(`J'aime`)"
        class="inline-flex items-center gap-1.5 rounded-full font-semibold transition"
        :class="[
            large ? 'px-4 py-2 text-sm ring-1' : 'px-2.5 py-1.5 text-sm',
            isLiked ? 'bg-rose-50 text-rose-600 ring-rose-200' : 'text-muted ring-slate-200 hover:bg-slate-100 hover:text-night',
        ]"
    >
        <Heart class="h-4 w-4 transition" :class="isLiked ? 'scale-110 fill-rose-500 text-rose-500' : ''" />
        <span>{{ total }}</span>
        <span v-if="large">{{ isLiked ? $t('Aimé') : $t("J'aime") }}</span>
    </button>
</template>
