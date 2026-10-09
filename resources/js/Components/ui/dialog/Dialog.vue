<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open']);
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity"
                @click="emit('update:open', false)"
            />
        </Transition>
        <Transition name="scale">
            <div
                v-if="open"
                class="fixed left-[50%] top-[50%] z-50 grid w-[calc(100%-2rem)] max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 rounded-2xl sm:rounded-3xl border border-border/80 bg-card p-6 shadow-2xl transition-all"
                @click.stop
            >
                <slot />
                <button
                    class="absolute right-4 top-4 rounded-xl p-1.5 text-muted-foreground opacity-70 transition-all hover:bg-muted hover:opacity-100 hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                    @click="emit('update:open', false)"
                    aria-label="Close"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.scale-enter-active { transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
.scale-leave-active { transition: all 0.2s ease-in; }
.scale-enter-from { opacity: 0; transform: translate(-50%, -48%) scale(0.96); }
.scale-leave-to { opacity: 0; transform: translate(-50%, -48%) scale(0.96); }
</style>
