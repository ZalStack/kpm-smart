<script setup>
import { cn } from '@/lib/utils';
import { cva } from 'class-variance-authority';

const props = defineProps({
    variant: { type: String, default: 'default' },
    class: { type: [String, Array, Object], default: '' },
});

const alertVariants = cva(
    'relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground',
    {
        variants: {
            variant: {
                default: 'bg-background text-foreground',
                destructive: 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
                success: 'border-green-200 bg-green-50 text-green-800 [&>svg]:text-green-600',
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
