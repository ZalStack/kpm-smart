<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: '' },
    color: { type: String, default: 'emerald' },
    subtitle: { type: String, default: '' },
    class: { type: [String, Array, Object], default: '' },
});

const colorMap = {
    'emerald': 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400',
    'fern': 'bg-emerald-50 text-emerald-600 border-emerald-100',
    'dry-sage': 'bg-teal-50 text-teal-600 border-teal-100',
    'pine-teal': 'bg-teal-50 text-teal-700 border-teal-100',
    'hunter-green': 'bg-emerald-50 text-emerald-700 border-emerald-100',
    'danger': 'bg-rose-50 text-rose-600 border-rose-100',
    'warning': 'bg-amber-50 text-amber-600 border-amber-100',
    'blue': 'bg-sky-50 text-sky-600 border-sky-100',
    'indigo': 'bg-indigo-50 text-indigo-600 border-indigo-100',
    'purple': 'bg-purple-50 text-purple-600 border-purple-100',
};

const borderAccentMap = {
    'emerald': '#10b981',
    'fern': '#10b981',
    'dry-sage': '#14b8a6',
    'pine-teal': '#0f766e',
    'hunter-green': '#047857',
    'danger': '#f43f5e',
    'warning': '#f59e0b',
    'blue': '#0ea5e9',
    'indigo': '#6366f1',
    'purple': '#8b5cf6',
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
