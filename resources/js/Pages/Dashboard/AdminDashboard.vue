<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/shared/StatCard.vue';

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
        <Head title="Dasbor Admin - KPM Belajar Online" />

        <template #header-title>Dasbor</template>
        <template #header-sub>Kelola sistem membership dengan mudah</template>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <StatCard title="Total Pengguna" :value="totalUsers" color="pine-teal" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'/></svg>" />
            <StatCard title="Total Paket" :value="totalPackages" color="fern" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/></svg>" />
            <StatCard title="Total Sesi" :value="totalSessions" color="dry-sage" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'/></svg>" />
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <Link :href="route('admin.users.index')" class="group rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-lg bg-pine-teal/10 flex items-center justify-center text-pine-teal mb-3 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <h3 class="font-semibold mb-1">Kelola Pengguna</h3>
                <p class="text-sm text-muted-foreground">Tambah, edit, atau hapus pengguna</p>
            </Link>
            <Link :href="route('admin.packages.index')" class="group rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-lg bg-fern/10 flex items-center justify-center text-fern mb-3 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="font-semibold mb-1">Kelola Paket</h3>
                <p class="text-sm text-muted-foreground">Buat dan kelola paket tugas</p>
            </Link>
            <Link :href="route('admin.practice-statistics.index')" class="group rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                <div class="w-10 h-10 rounded-lg bg-dry-sage/10 flex items-center justify-center text-dry-sage mb-3 group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                </div>
                <h3 class="font-semibold mb-1">Statistik</h3>
                <p class="text-sm text-muted-foreground">Lihat statistik pengerjaan tugas</p>
            </Link>
        </div>

        <!-- Recent Users -->
        <div class="rounded-xl border bg-card shadow-sm">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Pengguna Terbaru</h3>
                    <Link :href="route('admin.users.index')" class="text-sm text-primary hover:text-primary/80 font-medium transition">Lihat Semua</Link>
                </div>
            </div>
            <div class="divide-y">
                <div v-if="recentUsers.length === 0" class="p-6 text-center text-muted-foreground text-sm">
                    Belum ada pengguna
                </div>
                <div v-for="u in recentUsers" :key="u.id" class="flex items-center gap-4 px-6 py-4 hover:bg-muted/50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                        {{ (u.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ u.name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ u.email }}</p>
                    </div>
                    <span :class="['badge text-xs', u.is_active ? 'badge-success' : 'badge-danger']">
                        {{ u.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
