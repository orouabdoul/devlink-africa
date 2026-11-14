<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostForm from '@/Components/PostForm.vue';

const props = defineProps({ post: Object, categories: Array, types: Object });

const form = useForm({ ...props.post });
const submit = () => form.put(route('posts.update', props.post.id));
</script>

<template>
    <Head :title="$t(`Modifier la publication`)" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-5">
            <Link :href="route('posts.show', post.id)" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-night"><ArrowLeft class="h-4 w-4" /> {{ $t('Retour à la publication') }}</Link>
            <h1 class="text-2xl font-extrabold text-night">{{ $t('Modifier la publication') }}</h1>
            <PostForm :form="form" :categories="categories" :types="types" :submit-label="$t(`Enregistrer les modifications`)" @submit="submit">
                <template #secondary>
                    <Link :href="route('posts.show', post.id)" class="btn-secondary">{{ $t('Annuler') }}</Link>
                </template>
            </PostForm>
        </div>
    </AuthenticatedLayout>
</template>
