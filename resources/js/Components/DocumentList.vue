<script setup>
import { Download, Eye } from 'lucide-vue-next';
import DocumentIcon from '@/Components/DocumentIcon.vue';
import { formatSize } from '@/utils';

defineProps({ documents: { type: Array, default: () => [] }, compact: { type: Boolean, default: false } });
</script>

<template>
    <ul v-if="documents.length" class="grid gap-2" :class="compact ? '' : 'sm:grid-cols-2'">
        <li v-for="d in documents" :key="d.id" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-2.5">
            <DocumentIcon :ext="d.extension" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-night" :title="d.original_name">{{ d.original_name }}</p>
                <p class="text-xs text-muted">{{ formatSize(d.size) }}</p>
            </div>
            <a
                v-if="d.extension === 'pdf'"
                :href="route('attachments.view', d.id)"
                target="_blank"
                rel="noopener"
                class="rounded-lg p-2 text-muted hover:bg-slate-100 hover:text-night"
                :title="$t('Ouvrir')"
                :aria-label="$t('Ouvrir {name}', { name: d.original_name })"
            >
                <Eye class="h-4 w-4" />
            </a>
            <a :href="d.url" class="rounded-lg p-2 text-muted hover:bg-slate-100 hover:text-night" :title="$t('Télécharger')" :aria-label="$t('Télécharger {name}', { name: d.original_name })">
                <Download class="h-4 w-4" />
            </a>
        </li>
    </ul>
</template>
