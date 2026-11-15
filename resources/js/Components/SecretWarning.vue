<script setup>
import { computed } from 'vue';
import { ShieldAlert } from 'lucide-vue-next';
import { findSecrets, maskSecrets } from '@/secrets';

const props = defineProps({ modelValue: { type: String, default: '' } });
const emit = defineEmits(['update:modelValue']);

const secrets = computed(() => findSecrets(props.modelValue));
</script>

<template>
    <div v-if="secrets.length" role="alert" class="flex gap-3 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900">
        <ShieldAlert class="h-5 w-5 shrink-0 text-amber-600" />
        <div class="min-w-0 flex-1">
            <p class="font-bold">{{ $t('Attention : votre texte semble contenir un mot de passe ou une clé secrète.') }}</p>
            <ul class="mt-1 space-y-0.5">
                <li v-for="s in secrets.slice(0, 3)" :key="s" class="truncate font-mono text-xs">{{ s }}</li>
            </ul>
            <button type="button" @click="emit('update:modelValue', maskSecrets(modelValue))" class="mt-2 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700">
                {{ $t('Masquer automatiquement') }}
            </button>
        </div>
    </div>
</template>
