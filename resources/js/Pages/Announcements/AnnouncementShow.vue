<script setup>
import { Icon } from '@iconify/vue/offline';

const props = defineProps({
    announcement: {
        type: Object,
        required: true,
    },
    formatTanggal: {
        type: Function,
        required: true,
    },
});

// Helper functions for styling based on priority
const getPriorityStyles = (priority) => {
    switch (priority) {
        case 'urgent':
            return {
                badge: 'bg-rose-100 text-rose-700 ring-rose-500/20 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-400/20',
                icon: 'mdi:alert-circle-outline',
                iconColor: 'text-rose-600 dark:text-rose-300',
                gradient: 'from-rose-500/10 via-transparent to-transparent'
            };
        case 'info':
            return {
                badge: 'bg-sky-100 text-sky-700 ring-sky-500/20 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-400/20',
                icon: 'mdi:information-outline',
                iconColor: 'text-sky-600 dark:text-sky-300',
                gradient: 'from-sky-500/10 via-transparent to-transparent'
            };
        default: // warning
            return {
                badge: 'bg-amber-100 text-amber-700 ring-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/20',
                icon: 'mdi:warning-outline',
                iconColor: 'text-amber-600 dark:text-amber-300',
                gradient: 'from-amber-500/10 via-transparent to-transparent'
            };
    }
};

const getPriorityLabel = (priority) => {
    switch (priority) {
        case 'urgent': return 'Penting';
        case 'info': return 'Informasi';
        default: return 'Peringatan';
    }
};
</script>

<template>
    <Head :title="announcement.judul" />

    <div>
        <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 p-6 text-white shadow-xl sm:p-8">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%22')] opacity-30"></div>

            <div class="relative">
                <div class="mb-6">
                    <h1 class="text-2xl font-black leading-tight sm:text-3xl">
                        {{ announcement.judul }}
                    </h1>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset"
                            :class="getPriorityStyles(announcement.prioritas).badge"
                        >
                            <Icon :icon="getPriorityStyles(announcement.prioritas).icon" width="14" />
                            {{ getPriorityLabel(announcement.prioritas) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-100">
                            <Icon icon="mdi:calendar-blank-outline" width="14" />
                            {{ formatTanggal(announcement.created_at) }}
                        </span>
                    </div>
                </div>

                <a
                    :href="route('user.dashboard')"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/20"
                >
                    <Icon icon="mdi:arrow-left" width="18" />
                    Kembali ke Dashboard
                </a>
            </div>
        </header>

        <div class="mt-6 ui-card p-6 sm:p-8">
            <div
                class="prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-slate-900 prose-p:text-slate-600 prose-p:leading-relaxed prose-strong:text-slate-800 prose-a:text-blue-600 hover:prose-a:text-blue-700"
                v-html="announcement.isi"
            ></div>
        </div>

        <div class="mt-6 flex flex-col gap-4 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 p-5 ring-1 ring-blue-100 sm:flex-row sm:items-center">
            <div class="flex-shrink-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                    <Icon icon="mdi:information-outline" width="24" />
                </div>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Butuh bantuan?</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Jika ada pertanyaan mengenai pengumuman ini, silakan hubungi admin atau guru BK sekolah kamu.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.prose) {
    --tw-prose-body: #334155;
    --tw-prose-headings: #0f172a;
    --tw-prose-links: #2563eb;
    --tw-prose-bold: #1e293b;
}
</style>
