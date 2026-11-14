<script setup>
import { computed } from 'vue';
import { CircleHelp, MessagesSquare, BookOpen } from 'lucide-vue-next';
import { POST_TYPES } from '@/utils';

const props = defineProps({ type: { type: String, required: true } });
const icons = { question: CircleHelp, discussion: MessagesSquare, resource: BookOpen };
const meta = computed(() => POST_TYPES[props.type] ?? POST_TYPES.discussion);
</script>

<template>
    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset" :class="meta.classes">
        <component :is="icons[type]" class="h-3.5 w-3.5" />
        {{ $t(meta.label) }}
    </span>
</template>
