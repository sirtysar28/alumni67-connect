import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                /* Warna via CSS variables → mendukung dark/light mode
                   (rgb channel + <alpha-value> agar modifier /50 tetap jalan) */
                navy: {
                    DEFAULT: 'rgb(var(--c-navy) / <alpha-value>)',
                    deep: 'rgb(var(--c-navy-deep) / <alpha-value>)',
                    card: 'rgb(var(--c-navy-card) / <alpha-value>)',
                },
                neon: 'rgb(var(--c-neon) / <alpha-value>)',
                neonDim: 'rgb(var(--c-neon-dim) / <alpha-value>)',
                cream: 'rgb(var(--c-cream) / <alpha-value>)',
                creamDim: 'rgb(var(--c-cream-dim) / <alpha-value>)',
                line: 'rgb(var(--c-line) / <alpha-value>)',
                maroon: 'rgb(var(--c-maroon) / <alpha-value>)',
                gold: 'rgb(var(--c-gold) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Sora', 'sans-serif'],
                mono: ['"Space Mono"', ...defaultTheme.fontFamily.mono],
                hand: ['Caveat', 'cursive'],
            },
        },
    },

    plugins: [forms],
};
