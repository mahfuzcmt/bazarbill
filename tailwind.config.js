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
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
                bengali: ['Hind Siliguri', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: ['border-l-emerald-500', 'border-l-rose-500', 'text-emerald-500', 'text-rose-500', 'text-emerald-800', 'text-rose-800', 'text-rose-700'],

    plugins: [forms],
};
