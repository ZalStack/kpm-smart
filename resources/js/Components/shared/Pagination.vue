<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-center gap-1 mt-6">
        <template v-for="(link, key) in links" :key="key">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                    'px-3 py-2 text-sm rounded-md transition-colors',
                    link.active
                        ? 'bg-primary text-primary-foreground font-medium'
                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                ]"
            />
            <span
                v-else
                v-html="link.label"
                class="px-3 py-2 text-sm text-muted-foreground"
            />
        </template>
    </div>
</template>
