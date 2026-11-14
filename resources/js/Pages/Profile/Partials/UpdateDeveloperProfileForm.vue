<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { Camera, X, Github, Linkedin, CircleAlert } from 'lucide-vue-next';
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({ profile: Object, countries: Array });
const user = usePage().props.auth.user;

const form = useForm({
    headline: props.profile.headline ?? '',
    country: props.profile.country ?? '',
    bio: props.profile.bio ?? '',
    skills: props.profile.skills ?? [],
    github_url: props.profile.github_url ?? '',
    linkedin_url: props.profile.linkedin_url ?? '',
    avatar: null,
    remove_avatar: false,
});

const preview = ref(null);
const skillInput = ref('');
const suggestions = ['Laravel', 'Vue.js', 'React', 'Flutter', 'Python', 'Node.js', 'Java', 'Docker', 'Figma', 'MySQL'];
const avatarUser = computed(() => ({ ...user, avatar_url: preview.value ?? (form.remove_avatar ? null : props.profile.avatar_url) }));

const photoError = ref('');
const preparing = ref(false);

/** Réduit la photo à 512 px (JPEG) dans le navigateur : envoi rapide, même avec une photo de téléphone de 8 Mo. */
function resizeImage(file, max = 512) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            const scale = Math.min(1, max / Math.max(img.width, img.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(
                (blob) => (blob ? resolve(new File([blob], 'avatar.jpg', { type: 'image/jpeg' })) : reject()),
                'image/jpeg',
                0.85,
            );
            URL.revokeObjectURL(img.src);
        };
        img.onerror = reject;
        img.src = URL.createObjectURL(file);
    });
}

async function onFile(e) {
    const file = e.target.files[0];
    e.target.value = '';
    photoError.value = '';
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        photoError.value = 'Choisissez une image (JPG, PNG ou WebP).';
        return;
    }
    preparing.value = true;
    try {
        const small = await resizeImage(file);
        form.avatar = small;
        form.remove_avatar = false;
        preview.value = URL.createObjectURL(small);
    } catch {
        photoError.value = 'Cette image ne peut pas être lue. Essayez une photo au format JPG ou PNG.';
    } finally {
        preparing.value = false;
    }
}

const errorCount = computed(() => Object.keys(form.errors).length);

function removeAvatar() {
    form.avatar = null;
    form.remove_avatar = true;
    preview.value = null;
}

function addSkill(value = skillInput.value) {
    const skill = value.trim().replace(/,$/, '');
    if (skill && !form.skills.includes(skill) && form.skills.length < 15) form.skills.push(skill);
    skillInput.value = '';
}

function submit() {
    if (skillInput.value.trim()) addSkill();
    form.post(route('profile.developer.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.avatar = null;
            form.remove_avatar = false;
            preview.value = null;
        },
        onError: () => document.getElementById('profile-errors')?.scrollIntoView({ behavior: 'smooth', block: 'center' }),
    });
}
</script>

