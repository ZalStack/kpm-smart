<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const route = inject('route');
const props = defineProps({ announcement: Object });

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getPriorityConfig(p) {
    const map = {
        low: { label: 'Rendah', color: 'bg-slate-100 text-slate-700 border-slate-200', icon: 'mdi:flag-outline', dot: 'bg-slate-400' },
        normal: { label: 'Normal', color: 'bg-blue-50 text-blue-700 border-blue-200', icon: 'mdi:bell-outline', dot: 'bg-blue-500' },
        high: { label: 'Penting', color: 'bg-amber-50 text-amber-700 border-amber-200', icon: 'mdi:alert-circle-outline', dot: 'bg-amber-500' },
        urgent: { label: 'Mendesak', color: 'bg-red-50 text-red-700 border-red-200', icon: 'mdi:fire', dot: 'bg-red-500' },
    };
    return map[p] || map.normal;
}
</script>

<template>
    <AdminLayout>
        <Head :title="announcement.title + ' - Pengumuman Admin'" />

        <template #header-title>Detail Pengumuman</template>
        <template #header-sub>{{ formatDate(announcement.created_at) }}</template>

        <div class="w-full space-y-5">
            <!-- Back Link -->
            <Link :href="route('admin.announcements.index')"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-primary transition-colors anim-fade-in-up">
                <Icon icon="mdi:arrow-left" class="w-4 h-4" /> Kembali ke Daftar Pengumuman
            </Link>

            <!-- Main Content Card -->
            <div class="max-w-3xl mx-auto anim-fade-in-up anim-delay-1">
                <div class="bg-card border border-border/60 rounded-2xl shadow-sm overflow-hidden">
                    <!-- Card Header -->
                    <div class="p-6 sm:p-8 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold', getPriorityConfig(announcement.priority).color]">
                                <Icon :icon="getPriorityConfig(announcement.priority).icon" class="w-3.5 h-3.5" />
                                {{ getPriorityConfig(announcement.priority).label }}
                            </span>
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold',
                                announcement.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-muted text-muted-foreground border-border']">
                                <Icon :icon="announcement.is_active ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="w-3.5 h-3.5" />
                                {{ announcement.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-foreground leading-tight">
                            {{ announcement.title }}
                        </h1>
                    </div>

                    <!-- Meta Info Bar -->
                    <div class="px-6 sm:px-8 py-3.5 border-b border-border/50 bg-muted/20 flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                        <span class="inline-flex items-center gap-1.5">
                            <Icon icon="mdi:account-outline" class="w-4 h-4" />
                            <span>Dibuat oleh: <span class="font-semibold text-foreground">{{ announcement.creator?.name || 'Admin' }}</span></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <Icon icon="mdi:clock-outline" class="w-4 h-4" />
                            <span>{{ formatDate(announcement.created_at) }}</span>
                        </span>
                    </div>

                    <!-- Content Area -->
                    <div class="p-6 sm:p-8">
                        <div class="prose prose-sm max-w-none text-foreground/90 leading-relaxed whitespace-pre-wrap text-sm">
                            {{ announcement.content }}
                        </div>

                        <!-- Empty Content State -->
                        <div v-if="!announcement.content" class="flex flex-col items-center gap-3 py-10 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center">
                                <Icon icon="mdi:text-box-outline" class="w-7 h-7 text-muted-foreground/40" />
                            </div>
                            <p class="text-sm text-muted-foreground">Tidak ada isi pengumuman.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Info -->
            <div class="max-w-3xl mx-auto anim-fade-in-up anim-delay-2">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                    <Icon icon="mdi:information-outline" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-xs font-bold text-amber-800">Notifikasi Terkirim Otomatis</p>
                        <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                            Pengumuman ini telah dikirimkan ke semua siswa aktif sebagai notifikasi dan dapat diklik untuk melihat detail di panel siswa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
