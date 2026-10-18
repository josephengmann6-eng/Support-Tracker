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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                forest: { DEFAULT: '#0B2B1F', 700: '#14412F', 600: '#1B5A41', 500: '#25724F' },
                hero: '#0F3D2B',
                canvas: '#EEF3EF',
                brand: { DEFAULT: '#22C55E', 700: '#166534', 600: '#16A34A', 100: '#DCFCE7', 50: '#F0FDF4' },
                pending: { DEFAULT: '#F59E0B', 900: '#78350F', 700: '#B45309', 100: '#FEF3C7' },
                overdue: { DEFAULT: '#EF4444', 100: '#FEE2E2', 700: '#B91C1C' },
            },
            borderRadius: {
                card: '20px',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(11,43,31,0.04), 0 8px 24px rgba(11,43,31,0.06)',
            },
        },
    },

    plugins: [forms],
};
