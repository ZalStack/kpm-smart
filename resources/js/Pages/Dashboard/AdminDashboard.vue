<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/shared/StatCard.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    totalUsers: { type: Number, default: 0 },
    totalPackages: { type: Number, default: 0 },
    totalSessions: { type: Number, default: 0 },
    packageStats: { type: Array, default: () => [] },
});
</script>

<template>
    <AdminLayout>
        <Head title="Dasbor Administrator - PR KK" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:view-dashboard" class="w-5 h-5" />
                </span>
                <span>Dasbor Administrator</span>
            </div>
        </template>
        <template #header-sub>
            Pusat kendali manajemen siswa, PR pekanan, dan statistik evaluasi pembelajaran
        </template>

        <div class="space-y-8">
            <!-- Stats Grid -->
            <div class="grid-stats">
                <StatCard
                    title="Total Siswa Terdaftar"
                    :value="totalUsers"
                    color="indigo"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'/></svg>"
                />
                <StatCard
                    title="Total Paket Soal"
                    :value="totalPackages"
                    color="blue"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/></svg>"
                />
                <StatCard
                    title="Total Sesi Ujian Selesai"
                    :value="totalSessions"
                    color="emerald"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'/></svg>"
                />
            </div>

            <!-- Quick Action Cards Grid -->
            <div>
                <h3 class="section-eyebrow">Akses Cepat</h3>
                <h2 class="section-title">Pengelolaan Utama</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <Link :href="route('admin.packages.index')"
                          class="group ui-card-hover p-5 sm:p-6">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <Icon icon="mdi:book-open-page-variant" class="w-6 h-6" />
                        </div>
                        <h4 class="font-extrabold text-sm sm:text-base text-foreground mb-1.5 group-hover:text-primary transition-colors">PR Pekanan</h4>
                        <p class="text-xs text-muted-foreground leading-relaxed">Kelola paket PR pekanan, atur jadwal aktif, dan susun butir soal.</p>
                    </Link>

                    <Link :href="route('admin.practice-statistics.index')"
                          class="group ui-card-hover p-5 sm:p-6">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-blue flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <Icon icon="mdi:chart-box-outline" class="w-6 h-6" />
                        </div>
                        <h4 class="font-extrabold text-sm sm:text-base text-foreground mb-1.5 group-hover:text-primary transition-colors">Statistik Evaluasi</h4>
                        <p class="text-xs text-muted-foreground leading-relaxed">Analisis nilai rata-rata, tingkat kelulusan, dan akurasi kelas.</p>
                    </Link>

                    <Link :href="route('admin.users.index')"
                          class="group ui-card-hover p-5 sm:p-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-brand-amber flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <Icon icon="mdi:account-group-outline" class="w-6 h-6" />
                        </div>
                        <h4 class="font-extrabold text-sm sm:text-base text-foreground mb-1.5 group-hover:text-primary transition-colors">Manajemen Siswa</h4>
                        <p class="text-xs text-muted-foreground leading-relaxed">Kelola data akun siswa, penempatan kelas, bidang studi, dan profil.</p>
                    </Link>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="ui-card p-0">
                <div class="border-b border-border p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="section-title">Aktivitas Terbaru</h3>
                            <p class="text-xs text-muted-foreground">Log masuk dan aktivitas sistem</p>
                        </div>
                        <Link :href="route('admin.login-logs.index')" class="back-link">Lihat Semua Log <Icon icon="mdi:arrow-right" class="h-3 w-3 ml-0.5" /></Link>
                    </div>
                </div>
                <div class="divide-y divide-border/50">
                    <div v-for="i in 5" :key="i" class="p-5 sm:p-6 flex items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center">
                                <Icon icon="mdi:login" class="h-5 w-5 text-muted-foreground/50" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-extrabold text-foreground truncate">Admin Login</p>
                                <p class="text-[11px] text-muted-foreground">Sesi login administrator</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-extrabold text-primary">Baru saja</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>