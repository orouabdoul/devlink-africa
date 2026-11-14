<script setup>
import { computed } from 'vue';

const props = defineProps({
    user: { type: Object, required: true },
    size: { type: String, default: 'md' },
});

const sizes = {
    xs: 'h-7 w-7 text-[11px]',
    sm: 'h-9 w-9 text-xs',
    md: 'h-11 w-11 text-sm',
    lg: 'h-16 w-16 text-lg',
    xl: 'h-28 w-28 text-3xl',
};
const palette = ['bg-brand-600', 'bg-emerald-600', 'bg-amber-500', 'bg-rose-500', 'bg-violet-600', 'bg-cyan-600', 'bg-orange-500'];

const url = computed(() => props.user.avatar_url ?? props.user.profile?.avatar_url ?? null);
const initials = computed(() =>
    (props.user.name ?? '?')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join(''),
);
const color = computed(() => palette[(props.user.id ?? 0) % palette.length]);
</script>

<template>
    <img v-if="url" :src="url" :alt="user.name" class="shrink-0 rounded-full object-cover ring-2 ring-white" :class="sizes[size]" />
    <span
        v-else
        class="inline-flex shrink-0 select-none items-center justify-center rounded-full font-bold text-white ring-2 ring-white"
        :class="[sizes[size], color]"
        :aria-label="user.name"
        >{{ initials }}</span
    >
</template>
