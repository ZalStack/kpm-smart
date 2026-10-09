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
    recentUsers: { type: Array, default: () => [] },
    packageStats: { type: Array, default: () => [] },
});
</script>

<template>
    <AdminLayout>
        <Head title="Dasbor Administrator - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:view-dashboard" class="w-5 h-5" />
                </span>
                <span>Dasbor Administrator</span>
            </div>
        </template>
        <template #header-sub>
            Pusat kendali manajemen siswa, paket soal, izin, dan statistik evaluasi pembelajaran
        </template>

        <div class="space-y-7">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                <StatCard
                    title="Total Siswa Terdaftar"
                    :value="totalUsers"
                    color="pine-teal"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'/></svg>"
                />
                <StatCard
                    title="Total Paket Soal"
                    :value="totalPackages"
                    color="fern"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/></svg>"
                />
                <StatCard
                    title="Total Sesi Ujian Selesai"
                    :value="totalSessions"
                    color="dry-sage"
                    icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'/></svg>"
                />
            </div>

            <!-- Quick Action Cards Grid -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-foreground/80 mb-3 flex items-center gap-1.5">
                    <Icon icon="mdi:lightning-bolt" class="w-4 h-4 text-amber-500" /> Akses Cepat Pengelolaan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link :href="route('admin.users.index')"
                          class="group rounded-2xl border border-border/60 bg-card p-5 shadow-sm hover:shadow-xl hover:border-primary/40 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <Icon icon="mdi:account-group-outline" class="w-6 h-6" />
                        </div>
                        <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-primary transition-colors">Kelola Siswa</h4>
                        <p class="text-xs text-muted-foreground">Tambah data siswa, import akun, dan atur kelas belajar.</p>
                    </Link>

                    <Link :href="route('admin.packages.index')"
                          class="group rounded-2xl border border-border/60 bg-card p-5 shadow-sm hover:shadow-xl hover:border-primary/40 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <Icon icon="mdi:package-variant-closed" class="w-6 h-6" />
                        </div>
                        <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-primary transition-colors">Bank Soal Tugas</h4>
                        <p class="text-xs text-muted-foreground">Buat paket soal baru, atur waktu, dan susun kunci jawaban.</p>
                    </Link>

                    <Link :href="route('admin.practice-statistics.index')"
                          class="group rounded-2xl border border-border/60 bg-card p-5 shadow-sm hover:shadow-xl hover:border-primary/40 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <Icon icon="mdi:chart-box-outline" class="w-6 h-6" />
                        </div>
                        <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-primary transition-colors">Statistik Evaluasi</h4>
                        <p class="text-xs text-muted-foreground">Analisis nilai rata-rata, tingkat kelulusan, dan akurasi kelas.</p>
                    </Link>

                    <Link :href="route('admin.leave-requests.index')"
                          class="group rounded-2xl border border-border/60 bg-card p-5 shadow-sm hover:shadow-xl hover:border-primary/40 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <Icon icon="mdi:calendar-account-outline" class="w-6 h-6" />
                        </div>
                        <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-primary transition-colors">Pengajuan Izin</h4>
                        <p class="text-xs text-muted-foreground">Tinjau dan setujui permohonan izin ketidakhadiran siswa.</p>
                    </Link>
                </div>
            </div>

            <!-- Recent Users Section -->
            <div class="rounded-3xl border border-border/60 bg-card shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-border/50 flex items-center justify-between bg-muted/20">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:account-clock-outline" class="w-5 h-5 text-primary" />
                        <h3 class="text-base sm:text-lg font-bold text-foreground">Siswa Terbaru Terdaftar</h3>
                    </div>
                    <Link :href="route('admin.users.index')"
                          class="text-xs sm:text-sm font-semibold text-primary hover:text-primary/80 transition inline-flex items-center gap-1">
                        Lihat Semua Siswa <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                    </Link>
                </div>

                <div class="divide-y divide-border/40">
                    <div v-if="recentUsers.length === 0" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-muted flex items-center justify-center text-muted-foreground">
                            <Icon icon="mdi:account-off-outline" class="w-8 h-8" />
                        </div>
                        <p class="text-sm font-semibold text-foreground">Belum Ada Pengguna Baru</p>
                        <p class="text-xs text-muted-foreground mt-1">Data siswa yang baru didaftarkan akan muncul di sini.</p>
                    </div>

                    <div v-for="u in recentUsers" :key="u.id"
                         class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4 hover:bg-muted/30 transition-colors">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-black text-sm flex-shrink-0">
                                {{ (u.name || 'U').charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-foreground truncate">{{ u.name }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ u.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-start sm:self-auto pl-13 sm:pl-0">
                            <span v-if="u.student_class" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-muted text-muted-foreground border">
                                Kelas {{ u.student_class }}
                            </span>
                            <span :class="['text-xs font-bold px-2.5 py-1 rounded-full border', u.is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/40']">
                                {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <Link :href="route('admin.users.show', u.id)"
                                  class="p-1.5 rounded-lg text-muted-foreground hover:text-primary hover:bg-muted transition">
                                <Icon icon="mdi:chevron-right" class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
