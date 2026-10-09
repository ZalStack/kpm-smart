<script setup>
import { cn } from '@/lib/utils';
import { cva } from 'class-variance-authority';

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    class: { type: [String, Array, Object], default: '' },
});

const badgeVariants = cva(
    'inline-flex items-center gap-1 rounded-full font-medium tracking-wide transition-colors duration-150 select-none',
    {
        variants: {
            variant: {
                default: 'bg-primary/10 text-primary border border-primary/20',
                primary: 'bg-primary text-primary-foreground shadow-xs',
                secondary: 'bg-slate-100 text-slate-700 border border-slate-200/80',
                destructive: 'bg-rose-50 text-rose-700 border border-rose-200/80',
                danger: 'bg-rose-50 text-rose-700 border border-rose-200/80',
                outline: 'border border-border text-foreground bg-background',
                success: 'bg-emerald-50 text-emerald-700 border border-emerald-200/80',
                warning: 'bg-amber-50 text-amber-700 border border-amber-200/80',
                info: 'bg-sky-50 text-sky-700 border border-sky-200/80',
                purple: 'bg-purple-50 text-purple-700 border border-purple-200/80',
            },
            size: {
                default: 'px-2.5 py-0.5 text-xs',
                sm: 'px-2 py-0.5 text-[10px]',
                lg: 'px-3.5 py-1 text-sm font-semibold',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    }
);
</script>

<template>
    <div :class="cn(badgeVariants({ variant, size }), props.class)">
        <slot />
    </div>
</template>
