<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');

const props = defineProps({
    announcement: { type: Object, required: true },
});

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function priorityConfig(p) {
    const map = {
        low: { label: 'Rendah', cls: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200' },
        normal: { label: 'Informasi', cls: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200' },
        high: { label: 'Penting', cls: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-200' },
        urgent: { label: 'Mendesak', cls: 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border-red-200' }
    };
    return map[p] || map.normal;
}
</script>

<template>
    <UserLayout>
        <Head :title="announcement.title + ' - Pengumuman KPM SMART'" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <Icon icon="mdi:bullhorn-outline" class="w-5 h-5" />
                </span>
                <span>Detail Pengumuman</span>
            </div>
        </template>
        <template #header-sub>
            Informasi resmi dari tim pengajar dan administrator KPM
        </template>

        <div class="max-w-3xl mx-auto space-y-5">
            <!-- Back button -->
            <Link :href="route('user.notifications.index')"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Pusat Notifikasi
            </Link>

            <!-- Announcement Card -->
            <div class="bg-card border border-border/60 rounded-3xl overflow-hidden shadow-sm">
                <!-- Color bar indicator -->
                <div class="h-2 w-full"
                     :class="announcement.priority === 'urgent' ? 'bg-red-500' : announcement.priority === 'high' ? 'bg-amber-500' : 'bg-primary'"></div>

                <div class="p-6 sm:p-8 space-y-6">
                    <!-- Meta Header -->
                    <div class="flex flex-wrap items-center gap-2.5 pb-4 border-b border-border/50">
                        <span :class="['text-xs font-bold px-3 py-1 rounded-full border', priorityConfig(announcement.priority).cls]">
                            {{ priorityConfig(announcement.priority).label }}
                        </span>
                        <span class="text-xs text-muted-foreground inline-flex items-center gap-1">
                            <Icon icon="mdi:calendar-clock" class="w-3.5 h-3.5" />
                            {{ formatDate(announcement.created_at) }}
                        </span>
                        <span class="text-xs text-muted-foreground inline-flex items-center gap-1">
                            <Icon icon="mdi:account-circle-outline" class="w-3.5 h-3.5" />
                            {{ announcement.creator?.name || 'Administrator' }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-xl sm:text-2xl font-black text-foreground tracking-tight leading-snug">
                        {{ announcement.title }}
                    </h1>

                    <!-- Body Content -->
                    <div class="p-5 sm:p-6 bg-muted/20 rounded-2xl border border-border/40 text-foreground/90 text-sm sm:text-[15px] leading-relaxed whitespace-pre-wrap">
                        {{ announcement.content }}
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-border/50">
                        <Link :href="route('user.notifications.index')">
                            <Button variant="outline" size="sm" class="gap-1.5 text-xs font-semibold">
                                <Icon icon="mdi:bell-outline" class="w-4 h-4" /> Lihat Notifikasi Lainnya
                            </Button>
                        </Link>
                        <Link :href="route('user.dashboard')">
                            <Button size="sm" class="gap-1.5 text-xs font-semibold shadow-sm">
                                <Icon icon="mdi:view-dashboard-outline" class="w-4 h-4" /> Ke Halaman Dasbor
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
