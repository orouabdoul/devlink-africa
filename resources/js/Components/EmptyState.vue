<script setup>
import { Inbox } from 'lucide-vue-next';

defineProps({ title: String, text: String });
</script>

<template>
    <div class="card flex flex-col items-center px-6 py-12 text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
            <Inbox class="h-7 w-7" />
        </span>
        <h3 class="mt-4 text-base font-bold text-night">{{ title }}</h3>
        <p class="mt-1 max-w-sm text-sm text-muted">{{ text }}</p>
        <div class="mt-5"><slot /></div>
    </div>
</template>
