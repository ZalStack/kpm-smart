<script setup>
import { computed, useAttrs } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    placeholder: { type: String, default: '' },
    type: { type: String, default: 'text' },
    disabled: { type: Boolean, default: false },
    /** Tandai input bermasalah: menambah aria-invalid dan cincin merah. */
    error: { type: [String, Boolean], default: false },
    class: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['update:modelValue']);

const attrs = useAttrs();

const hasError = computed(() => props.error !== false && props.error !== '');

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
            'flex h-10 w-full rounded-md border bg-background px-3 py-2 text-sm transition-[color,box-shadow] ring-offset-background',
            'file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
            'disabled:cursor-not-allowed disabled:opacity-50',
            hasError
                ? 'border-destructive focus-visible:ring-destructive'
                : 'border-input focus-visible:ring-ring',
            props.class
        )"
    />
</template>
