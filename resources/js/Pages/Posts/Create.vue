<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostForm from '@/Components/PostForm.vue';

const props = defineProps({ categories: Array, types: Object, preset: Object });

const form = useForm({ title: '', type: props.preset.type, category_id: null, content: '', images: [], documents: [], remove_images: [] });
const submit = () => form.post(route('posts.store'), { forceFormData: true });
</script>

<template>
    <Head :title="$t(`Nouvelle publication`)" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-5">
            <Link :href="route('dashboard')" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-night"><ArrowLeft class="h-4 w-4" /> {{ $t('Retour au fil') }}</Link>
            <div>
                <h1 class="text-2xl font-extrabold text-night">{{ $t('Nouvelle publication') }}</h1>
                <p class="text-sm text-muted">{{ $t('Un bon titre et un contexte clair attirent de meilleures réponses.') }}</p>
            </div>
            <PostForm :form="form" :categories="categories" :types="types" :submit-label="$t(`Publier`)" @submit="submit">
                <template #secondary>
                    <Link :href="route('dashboard')" class="btn-secondary">{{ $t('Annuler') }}</Link>
                </template>
            </PostForm>
        </div>
    </AuthenticatedLayout>
</template>
