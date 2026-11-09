<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <div :class="cn('rounded-3xl border border-border/80 bg-card text-card-foreground shadow-sm transition-all duration-200', props.class)">
        <slot />
    </div>
</template>