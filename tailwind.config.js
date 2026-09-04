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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', ...defaultTheme.fontFamily.sans],
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

                /* Vibrant Brand Palette */
                'olive': '#769826',
                'olive-light': '#8AB52E',
                'lime-vibrant': '#A1CB35',
                'lime-bright': '#B8D94E',
                'sun-yellow': '#FFDE4E',
                'sun-warm': '#FFE87A',
                'tangerine': '#FF9D4D',
                'tangerine-light': '#FFB877',
                'coral': '#FF7A5C',

                /* Legacy aliases for backward compat */
                'dust-grey': '#e8e5dc',
                'dry-sage': '#A1CB35',
                'fern': '#769826',
                'hunter-green': '#5A7A1E',
                'pine-teal': '#3D5A14',

                success: {
                    50: '#f0fdf4',
                    500: '#22c55e',
                    600: '#16a34a',
                },
                danger: {
                    50: '#fef2f2',
                    500: '#ef4444',
                    600: '#dc2626',
                },
                warning: {
                    50: '#fffbeb',
                    500: '#FF9D4D',
                    600: '#E88A3A',
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
                'card': '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-hover': '0 10px 25px -5px rgb(0 0 0 / 0.08), 0 8px 10px -6px rgb(0 0 0 / 0.04)',
                'card-lg': '0 20px 40px -8px rgb(0 0 0 / 0.1), 0 8px 16px -6px rgb(0 0 0 / 0.06)',
                'sidebar': '4px 0 24px -4px rgb(0 0 0 / 0.15)',
                'glow': '0 0 20px -4px rgb(118 152 38 / 0.35)',
                'glow-lime': '0 0 20px -4px rgb(161 203 53 / 0.35)',
                'glow-yellow': '0 0 20px -4px rgb(255 222 78 / 0.4)',
                'glow-orange': '0 0 20px -4px rgb(255 157 77 / 0.35)',
                'soft': '0 2px 15px -3px rgb(0 0 0 / 0.07), 0 10px 20px -2px rgb(0 0 0 / 0.04)',
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
            },

            spacing: {
                '18': '4.5rem',
                '88': '22rem',
            },
        },
    },

    plugins: [forms],

    safelist: [
        'bg-olive',
        'bg-olive-light',
        'bg-lime-vibrant',
        'bg-lime-bright',
        'bg-sun-yellow',
        'bg-sun-warm',
        'bg-tangerine',
        'bg-tangerine-light',
        'bg-coral',
        'bg-dust-grey',
        'bg-dry-sage',
        'bg-fern',
        'bg-hunter-green',
        'bg-pine-teal',
        'text-olive',
        'text-olive-light',
        'text-lime-vibrant',
        'text-lime-bright',
        'text-sun-yellow',
        'text-tangerine',
        'text-coral',
        'text-dust-grey',
        'text-dry-sage',
        'text-fern',
        'text-hunter-green',
        'text-pine-teal',
        'border-olive',
        'border-lime-vibrant',
        'border-sun-yellow',
        'border-tangerine',
        'border-dust-grey',
        'border-dry-sage',
        'border-fern',
        'border-hunter-green',
        'border-pine-teal',
        'from-olive',
        'from-lime-vibrant',
        'from-sun-yellow',
        'from-tangerine',
        'to-olive',
        'to-lime-vibrant',
        'to-sun-yellow',
        'to-tangerine',
        'animate-fade-in',
        'animate-fade-in-up',
        'animate-slide-up',
        'animate-scale-in',
        'animate-pulse-soft',
        'animate-shimmer',
        'animate-float',
    ]
};
