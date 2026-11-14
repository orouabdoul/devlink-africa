import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Palette DevLink Africa
                brand: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    500: '#3B82F6',
                    600: '#2563EB', // Bleu principal
                    700: '#1D4ED8',
                    800: '#1E40AF',
                },
                night: '#111827', // Bleu nuit
                canvas: '#F8FAFC', // Fond
                muted: '#64748B', // Texte secondaire
                success: '#16A34A',
                // Accents « kente » discrets, utilisés uniquement dans la bande décorative
                kente: { gold: '#F59E0B', green: '#16A34A', red: '#DC2626' },
            },
        },
    },

    plugins: [forms, typography],
};
