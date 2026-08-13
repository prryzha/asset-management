import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    // SweetAlert2 menyuntikkan elemen & class-nya sendiri lewat JavaScript saat
    // runtime, jadi nama class-nya (swal2-*) tidak pernah muncul secara harfiah
    // di file blade manapun — tanpa safelist ini, Tailwind akan membuang semua
    // custom styling untuk popup SweetAlert2 di resources/css/app.css.
    safelist: [
        { pattern: /^swal2-/ },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', 'system-ui', 'sans-serif'],
                heading: ['Poppins', 'system-ui', 'sans-serif'],
                body: ['"Google Sans"', 'Inter', 'system-ui', 'sans-serif'],
                table: ['"Google Sans"', 'Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                },
                success: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                },
                danger: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                },
                info: {
                    50: '#F0F9FF',
                    100: '#E0F2FE',
                    500: '#0EA5E9',
                    600: '#0284C7',
                    700: '#0369A1',
                },
            },
            borderRadius: {
                DEFAULT: '6px',
                sm: '4px',
                md: '6px',
                lg: '8px',
                xl: '8px',
                '2xl': '8px',
                '3xl': '8px',
                full: '9999px',
            },
            boxShadow: {
                card: '0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px 0 rgba(0, 0, 0, 0.04)',
                'card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04)',
                header: '0 1px 3px 0 rgba(0, 0, 0, 0.04)',
            },
        },
    },

    plugins: [forms],
};
