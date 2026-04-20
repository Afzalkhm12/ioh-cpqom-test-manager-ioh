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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    dark: '#4D4D4F',
                    red: '#ED1C24',
                    yellow: '#FFCB05',
                    teal: '#32BCAD',
                    purple: '#C6168D',
                    pink: '#EC008C',
                }
            }
        },
    },

    plugins: [forms],
};
