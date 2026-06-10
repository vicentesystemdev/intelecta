import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: 'rgb(var(--color-primary) / <alpha-value>)',
                    secondary: 'rgb(var(--color-secondary) / <alpha-value>)',
                    accent: 'rgb(var(--color-accent) / <alpha-value>)',
                    bg: 'rgb(var(--color-bg) / <alpha-value>)',
                    card: 'rgb(var(--color-card) / <alpha-value>)',
                    border: 'rgb(var(--color-border) / <alpha-value>)',
                    success: 'rgb(var(--color-success) / <alpha-value>)',
                    warning: 'rgb(var(--color-warning) / <alpha-value>)',
                    danger: 'rgb(var(--color-danger) / <alpha-value>)',
                    info: 'rgb(var(--color-info) / <alpha-value>)',
                },
                text: {
                    main: 'rgb(var(--text-main) / <alpha-value>)',
                    muted: 'rgb(var(--text-muted) / <alpha-value>)',
                }
            },
        },
    },

    plugins: [forms],
};
