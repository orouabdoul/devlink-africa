<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CircleCheck, X } from 'lucide-vue-next';

const page = usePage();
const message = ref(null);
let timer;

watch(
    () => page.props.flash?.success,
    (value) => {
        if (!value) return;
        message.value = value;
        clearTimeout(timer);
        timer = setTimeout(() => (message.value = null), 3500);
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        enter-from-class="translate-y-3 opacity-0"
        enter-active-class="transition duration-200"
        leave-to-class="translate-y-3 opacity-0"
        leave-active-class="transition duration-200"
    >
        <div
            v-if="message"
            role="status"
            class="fixed bottom-20 left-1/2 z-50 flex -translate-x-1/2 items-center gap-3 rounded-2xl bg-night px-4 py-3 text-sm font-medium text-white shadow-xl lg:bottom-6"
        >
            <CircleCheck class="h-5 w-5 text-green-400" />
            {{ message }}
            <button @click="message = null" class="text-slate-400 hover:text-white" aria-label="Fermer"><X class="h-4 w-4" /></button>
        </div>
    </Transition>
</template>
