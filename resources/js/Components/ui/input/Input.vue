<script setup>
import { computed, useAttrs } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: '' },
    type: { type: String, default: 'text' },
    disabled: { type: Boolean, default: false },
    /** Tandai input bermasalah: menambah aria-invalid dan border merah. */
    error: { type: [String, Boolean], default: false },
    class: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['update:modelValue']);

const attrs = useAttrs();

const hasError = computed(() => props.error !== false && props.error !== '' && props.error !== undefined);

function onInput(event) {
    emit('update:modelValue', event.target.value);
}
</script>

<template>
    <input
        v-bind="attrs"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :aria-invalid="hasError ? 'true' : undefined"
        @input="onInput"
        :class="cn(
            'flex h-11 w-full rounded-xl border bg-background px-3.5 py-2 text-sm text-foreground transition-all duration-200 shadow-xs',
            'file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground/70',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-0',
            'disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-muted/40',
            hasError
                ? 'border-rose-400 bg-rose-50/20 text-rose-900 focus-visible:border-rose-500 focus-visible:ring-rose-500/20'
                : 'border-border/80 hover:border-slate-300 focus-visible:border-primary focus-visible:ring-primary/20',
            props.class
        )"
    />
</template>
