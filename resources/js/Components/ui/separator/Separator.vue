<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    orientation: { type: String, default: 'horizontal' },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <div
        :class="cn(
            'shrink-0 bg-border',
            props.orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
            props.class
        )"
    />
</template>
