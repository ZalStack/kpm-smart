<script setup>
import { computed, useAttrs } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    disabled: { type: Boolean, default: false },
    /** Tandai select bermasalah: menambah aria-invalid dan ring merah. */
    error: { type: [String, Boolean], default: false },
    class: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['update:modelValue']);

const attrs = useAttrs();

const hasError = computed(() => props.error !== false && props.error !== '' && props.error !== undefined);
</script>

<template>
    <select
        v-bind="attrs"
        :value="modelValue"
        :disabled="disabled"
        :aria-invalid="hasError ? 'true' : undefined"
        @change="emit('update:modelValue', $event.target.value)"
        :class="cn(
            'flex h-11 w-full items-center justify-between rounded-xl border bg-background px-3.5 py-2 text-sm text-foreground transition-all duration-200 shadow-xs cursor-pointer',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-0',
            'disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-muted/40',
            hasError
                ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus-visible:border-rose-500 focus-visible:ring-rose-500/20'
                : 'border-border/80 hover:border-slate-300 focus-visible:border-primary focus-visible:ring-primary/20',
            props.class
        )"
    >
        <slot />
    </select>
</template>
