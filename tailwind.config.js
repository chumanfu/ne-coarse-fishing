import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Source Sans 3"', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', 'serif'],
            },
            colors: {
                ink: {
                    DEFAULT: '#1f2e26',
                    muted: '#4d5f55',
                    soft: '#6b7c73',
                },
                paper: {
                    DEFAULT: '#f5f2ea',
                    bright: '#fcfaf5',
                    deep: '#ebe6db',
                },
                moss: {
                    DEFAULT: '#3f5c48',
                    soft: '#e6eee8',
                    dark: '#2c4234',
                    light: '#5a7a62',
                },
                water: {
                    DEFAULT: '#4a7c8c',
                    soft: '#e7f1f4',
                    dark: '#356575',
                    mist: '#d4e5eb',
                },
                bank: {
                    DEFAULT: '#6b5746',
                    soft: '#f0ebe3',
                    light: '#8a7360',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(31, 46, 38, 0.04), 0 8px 24px rgba(31, 46, 38, 0.06)',
                lift: '0 2px 4px rgba(31, 46, 38, 0.05), 0 12px 28px rgba(31, 46, 38, 0.08)',
            },
            borderRadius: {
                card: '1rem',
            },
        },
    },

    plugins: [forms],
};
