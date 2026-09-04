<script setup>
import { inject, computed } from 'vue';
const route = inject('route');

import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import StatCard from '@/Components/shared/StatCard.vue';

const page = usePage();
const user = page.props.auth?.user;

const props = defineProps({
    packages: { type: Array, default: () => [] },
    totalAttempts: { type: Number, default: 0 },
    bestScore: { type: Number, default: 0 },
    averageScore: { type: Number, default: 0 },
    gamification: { type: Object, default: () => ({}) },
});

const g = computed(() => props.gamification);
const xpProgress = computed(() => g.value.xp_in_level || 0);
</script>

<template>
    <UserLayout>
        <Head title="Dasbor - KPM SMART" />

        <template #header-title>Dasbor</template>
        <template #header-sub>Selamat datang, {{ user?.name }}</template>

        <!-- Welcome Banner -->
        <div class="anim-fade-in-up rounded-2xl bg-gradient-to-r from-fern via-hunter-green to-emerald-700 p-6 sm:p-8 text-white mb-8 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/20 blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-white/5 blur-3xl"></div>
            </div>
            <div class="relative z-10">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">Halo, {{ user?.name }}! <Icon icon="mdi:hands-up" class="w-6 h-6 inline-block align-middle mr-1" /></h2>
                <p class="text-white/80 text-sm sm:text-base max-w-lg">Selamat datang di KPM SMART. Pilih soal tugas dan mulai berlatih sekarang.</p>
                <Link :href="route('packages.index')" class="inline-flex items-center gap-2 mt-4 bg-white/20 hover:bg-white/30 backdrop-blur px-5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 hover:shadow-lg">
                    Kerjakan Tugas
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
        </div>

        <!-- Gamification Widget -->
        <div class="anim-fade-in-up anim-delay-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-8">
            <!-- Level & XP Card -->
            <div class="rounded-2xl border border-amber-200/60 bg-card p-5 sm:p-6 shadow-card hover:shadow-card-hover hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-lg font-black shadow-md">
                        {{ g.level || 1 }}
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Level</p>
                        <p class="text-sm font-bold">{{ g.badge || 'Pemula' }}</p>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs text-muted-foreground">
                        <span>XP</span>
                        <span class="font-semibold">{{ g.xp || 0 }} / {{ ((g.level || 1) * 100) }}</span>
                    </div>
                    <div class="h-2.5 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-700" :style="{ width: xpProgress + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Streak Card -->
            <div class="rounded-2xl border border-orange-200/60 bg-card p-5 sm:p-6 shadow-card hover:shadow-card-hover hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-xl"><Icon icon="mdi:fire" class="w-5 h-5 text-orange-500" /></div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Streak</p>
                        <p class="text-xl sm:text-2xl font-bold">{{ g.streak?.current || 0 }} <span class="text-xs font-normal text-muted-foreground">hari</span></p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">Streak terbaik: <span class="font-semibold text-foreground">{{ g.streak?.best || 0 }} hari</span></p>
            </div>

            <!-- Summary Card -->
            <div class="rounded-2xl border border-emerald-200/60 bg-card p-5 sm:p-6 shadow-card hover:shadow-card-hover hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-xl"><Icon icon="mdi:target" class="w-5 h-5 text-emerald-500" /></div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Ringkasan</p>
                        <p class="text-sm font-bold">{{ totalAttempts }} percobaan</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-muted/30 rounded-lg px-2.5 py-2">
                        <span class="text-muted-foreground">Skor terbaik</span>
                        <p class="font-bold text-foreground">{{ bestScore }}%</p>
                    </div>
                    <div class="bg-muted/30 rounded-lg px-2.5 py-2">
                        <span class="text-muted-foreground">Rata-rata</span>
                        <p class="font-bold text-foreground">{{ averageScore }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="anim-fade-in-up anim-delay-2 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
            <StatCard title="Total Percobaan" :value="totalAttempts" color="pine-teal" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />
            <StatCard title="Skor Tertinggi" :value="bestScore + '%'" color="fern" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0116.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.015 6.015 0 01-2.77.665 6.015 6.015 0 01-2.77-.665'/></svg>" />
            <StatCard title="Rata-rata Skor" :value="averageScore + '%'" color="dry-sage" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'/></svg>" />
            <StatCard title="Soal Aktif" :value="packages.length" color="hunter-green" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/></svg>" />
        </div>

        <!-- Quick Actions -->
        <div class="anim-fade-in-up anim-delay-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            <Link :href="route('packages.index')" class="group rounded-2xl border bg-card p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-fern/10 flex items-center justify-center text-fern mb-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base mb-1">Tugas PR</h3>
                <p class="text-xs sm:text-sm text-muted-foreground">Lihat dan pilih soal tugas</p>
            </Link>
            <Link :href="route('practice.history')" class="group rounded-2xl border bg-card p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-pine-teal/10 flex items-center justify-center text-pine-teal mb-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base mb-1">Riwayat Tugas</h3>
                <p class="text-xs sm:text-sm text-muted-foreground">Lihat riwayat pengerjaan</p>
            </Link>
            <Link :href="route('leaderboard')" class="group rounded-2xl border bg-card p-6 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 mb-3 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516"/></svg>
                </div>
                <h3 class="font-semibold text-sm sm:text-base mb-1">Papan Peringkat</h3>
                <p class="text-xs sm:text-sm text-muted-foreground">Lihat peringkatmu</p>
            </Link>
        </div>
    </UserLayout>
</template>
