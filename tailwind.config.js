import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    darkMode: 'class',

    theme: {
        container: {
            center: true,
            padding: '2rem',
            screens: {
                '2xl': '1400px',
            },
        },
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },

                /* Modern Brand Palette - Light Mode Only */
                'brand-indigo': '#4f46e5',
                'brand-blue': '#2563eb',
                'brand-cyan': '#0284c7',
                'brand-violet': '#7c3aed',
                'brand-amber': '#f59e0b',
                'brand-emerald': '#10b981',
                'brand-rose': '#f43f5e',
                'brand-slate': '#1e293b',

                success: {
                    50: '#f0fdf4',
                    500: '#10b981',
                    600: '#059669',
                },
                danger: {
                    50: '#fef2f2',
                    500: '#f43f5e',
                    600: '#e11d48',
                },
                warning: {
                    50: '#fffbeb',
                    500: '#f59e0b',
                    600: '#d97706',
                },
                info: {
                    50: '#eff6ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                },
            },

            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
                '2xl': '1rem',
                '3xl': '1.25rem',
                '4xl': '1.5rem',
            },

            boxShadow: {
                '2xs': '0 1px 2px 0 rgb(0 0 0 / 0.03)',
                'xs': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                'sm': '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                'card': '0 1px 3px 0 rgb(0 0 0 / 0.08), 0 1px 2px -1px rgb(0 0 0 / 0.08)',
                'card-hover': '0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
                'card-lg': '0 20px 40px -8px rgb(0 0 0 / 0.12), 0 8px 16px -6px rgb(0 0 0 / 0.08)',
                'sidebar': '4px 0 24px -4px rgb(0 0 0 / 0.12)',
                'glow': '0 0 20px -4px rgb(79 70 229 / 0.35)',
                'glow-indigo': '0 0 20px -4px rgb(79 70 229 / 0.35)',
                'glow-blue': '0 0 20px -4px rgb(37 99 235 / 0.35)',
                'glow-amber': '0 0 20px -4px rgb(245 158 11 / 0.35)',
                'glow-emerald': '0 0 20px -4px rgb(16 185 129 / 0.35)',
                'soft': '0 2px 15px -3px rgb(0 0 0 / 0.07), 0 10px 20px -2px rgb(0 0 0 / 0.04)',
                'elevated': '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 10px 10px -5px rgb(0 0 0 / 0.04)',
            },

            keyframes: {
                'accordion-down': {
                    from: { height: '0' },
                    to: { height: 'var(--radix-accordion-content-height)' },
                },
                'accordion-up': {
                    from: { height: 'var(--radix-accordion-content-height)' },
                    to: { height: '0' },
                },
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                'fade-in-up': {
                    from: { opacity: '0', transform: 'translateY(24px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-up': {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'scale-in': {
                    from: { opacity: '0', transform: 'scale(0.95)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                'pulse-soft': {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                'shimmer': {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                'float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                'blob': {
                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                    '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                    '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                },
            },

            animation: {
                'accordion-down': 'accordion-down 0.2s ease-out',
                'accordion-up': 'accordion-up 0.2s ease-out',
                'fade-in': 'fadeIn 0.4s ease-out',
                'fade-in-up': 'fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
                'scale-in': 'scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
                'pulse-soft': 'pulseSoft 2s ease-in-out infinite',
                'shimmer': 'shimmer 2s linear infinite',
                'float': 'float 6s ease-in-out infinite',
                'blob': 'blob 7s infinite',
            },

            spacing: {
                '18': '4.5rem',
                '88': '22rem',
            },
        },
    },

    plugins: [forms],

    safelist: [
        'bg-brand-indigo',
        'bg-brand-blue',
        'bg-brand-cyan',
        'bg-brand-violet',
        'bg-brand-amber',
        'bg-brand-emerald',
        'bg-brand-rose',
        'bg-brand-slate',
        'bg-indigo-50',
        'bg-blue-50',
        'bg-emerald-50',
        'bg-amber-50',
        'bg-sky-50',
        'bg-rose-50',
        'text-indigo-600',
        'text-blue-600',
        'text-emerald-600',
        'text-amber-600',
        'text-sky-600',
        'text-rose-600',
        'border-indigo-200',
        'border-blue-200',
        'border-emerald-200',
        'border-amber-200',
        'border-sky-200',
        'border-rose-200',
        'from-indigo-500',
        'from-blue-500',
        'from-emerald-500',
        'from-amber-500',
        'to-indigo-600',
        'to-blue-600',
        'to-emerald-600',
        'to-amber-600',
        'animate-fade-in',
        'animate-fade-in-up',
        'animate-slide-up',
        'animate-scale-in',
        'animate-pulse-soft',
        'animate-shimmer',
        'animate-float',
        'animate-blob',
    ]
};
