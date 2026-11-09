<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    asChild: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
});

const variants = {
    default: 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm hover:shadow active:bg-primary/95',
    brand: 'bg-gradient-to-r from-brand-indigo to-brand-blue text-white hover:from-brand-indigo/90 hover:to-brand-blue/90 shadow-sm hover:shadow-md active:scale-[0.97]',
    destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-sm hover:shadow active:bg-destructive/95',
    outline: 'border border-border bg-background hover:bg-accent/80 hover:text-foreground shadow-xs',
    'outline-primary': 'border border-primary/30 text-primary bg-primary/5 hover:bg-primary/10 hover:border-primary/50',
    secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80 border border-secondary-foreground/10',
    ghost: 'text-muted-foreground hover:bg-muted/80 hover:text-foreground',
    link: 'text-primary underline-offset-4 hover:underline p-0 h-auto font-medium',
    success: 'bg-brand-emerald text-white hover:bg-emerald-700 shadow-sm hover:shadow',
};

const sizes = {
    default: 'h-10 px-4 py-2 rounded-2xl text-sm font-extrabold',
    xs: 'h-7 px-2.5 rounded-xl text-xs font-extrabold',
    sm: 'h-9 px-3 rounded-xl text-xs font-extrabold',
    lg: 'h-12 px-6 rounded-2xl text-base font-extrabold',
    icon: 'h-10 w-10 rounded-2xl p-0',
    'icon-sm': 'h-8 w-8 rounded-xl p-0',
};
</script>

<template>
    <button
        :disabled="loading || $attrs.disabled"
        :aria-busy="loading ? 'true' : undefined"
        :class="cn(
            'inline-flex items-center justify-center gap-2 font-extrabold tracking-tight whitespace-nowrap select-none transition-all duration-200 ease-out',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background',
            'active:scale-[0.97] disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none',
            variants[variant] || variants.default,
            sizes[size] || sizes.default,
            $attrs.class
        )"
    >
        <span
            v-if="loading"
            class="h-4 w-4 shrink-0 rounded-full border-2 border-current border-t-transparent animate-spin"
            aria-hidden="true"
        />
        <slot />
    </button>
</template>