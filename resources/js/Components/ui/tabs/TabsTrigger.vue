<script setup>
import { inject } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    value: { type: String, required: true },
    disabled: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const { activeTab, setActiveTab } = inject('tabs');
</script>

<template>
    <button
        :class="cn(
            'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
            activeTab === value && 'bg-background text-foreground shadow-sm',
            props.class
        )"
        :disabled="disabled"
        @click="setActiveTab(value)"
    >
        <slot />
    </button>
</template>
