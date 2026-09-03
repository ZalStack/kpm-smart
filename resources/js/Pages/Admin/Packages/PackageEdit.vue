<script setup>
import { inject, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
});

const tabs = computed(() => [
    {
        label: 'Informasi',
        href: route('admin.packages.edit.informasi', props.package.id),
        routeMatch: 'admin.packages.edit.informasi',
        icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    },
    {
        label: 'Cards',
        href: route('admin.packages.edit.cards', props.package.id),
        routeMatch: 'admin.packages.edit.cards',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
    {
        label: 'Soal',
        href: route('admin.packages.edit.questions', props.package.id),
        routeMatch: 'admin.packages.edit.questions',
        icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
]);
</script>

<template>
    <AdminLayout>
        <template #header-title>Edit Paket: {{ package.title }}</template>
        <template #header-sub>Kelola informasi, cards, dan soal untuk paket ini</template>

        <div class="space-y-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-muted-foreground">
                <Link :href="route('admin.packages.index')" class="hover:text-foreground transition">Paket</Link>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <Link :href="route('admin.packages.detail', package.id)" class="hover:text-foreground transition truncate max-w-[160px]">{{ package.title }}</Link>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-foreground font-medium">Edit</span>
            </nav>

            <!-- Tab Navigation -->
            <div class="border-b border-border">
                <nav class="-mb-px flex gap-1 overflow-x-auto">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.label"
                        :href="tab.href"
                        :class="[
                            'flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors',
                            route().current(tab.routeMatch)
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                        ]"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/>
                        </svg>
                        {{ tab.label }}
                    </Link>
                </nav>
            </div>

            <!-- Slot untuk konten tiap tab (tidak digunakan karena setiap sub-halaman render layout sendiri) -->
            <div class="rounded-lg border bg-card p-6 text-center text-muted-foreground">
                <p class="text-sm">Silakan pilih tab di atas untuk mengedit paket ini.</p>
                <div class="flex justify-center gap-3 mt-4">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.label"
                        :href="tab.href"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90 transition"
                    >
                        {{ tab.label }}
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
