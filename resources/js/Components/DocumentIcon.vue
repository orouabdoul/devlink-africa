<script setup>
import { computed } from 'vue';
import { FileText, FileSpreadsheet, Presentation, FileCode, File } from 'lucide-vue-next';

const props = defineProps({ ext: { type: String, default: '' } });
const meta = computed(() => {
    const e = props.ext.toLowerCase();
    if (e === 'pdf') return { icon: FileText, cls: 'bg-rose-50 text-rose-600' };
    if (['docx', 'odt'].includes(e)) return { icon: FileText, cls: 'bg-brand-50 text-brand-600' };
    if (['xlsx', 'ods', 'csv'].includes(e)) return { icon: FileSpreadsheet, cls: 'bg-green-50 text-green-600' };
    if (['pptx', 'odp'].includes(e)) return { icon: Presentation, cls: 'bg-orange-50 text-orange-600' };
    if (['json', 'log', 'md', 'txt'].includes(e)) return { icon: FileCode, cls: 'bg-slate-100 text-slate-600' };
    return { icon: File, cls: 'bg-slate-100 text-slate-600' };
});
</script>

<template>
    <span class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg" :class="meta.cls">
        <component :is="meta.icon" class="h-4 w-4" />
        <span class="mt-0.5 text-[9px] font-bold uppercase leading-none">{{ ext }}</span>
    </span>
</template>
