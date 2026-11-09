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
    'blue': 'bg-blue-50 text-brand-blue border-blue-200',
    'indigo': 'bg-indigo-50 text-brand-indigo border-indigo-200',
    'purple': 'bg-violet-50 text-brand-violet border-violet-200',
    'sky': 'bg-sky-50 text-brand-cyan border-sky-200',
    'emerald': 'bg-emerald-50 text-brand-emerald border-emerald-200',
    'danger': 'bg-rose-50 text-rose-600 border-rose-200',
    'warning': 'bg-amber-50 text-amber-600 border-amber-200',
    'fern': 'bg-blue-50 text-brand-blue border-blue-200',
    'dry-sage': 'bg-indigo-50 text-brand-indigo border-indigo-200',
    'pine-teal': 'bg-sky-50 text-brand-cyan border-sky-200',
    'hunter-green': 'bg-emerald-50 text-brand-emerald border-emerald-200',
};

const borderAccentMap = {
    'blue': '#2563eb',
    'indigo': '#4f46e5',
    'purple': '#7c3aed',
    'sky': '#0284c7',
    'emerald': '#10b981',
    'danger': '#f43f5e',
    'warning': '#f59e0b',
    'fern': '#2563eb',
    'dry-sage': '#4f46e5',
    'pine-teal': '#0284c7',
    'hunter-green': '#10b981',
};
</script>

<template>
    <div
        :class="cn('stat-tile p-5 sm:p-6 group', props.class)"
        :style="{ '--tile-accent': borderAccentMap[color] || '#10b981' }"
    >
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="stat-label">{{ title }}</p>
                <p class="stat-value">{{ value }}</p>
                <p v-if="subtitle" class="text-xs text-muted-foreground mt-1.5 truncate">{{ subtitle }}</p>
            </div>
            <div
                v-if="icon"
                :class="cn(
                    'w-12 h-12 rounded-2xl border flex items-center justify-center text-xl flex-shrink-0 transition-transform duration-300 group-hover:scale-110 shadow-xs',
                    colorMap[color] || colorMap.emerald
                )"
            >
                <span v-html="icon" class="flex items-center justify-center" />
            </div>
        </div>
        <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-border/60">
            <slot name="footer" />
        </div>
    </div>
</template>