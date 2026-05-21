import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        '.\\storage\\framework\\views\\*.php',
        '.\\resources\\views\\**\\*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#EBF4FF',
                    100: '#D6E9FF',
                    200: '#AED3FF',
                    300: '#7DB8FF',
                    400: '#4A9BFF',
                    500: '#2E70DA',
                    600: '#1E3A8A',
                    700: '#1A336E',
                    800: '#162B58',
                    900: '#122344',
                },
                secondary: {
                    50: '#FDF5E6',
                    100: '#FCEBD0',
                    200: '#F9D7A0',
                    300: '#F6C370',
                    400: '#F3AF40',
                    500: '#F6B745',
                    600: '#D89E2E',
                    700: '#B8851F',
                    800: '#9A6E17',
                    900: '#7A5711',
                },
                success: {
                    50: '#ECFDF5',
                    100: '#D1FAE5',
                    200: '#A7F3D0',
                    300: '#6EE7B7',
                    400: '#34D399',
                    500: '#10B981',
                    600: '#059669',
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    300: '#FCD34D',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706',
                },
                danger: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                },
            },
            boxShadow: {
                card: '0 0 0 1px rgba(0, 0, 0, 0.03), 0 2px 8px rgba(0, 0, 0, 0.04)',
                'card-hover': '0 0 0 1px rgba(0, 0, 0, 0.03), 0 12px 24px rgba(0, 0, 0, 0.08)',
                'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.04)',
                'soft-lg': '0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
