import { usePage } from '@inertiajs/vue3';
import en from './lang/en.json';
import fr from './lang/fr.json';

/**
 * Traduction légère : le texte français sert de clé, l'anglais est dans lang/en.json.
 * Paramètres : t('Bonjour {name} 👋', { name: 'Awa' }). Pluriel : valeur « un|plusieurs » + paramètre n.
 */
const dictionaries = { en, fr };

let page = null;

/** Langue courante, réactive : un changement de langue redessine les textes. */
export function currentLocale() {
    page ??= usePage();
    return page.props?.locale ?? 'fr';
}

export function t(text, params = {}) {
    const locale = currentLocale();
    let out = dictionaries[locale]?.[text] ?? text;
    // Singulier|pluriel selon {n} : « 1 réponse », « 0 réponse », « 3 réponses » / « 1 reply », « 0 replies »
    if (out.includes('|') && typeof params.n === 'number') {
        const [one, many] = out.split('|');
        const singular = locale === 'fr' ? params.n <= 1 : params.n === 1;
        out = singular ? one : many;
    }
    for (const [k, v] of Object.entries(params)) out = out.replaceAll(`{${k}}`, v);
    return out;
}

export default {
    install(app) {
        app.config.globalProperties.$t = t;
    },
};
