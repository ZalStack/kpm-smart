<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap items-center justify-center gap-1.5 mt-8">
        <template v-for="(link, key) in links" :key="key">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                    'min-w-9 h-9 px-3.5 flex items-center justify-center text-xs font-semibold rounded-xl transition-all duration-200 shadow-2xs',
                    link.active
                        ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/25 pointer-events-none'
                        : 'bg-card text-foreground border border-border/80 hover:border-primary/40 hover:bg-accent/70 hover:text-primary'
                ]"
            />
            <span
                v-else
                v-html="link.label"
                class="min-w-9 h-9 px-3 flex items-center justify-center text-xs font-medium text-muted-foreground/50 select-none"
            />
        </template>
    </div>
</template>
