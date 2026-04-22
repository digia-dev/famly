const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',

        './app/Helpers/**/*.php',
    ],
    safelist: [
        'bg-emerald-700',
        'bg-rose-700',
        'bg-slate-900',
        'bg-blue-600',
        'bg-amber-600',
        'bg-white',
        'text-white',
        'text-slate-900',
        'text-slate-500',
        'text-emerald-600',
        'text-amber-600'
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': '#00AA13',
                'primary-container': '#00DF19',
                'tertiary': '#735c00',
                'tertiary-container': '#d3ae36',
                'surface': '#FFFFFF',
                'surface-low': '#F6F7F8',
                'surface-lowest': '#FFFFFF',
                'on-surface': '#1C1C1C',
                'on-surface-variant': '#6A6A6A',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
