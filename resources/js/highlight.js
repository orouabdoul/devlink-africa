import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import javascript from 'highlight.js/lib/languages/javascript';
import typescript from 'highlight.js/lib/languages/typescript';
import dart from 'highlight.js/lib/languages/dart';
import python from 'highlight.js/lib/languages/python';
import java from 'highlight.js/lib/languages/java';
import kotlin from 'highlight.js/lib/languages/kotlin';
import sql from 'highlight.js/lib/languages/sql';
import bash from 'highlight.js/lib/languages/bash';
import json from 'highlight.js/lib/languages/json';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import yaml from 'highlight.js/lib/languages/yaml';
import plaintext from 'highlight.js/lib/languages/plaintext';
import 'highlight.js/styles/github-dark.min.css';
import { t } from '@/i18n';

Object.entries({ php, javascript, typescript, dart, python, java, kotlin, sql, bash, json, xml, css, yaml, plaintext }).forEach(
    ([name, lang]) => hljs.registerLanguage(name, lang),
);
hljs.registerAliases(['html', 'blade', 'vue'], { languageName: 'xml' });
hljs.registerAliases(['text', 'txt', 'log'], { languageName: 'plaintext' });
hljs.registerAliases(['js'], { languageName: 'javascript' });
hljs.registerAliases(['ts'], { languageName: 'typescript' });
hljs.registerAliases(['sh', 'shell', 'terminal'], { languageName: 'bash' });
hljs.configure({ ignoreUnescapedHTML: true });

const COPY_ICON =
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';

function enhance(root) {
    root.querySelectorAll('pre > code').forEach((code) => {
        if (code.dataset.enhanced) return;
        code.dataset.enhanced = '1';

        // Coloration : langage indiqué (```php) ou détection automatique pour les blocs sans langage.
        const declared = [...code.classList].find((c) => c.startsWith('language-'))?.slice(9);
        if (declared && hljs.getLanguage(declared)) {
            hljs.highlightElement(code);
        } else if (!declared) {
            const res = hljs.highlightAuto(code.textContent);
            if (res.relevance > 5) {
                code.innerHTML = res.value; // HTML produit et échappé par highlight.js
                code.classList.add('hljs');
            }
        }

        // Bouton « Copier »
        const pre = code.parentElement;
        pre.classList.add('relative', 'group');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className =
            'absolute right-2 top-2 inline-flex items-center gap-1 rounded-md bg-white/10 px-2 py-1 text-[11px] font-semibold text-slate-200 opacity-70 transition hover:bg-white/20 hover:opacity-100';
        const label = () => `${COPY_ICON}<span>${t('Copier')}</span>`;
        btn.innerHTML = label();
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(code.textContent);
                btn.innerHTML = `<span>✓ ${t('Copié !')}</span>`;
            } catch {
                btn.innerHTML = `<span>${t('Copie impossible')}</span>`;
            }
            setTimeout(() => (btn.innerHTML = label()), 1800);
        });
        pre.appendChild(btn);
    });
}

/** v-highlight : à placer sur un conteneur qui reçoit du HTML Markdown (v-html). */
export default {
    mounted: enhance,
    updated: enhance,
};
