<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ category: { type: Object, required: true }, link: { type: Boolean, default: true } });
</script>

<template>
    <component
        :is="link ? Link : 'span'"
        :href="link ? route('explore', { category: category.slug }) : undefined"
        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
    >
        <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: category.color }" />
        {{ $t(category.name) }}
    </component>
</template>
