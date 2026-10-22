<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: '' },
    color: { type: String, default: 'blue' },
    subtitle: { type: String, default: '' },
    class: { type: [String, Array, Object], default: '' },
});

const colorMap = {
    'blue': 'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400',
    'indigo': 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400',
    'purple': 'bg-purple-50 text-purple-600 border-purple-100 dark:bg-purple-950/40 dark:text-purple-400',
    'sky': 'bg-sky-50 text-sky-600 border-sky-100 dark:bg-sky-950/40 dark:text-sky-400',
    'fern': 'bg-blue-50 text-blue-600 border-blue-100',
    'dry-sage': 'bg-indigo-50 text-indigo-600 border-indigo-100',
    'pine-teal': 'bg-sky-50 text-sky-700 border-sky-100',
    'hunter-green': 'bg-blue-50 text-blue-700 border-blue-100',
    'emerald': 'bg-blue-50 text-blue-600 border-blue-100',
    'danger': 'bg-rose-50 text-rose-600 border-rose-100',
    'warning': 'bg-amber-50 text-amber-600 border-amber-100',
};

const borderAccentMap = {
    'blue': '#2563eb',
    'indigo': '#4f46e5',
    'purple': '#7c3aed',
    'sky': '#0284c7',
    'fern': '#2563eb',
    'dry-sage': '#4f46e5',
    'pine-teal': '#0284c7',
    'hunter-green': '#1d4ed8',
    'emerald': '#2563eb',
    'danger': '#f43f5e',
    'warning': '#f59e0b',
};
</script>

<template>
    <div
        :class="cn('stat-tile p-5 group bg-card transition-all duration-300 hover:shadow-md', props.class)"
        :style="{ '--tile-accent': borderAccentMap[color] || '#10b981' }"
    >
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground truncate">{{ title }}</p>
                <p class="text-2xl sm:text-3xl font-extrabold mt-1 tracking-tight text-foreground">{{ value }}</p>
                <p v-if="subtitle" class="text-xs text-muted-foreground mt-1 truncate">{{ subtitle }}</p>
            </div>
            <div
                v-if="icon"
                :class="cn(
                    'w-12 h-12 rounded-2xl border flex items-center justify-center text-xl flex-shrink-0 transition-transform duration-300 group-hover:scale-105 shadow-xs',
                    colorMap[color] || colorMap.emerald
                )"
            >
                <span v-html="icon" class="flex items-center justify-center" />
            </div>
        </div>
        <div v-if="$slots.footer" class="mt-3 pt-3 border-t border-border/60">
            <slot name="footer" />
        </div>
    </div>
</template>
