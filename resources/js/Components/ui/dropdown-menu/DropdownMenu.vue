<script setup>
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: [String, Array, Object], default: '' },
});

const open = ref(false);

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}
</script>

<template>
    <div class="relative inline-block text-left" v-on-click-outside="close">
        <div @click="toggle">
            <slot name="trigger" />
        </div>
        <Transition name="dropdown">
            <div
                v-if="open"
                :class="cn(
                    'absolute right-0 z-50 mt-2 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md',
                    props.class
                )"
            >
                <slot :close="close" />
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-enter-from { opacity: 0; transform: scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: scale(0.95); }
</style>
