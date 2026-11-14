<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ paginator: { type: Object, required: true } });
</script>

<template>
    <nav v-if="paginator.last_page > 1" class="mt-6 flex flex-wrap items-center justify-center gap-1.5" aria-label="Pagination">
        <template v-for="(link, i) in paginator.links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="min-w-[2.5rem] rounded-xl px-3 py-2 text-center text-sm font-semibold transition"
                :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-night ring-1 ring-slate-200 hover:bg-slate-50'"
                v-html="link.label"
            />
            <span v-else class="rounded-xl px-3 py-2 text-sm text-slate-400" v-html="link.label" />
        </template>
    </nav>
</template>
