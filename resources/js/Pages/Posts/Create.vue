<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostForm from '@/Components/PostForm.vue';

const props = defineProps({ categories: Array, types: Object, preset: Object });

const form = useForm({ title: '', type: props.preset.type, category_id: null, content: '' });
const submit = () => form.post(route('posts.store'));
</script>

<template>
    <Head title="Nouvelle publication" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl space-y-5">
            <Link :href="route('dashboard')" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-night"><ArrowLeft class="h-4 w-4" /> Retour au fil</Link>
            <div>
                <h1 class="text-2xl font-extrabold text-night">Nouvelle publication</h1>
                <p class="text-sm text-muted">Un bon titre et un contexte clair attirent de meilleures réponses.</p>
            </div>
            <PostForm :form="form" :categories="categories" :types="types" submit-label="Publier" @submit="submit">
                <template #secondary>
                    <Link :href="route('dashboard')" class="btn-secondary">Annuler</Link>
                </template>
            </PostForm>
        </div>
    </AuthenticatedLayout>
</template>
