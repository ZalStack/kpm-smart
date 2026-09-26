<script setup>
import { computed, useAttrs } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
    /** Tandai textarea bermasalah: menambah aria-invalid dan cincin merah. */
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
    <textarea
        v-bind="attrs"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        :aria-invalid="hasError ? 'true' : undefined"
        @input="onInput"
        :class="cn(
            'flex min-h-[80px] w-full rounded-md border bg-background px-3 py-2 text-sm transition-[color,box-shadow] ring-offset-background',
            'placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
            'disabled:cursor-not-allowed disabled:opacity-50',
            hasError
                ? 'border-destructive focus-visible:ring-destructive'
                : 'border-input focus-visible:ring-ring',
            props.class
        )"
    />
</template>
