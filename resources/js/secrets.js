/**
 * Détecte les mots de passe, clés et jetons collés par erreur dans un message d'erreur ou un .env.
 * Chaque règle indique la partie à masquer (la valeur, pas le nom de la variable).
 */
const RULES = [
    // NOM_DE_VARIABLE=valeur  (.env, config) : DB_PASSWORD=..., APP_KEY=..., API_TOKEN: ...
    {
        re: /\b([A-Z0-9_]*(?:PASSWORD|PASSWD|PWD|SECRET|API_KEY|APIKEY|TOKEN|PRIVATE_KEY|ACCESS_KEY|APP_KEY|AUTH_KEY)[A-Z0-9_]*)([ \t]*[=:][ \t]*["']?)([^\s"'#]{4,})/g,
        value: 3,
        mask: (m, name, sep) => `${name}${sep}********`,
    },
    // JSON / YAML / objets : "api_token": "valeur", password: 'valeur'
    {
        re: /(["']?)\b([A-Za-z0-9_]*(?:password|passwd|secret|api_?key|token|private_?key|access_?key)[A-Za-z0-9_]*)\1([ \t]*:[ \t]*["'])([^"'\s]{4,})(["'])/gi,
        value: 4,
        mask: (m, q, name, sep, v, end) => `${q}${name}${q}${sep}********${end}`,
    },
    // URL de base de données avec mot de passe : mysql://user:motdepasse@hote
    { re: /\b((?:mysql|postgres(?:ql)?|mongodb(?:\+srv)?|redis|amqp):\/\/[^\s:/@]+:)([^\s@]+)(@)/g, value: 2, mask: (m, a, pw, b) => `${a}********${b}` },
    // Clés connues
    { re: /\bsk-[A-Za-z0-9_-]{20,}/g, mask: () => 'sk-********' },
    { re: /\bgh[pousr]_[A-Za-z0-9]{30,}/g, mask: () => 'ghp_********' },
    { re: /\bAKIA[0-9A-Z]{16}\b/g, mask: () => 'AKIA********' },
    { re: /\bAIza[0-9A-Za-z_-]{30,}/g, mask: () => 'AIza********' },
    { re: /\bxox[baprs]-[A-Za-z0-9-]{10,}/g, mask: () => 'xox-********' },
    { re: /-----BEGIN [A-Z ]*PRIVATE KEY-----[\s\S]*?-----END [A-Z ]*PRIVATE KEY-----/g, mask: () => '-----PRIVATE KEY MASQUÉE-----' },
];

/** Liste des extraits suspects (pour l'alerte). */
export function findSecrets(text = '') {
    const found = [];
    for (const { re, value } of RULES) {
        for (const m of text.matchAll(re)) {
            if (value && /^\*+$/.test(m[value])) continue; // déjà masqué
            found.push(m[0].length > 48 ? m[0].slice(0, 45) + '…' : m[0]);
        }
    }
    return [...new Set(found)];
}

/** Remplace les valeurs secrètes par ********. */
export function maskSecrets(text = '') {
    return RULES.reduce((out, { re, mask }) => out.replace(re, mask), text);
}
