import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                shell: '1.75rem',
            },
            boxShadow: {
                shell: '0 25px 50px -12px rgb(74 32 112 / 0.12), 0 0 0 1px rgb(226 232 240 / 0.6)',
                'shell-inner': '0 20px 40px -15px rgb(99 42 126 / 0.15)',
            },
            colors: {
                talents: {
                    50: '#f5f0fa',
                    100: '#e8dcf2',
                    200: '#d4b8e4',
                    300: '#b388d9',
                    400: '#9b6bc4',
                    500: '#7b4fa2',
                    600: '#632a7e',
                    700: '#4a2070',
                    800: '#3a1858',
                    900: '#2a1042',
                    accent: '#b388d9',
                },
                /** Destaques tipo LMS (gráficos, CTAs) */
                highlight: {
                    DEFAULT: '#e8b84a',
                    muted: '#f5e6c8',
                },
            },
        },
    },

    plugins: [forms],
};
