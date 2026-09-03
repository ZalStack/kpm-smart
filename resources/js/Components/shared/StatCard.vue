<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: '' },
    color: { type: String, default: 'fern' },
    class: { type: [String, Array, Object], default: '' },
});

const colorMap = {
    'fern': 'bg-fern/10 text-fern',
    'dry-sage': 'bg-dry-sage/10 text-dry-sage',
    'pine-teal': 'bg-pine-teal/10 text-pine-teal',
    'hunter-green': 'bg-hunter-green/10 text-hunter-green',
    'danger': 'bg-red-50 text-red-600',
    'warning': 'bg-yellow-50 text-yellow-600',
    'blue': 'bg-blue-50 text-blue-600',
};
</script>

<template>
    <div :class="cn('stat-tile p-5 group', props.class)">
        <div class="flex items-start justify-between">
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground font-medium">{{ title }}</p>
                <p class="text-2xl font-bold mt-1.5 tracking-tight">{{ value }}</p>
            </div>
            <div v-if="icon" :class="cn('w-12 h-12 rounded-lg flex items-center justify-center text-xl flex-shrink-0 transition-all duration-300 group-hover:scale-110', colorMap[color] || colorMap.fern)">
                <span v-html="icon" />
            </div>
        </div>
        <slot name="footer" />
    </div>
</template>
