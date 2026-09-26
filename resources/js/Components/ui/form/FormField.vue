<script setup>
import { computed, useId } from 'vue';
import Label from '@/Components/ui/label/Label.vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    label: { type: String, default: '' },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    class: { type: [String, Array, Object], default: '' },
});

const uid = useId();
const controlId = computed(() => `field-${uid}`);
const hintId = computed(() => `${controlId.value}-hint`);
const errorId = computed(() => `${controlId.value}-error`);

/**
 * Slot props supaya kontrol anak otomatis terhubung ke hint/error tanpa
 * menuliskan id manual di tiap form:
 *
 *   <FormField label="Email" :error="form.errors.email">
 *     <template #default="a"><Input v-bind="a" v-model="form.email" /></template>
 *   </FormField>
 */
const bind = computed(() => ({
    id: controlId.value,
    'aria-describedby': [props.hint ? hintId.value : null, props.error ? errorId.value : null]
        .filter(Boolean)
        .join(' ') || undefined,
    'aria-invalid': props.error ? 'true' : undefined,
    error: props.error || undefined,
}));
</script>

<template>
    <div :class="cn('space-y-2', props.class)">
        <Label v-if="label" :for="controlId">
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true">*</span>
        </Label>

        <slot v-bind="bind" />

        <p v-if="hint && !error" :id="hintId" class="text-xs text-muted-foreground">
            {{ hint }}
        </p>

        <p
            v-if="error"
            :id="errorId"
            class="flex items-start gap-1.5 text-xs font-medium text-destructive"
        >
            <svg class="mt-px h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" />
            </svg>
            <span>{{ error }}</span>
        </p>
    </div>
</template>
