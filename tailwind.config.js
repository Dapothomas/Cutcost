import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        borderRadius: {
            none: '0',
            sm: '0',
            DEFAULT: '0',
            md: '0',
            lg: '0',
            xl: '0',
            '2xl': '0',
            '3xl': '0',
            full: '0',
        },
        extend: {
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    deep: 'hsl(var(--primary-deep))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                success: {
                    DEFAULT: 'hsl(var(--success))',
                    foreground: 'hsl(var(--success-foreground))',
                },
                warning: {
                    DEFAULT: 'hsl(var(--warning))',
                    foreground: 'hsl(var(--warning-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar-background))',
                    foreground: 'hsl(var(--sidebar-foreground))',
                    border: 'hsl(var(--sidebar-border))',
                    accent: 'hsl(var(--sidebar-accent))',
                    'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
                },
                brand: {
                    50: '#eef4ff',
                    100: '#dfe9ff',
                    200: '#c5d7ff',
                    300: '#a2bcff',
                    400: '#7c97fd',
                    500: '#5c72f7',
                    600: '#3d4feb',
                    700: '#3140cf',
                    800: '#2b38a7',
                    900: '#293584',
                },
                ink: {
                    50: '#f7f8fa',
                    100: '#eef0f4',
                    200: '#dadee6',
                    300: '#b9c0cd',
                    400: '#929cb0',
                    500: '#747f95',
                    600: '#5d677c',
                    700: '#4c5365',
                    800: '#414755',
                    900: '#393e49',
                    950: '#131722',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                sm: '0 1px 2px 0 rgb(19 23 34 / 0.04)',
                card: '0 1px 2px rgb(19 23 34 / 0.04), 0 8px 24px -12px rgb(19 23 34 / 0.10)',
                'card-hover': '0 2px 4px rgb(19 23 34 / 0.05), 0 16px 40px -16px rgb(19 23 34 / 0.18)',
                pop: '0 12px 32px -12px hsl(var(--primary) / 0.45)',
            },
        },
    },

    plugins: [forms],
};
