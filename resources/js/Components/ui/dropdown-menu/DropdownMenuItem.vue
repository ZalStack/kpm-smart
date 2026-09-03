<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
    disabled: { type: Boolean, default: false },
    as: { type: String, default: 'button' },
});

const emit = defineEmits(['click']);
</script>

<template>
    <component
        :is="as"
        :class="cn(
            'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
            props.class
        )"
        :disabled="disabled"
        @click="emit('click')"
    >
        <slot />
    </component>
</template>
