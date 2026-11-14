<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

defineProps({ dark: { type: Boolean, default: false } });

const page = usePage();
const locale = computed(() => page.props.locale ?? 'fr');
const langs = [
    { code: 'fr', label: 'FR', name: 'Français' },
    { code: 'en', label: 'EN', name: 'English' },
];

function choose(code) {
    if (code === locale.value) return;
    router.post(route('locale.update'), { locale: code }, {
        preserveScroll: true,
        onSuccess: () => (document.documentElement.lang = code),
    });
}
</script>

<template>
    <div role="group" :aria-label="$t('Langue')" class="inline-flex shrink-0 rounded-lg p-0.5 text-xs font-bold" :class="dark ? 'bg-white/10' : 'bg-slate-100'">
        <button
            v-for="l in langs"
            :key="l.code"
            type="button"
            @click="choose(l.code)"
            :title="l.name"
            :lang="l.code"
            :aria-pressed="locale === l.code"
            class="rounded-md px-2 py-1 transition"
            :class="
                locale === l.code
                    ? dark
                        ? 'bg-white text-night'
                        : 'bg-white text-brand-700 shadow-sm'
                    : dark
                      ? 'text-slate-300 hover:text-white'
                      : 'text-slate-500 hover:text-night'
            "
        >
            {{ l.label }}
        </button>
    </div>
</template>
