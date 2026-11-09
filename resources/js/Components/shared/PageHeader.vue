<script setup>
const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <div :class="cn('flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-6 sm:mb-8', props.class)">
        <div>
            <h1 v-if="title" class="text-xl sm:text-2xl font-extrabold tracking-tight text-foreground">{{ title }}</h1>
            <p v-if="subtitle" class="mt-1 sm:mt-1.5 text-sm text-muted-foreground leading-relaxed">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 sm:gap-3">
            <slot name="actions" />
        </div>
    </div>
</template>

<script>
import { cn } from '@/lib/utils';
</script>