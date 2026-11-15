<script setup>
import { Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';

defineProps({ developer: { type: Object, required: true } });
</script>

<template>
    <Link :href="route('developers.show', developer.id)" class="card group flex flex-col p-5 transition hover:border-brand-200 hover:shadow-md">
        <div class="flex items-center gap-3">
            <UserAvatar :user="developer" size="md" />
            <div class="min-w-0">
                <p class="truncate font-bold text-night group-hover:text-brand-600">{{ developer.name }}</p>
                <p class="truncate text-xs text-muted">{{ developer.profile?.headline || $t('Développeur·se') }}</p>
            </div>
        </div>
        <p v-if="developer.profile?.country" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-slate-600">
            <MapPin class="h-3.5 w-3.5 text-brand-600" />{{ $t(developer.profile.country) }}
        </p>
        <div v-if="developer.profile?.skills?.length" class="mt-3 flex flex-wrap gap-1.5">
            <span v-for="skill in developer.profile.skills.slice(0, 4)" :key="skill" class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                {{ skill }}
            </span>
        </div>
        <p v-if="developer.posts_count !== undefined" class="mt-auto pt-3 text-xs text-muted">{{ $t('{n} publication(s)', { n: developer.posts_count }) }}</p>
    </Link>
</template>
