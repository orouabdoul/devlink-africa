<script setup>
defineProps({ dark: { type: Boolean, default: false }, compact: { type: Boolean, default: false } });
</script>

<template>
    <span class="inline-flex items-center gap-2.5">
        <svg viewBox="0 0 64 64" class="h-9 w-9 shrink-0" aria-hidden="true">
            <rect width="64" height="64" rx="16" fill="#2563EB" />
            <circle cx="22" cy="32" r="9" fill="none" stroke="#fff" stroke-width="6" />
            <circle cx="42" cy="32" r="9" fill="none" stroke="#fff" stroke-width="6" />
            <rect x="8" y="54" width="12" height="4" fill="#F59E0B" />
            <rect x="20" y="54" width="12" height="4" fill="#16A34A" />
            <rect x="32" y="54" width="12" height="4" fill="#DC2626" />
            <rect x="44" y="54" width="12" height="4" fill="#111827" />
        </svg>
        <span v-if="!compact" class="text-lg font-extrabold leading-none tracking-tight" :class="dark ? 'text-white' : 'text-night'">
            DevLink<span class="text-brand-600" :class="{ 'text-brand-200': dark }"> Africa</span>
        </span>
    </span>
</template>
