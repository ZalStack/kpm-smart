<script setup>
import { ref, provide } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    defaultValue: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    class: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['update:modelValue']);
const activeTab = ref(props.modelValue || props.defaultValue);

function setActiveTab(value) {
    activeTab.value = value;
    emit('update:modelValue', value);
}

provide('tabs', { activeTab, setActiveTab });
</script>

<template>
    <div :class="cn('', props.class)">
        <slot />
    </div>
</template>
