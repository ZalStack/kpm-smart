<script setup>
const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">{{ title }}</h1>
            <p v-if="subtitle" class="text-muted-foreground text-xs sm:text-sm mt-1 leading-relaxed">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2.5">
            <slot name="actions" />
        </div>
    </div>
</template>
