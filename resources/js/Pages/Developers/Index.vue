<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeveloperCard from '@/Components/DeveloperCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({ developers: Object, filters: Object, countries: Array });
const f = reactive({ ...props.filters });

function apply() {
    const params = Object.fromEntries(Object.entries(f).filter(([, v]) => v));
    router.get(route('developers.index'), params, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <Head :title="$t(`Développeurs`)" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-extrabold text-night">{{ $t('Développeurs') }}</h1>
                <p class="text-sm text-muted">{{ $t('Trouvez des développeurs avec qui collaborer, par pays ou par compétence.') }}</p>
            </div>

            <form @submit.prevent="apply" class="card grid gap-3 p-4 sm:grid-cols-[1fr_1fr_auto]">
                <select v-model="f.country" @change="apply" class="input" :aria-label="$t(`Pays`)">
                    <option value="">{{ $t('Tous les pays') }}</option>
                    <option v-for="c in countries" :key="c" :value="c">{{ $t(c) }}</option>
                </select>
                <input v-model="f.skill" type="text" class="input" :placeholder="$t(`Compétence : Flutter, Python…`)" :aria-label="$t(`Compétence`)" />
                <button class="btn-primary">{{ $t('Filtrer') }}</button>
            </form>

            <div v-if="developers.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <DeveloperCard v-for="d in developers.data" :key="d.id" :developer="d" />
            </div>
            <EmptyState v-else :title="$t(`Aucun développeur trouvé`)" :text="$t(`Essayez un autre pays ou une autre compétence.`)" />

            <Pagination :paginator="developers" />
        </div>
    </AuthenticatedLayout>
</template>
