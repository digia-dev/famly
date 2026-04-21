const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',

        './app/Helpers/**/*.php',
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