<template>
    <section>
        <header class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-night">Profil développeur</h2>
                <p class="mt-1 text-sm text-muted">Ce que la communauté voit sur votre page publique.</p>
            </div>
            <Link :href="route('developers.show', user.id)" class="text-sm font-semibold text-brand-600 hover:underline">Voir ma page publique →</Link>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-5">
            <div v-if="errorCount" id="profile-errors" class="flex gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <CircleAlert class="h-5 w-5 shrink-0" />
                <div>
                    <p class="font-bold">Le profil n'a pas été enregistré.</p>
                    <ul class="mt-1 list-disc pl-4">
                        <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <UserAvatar :user="avatarUser" size="lg" />
                <div class="flex flex-wrap gap-2">
                    <label class="btn-secondary cursor-pointer !py-2">
                        <Camera class="h-4 w-4" /> {{ preparing ? 'Préparation…' : 'Changer la photo' }}
                        <input type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" @change="onFile" />
                    </label>
                    <button v-if="avatarUser.avatar_url" type="button" @click="removeAvatar" class="btn-secondary !py-2 !text-rose-600">Retirer</button>
                </div>
            </div>
            <p v-if="photoError || form.errors.avatar" class="text-xs text-rose-600">{{ photoError || form.errors.avatar }}</p>
            <p v-else-if="form.avatar" class="-mt-3 text-xs font-medium text-brand-600">Nouvelle photo prête — cliquez sur « Enregistrer le profil » pour la valider.</p>
            <p v-else class="-mt-3 text-xs text-muted">JPG, PNG ou WebP. La photo est redimensionnée automatiquement.</p>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="headline" class="label">Titre professionnel</label>
                    <input id="headline" v-model="form.headline" type="text" maxlength="120" class="input" placeholder="Ex. : Développeur full-stack Laravel" />
                    <p v-if="form.errors.headline" class="mt-1 text-xs text-rose-600">{{ form.errors.headline }}</p>
                </div>
                <div>
                    <label for="country" class="label">Pays</label>
                    <select id="country" v-model="form.country" class="input">
                        <option value="">Sélectionnez votre pays</option>
                        <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
                    </select>
                    <p v-if="form.errors.country" class="mt-1 text-xs text-rose-600">{{ form.errors.country }}</p>
                </div>
            </div>

            <div>
                <label for="bio" class="label">Présentation</label>
                <textarea id="bio" v-model="form.bio" rows="4" maxlength="1000" class="input" placeholder="Votre parcours, vos projets, ce que vous aimez construire…" />
                <div class="mt-1 flex justify-between text-xs"><span class="text-rose-600">{{ form.errors.bio }}</span><span class="text-muted">{{ form.bio.length }}/1000</span></div>
            </div>

            <div>
                <label for="skills" class="label">Compétences techniques</label>
                <div class="flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white p-2 focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                    <span v-for="(s, i) in form.skills" :key="s" class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-2 py-1 text-xs font-bold text-brand-700">
                        {{ s }}
                        <button type="button" @click="form.skills.splice(i, 1)" :aria-label="`Retirer ${s}`"><X class="h-3 w-3" /></button>
                    </span>
                    <input
                        id="skills"
                        v-model="skillInput"
                        @keydown.enter.prevent="addSkill()"
                        @keydown.,.prevent="addSkill()"
                        type="text"
                        class="min-w-[160px] flex-1 border-0 p-1 text-sm focus:ring-0"
                        placeholder="Tapez puis Entrée"
                    />
                </div>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <button
                        v-for="s in suggestions.filter((x) => !form.skills.includes(x))"
                        :key="s"
                        type="button"
                        @click="addSkill(s)"
                        class="rounded-md border border-dashed border-slate-300 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:border-brand-500 hover:text-brand-600"
                    >
                        + {{ s }}
                    </button>
                </div>
                <p v-if="form.errors.skills" class="mt-1 text-xs text-rose-600">{{ form.errors.skills }}</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="github" class="label flex items-center gap-1.5"><Github class="h-4 w-4" /> GitHub</label>
                    <input id="github" v-model="form.github_url" type="text" inputmode="url" class="input" placeholder="https://github.com/votre-pseudo" />
                    <p v-if="form.errors.github_url" class="mt-1 text-xs text-rose-600">{{ form.errors.github_url }}</p>
                </div>
                <div>
                    <label for="linkedin" class="label flex items-center gap-1.5"><Linkedin class="h-4 w-4" /> LinkedIn</label>
                    <input id="linkedin" v-model="form.linkedin_url" type="text" inputmode="url" class="input" placeholder="https://www.linkedin.com/in/votre-profil" />
                    <p v-if="form.errors.linkedin_url" class="mt-1 text-xs text-rose-600">{{ form.errors.linkedin_url }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="btn-primary" :disabled="form.processing || preparing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer le profil' }}</button>
                <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition">
                    <p v-if="form.recentlySuccessful" class="text-sm font-medium text-success">Profil enregistré.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
