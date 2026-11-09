<script setup>
import { cn } from '@/lib/utils';
import { cva } from 'class-variance-authority';

const props = defineProps({
    variant: { type: String, default: 'default' },
    class: { type: [String, Array, Object], default: '' },
});

const alertVariants = cva(
    'relative w-full rounded-2xl border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground shadow-sm',
    {
        variants: {
            variant: {
                default: 'bg-background text-foreground border-border',
                destructive: 'border-rose-200 bg-rose-50 text-rose-800 [&>svg]:text-rose-500',
                success: 'border-emerald-200 bg-emerald-50 text-emerald-800 [&>svg]:text-brand-emerald',
                warning: 'border-amber-200 bg-amber-50 text-amber-800 [&>svg]:text-brand-amber',
                info: 'border-blue-200 bg-blue-50 text-blue-800 [&>svg]:text-brand-blue',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);
</script>

<template>
    <div role="alert" :class="cn(alertVariants({ variant }), props.class)">
        <slot />
    </div>
</template>