<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    asChild: { type: Boolean, default: false },
    /** Tampilkan spinner dan kunci klik. Pakai ini alih-alih mengelola disabled manual. */
    loading: { type: Boolean, default: false },
});

const variants = {
    default: 'bg-primary text-primary-foreground hover:bg-primary/90',
    destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
    outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
    secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
    ghost: 'hover:bg-accent hover:text-accent-foreground',
    link: 'text-primary underline-offset-4 hover:underline',
};

const sizes = {
    default: 'h-10 px-4 py-2',
    sm: 'h-9 rounded-md px-3',
    lg: 'h-11 rounded-md px-8',
    icon: 'h-10 w-10',
};
</script>

<template>
    <button
        :disabled="loading || $attrs.disabled"
        :aria-busy="loading ? 'true' : undefined"
        :class="cn(
            'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-[background-color,color,box-shadow,transform] duration-150',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
            'active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50',
            variants[variant],
            sizes[size],
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
