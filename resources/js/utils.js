import { t, currentLocale } from '@/i18n';

/** « il y a 3 heures », « hier », etc. */
export function timeAgo(date) {
    if (!date) return '';
    const rtf = new Intl.RelativeTimeFormat(currentLocale(), { numeric: 'auto' });
    const seconds = Math.round((new Date(date).getTime() - Date.now()) / 1000);
    const units = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
    ];
    for (const [unit, s] of units) {
        if (Math.abs(seconds) >= s) return rtf.format(Math.round(seconds / s), unit);
    }
    return t("à l'instant");
}

export function formatDate(date) {
    return new Date(date).toLocaleDateString(currentLocale() === 'en' ? 'en-GB' : 'fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}

export function plural(n, singular, pluralForm = null) {
    return `${n} ${n > 1 ? pluralForm ?? singular + 's' : singular}`;
}

export const POST_TYPES = {
    question: { label: 'Question', classes: 'bg-amber-50 text-amber-700 ring-amber-200' },
    discussion: { label: 'Discussion', classes: 'bg-brand-50 text-brand-700 ring-brand-200' },
    resource: { label: 'Ressource', classes: 'bg-green-50 text-green-700 ring-green-200' },
};

export function formatSize(bytes) {
    if (!bytes && bytes !== 0) return '';
    const en = currentLocale() === 'en';
    if (bytes < 1024) return `${bytes} ${en ? 'B' : 'o'}`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} ${en ? 'KB' : 'Ko'}`;
    const mb = (bytes / 1024 / 1024).toFixed(1);
    return en ? `${mb} MB` : `${mb.replace('.', ',')} Mo`;
}

export const DOCUMENT_EXTENSIONS = ['pdf', 'docx', 'pptx', 'xlsx', 'odt', 'odp', 'ods', 'txt', 'md', 'csv', 'json', 'log'];
