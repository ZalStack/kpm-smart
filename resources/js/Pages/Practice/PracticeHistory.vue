<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
const route = inject('route');

const props = defineProps({
    sessions: { type: Array, default: () => [] },
});

const search = ref('');
const sortBy = ref('newest');

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}, ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function formatDuration(seconds) {
    if (!seconds) return '0 mnt';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    if (m >= 60) {
        const h = Math.floor(m / 60);
        const rm = m % 60;
        return `${h} jam ${rm} mnt`;
    }
    return m > 0 ? `${m} mnt ${s} dtk` : `${s} dtk`;
}

function scoreGrade(score) {
    const s = Number(score);
    if (s >= 90) return { label: 'Sangat Baik', color: 'text-emerald-600', bg: 'bg-emerald-50', ring: 'ring-emerald-200' };
    if (s >= 75) return { label: 'Baik', color: 'text-blue-600', bg: 'bg-blue-50', ring: 'ring-blue-200' };
    if (s >= 60) return { label: 'Cukup', color: 'text-yellow-600', bg: 'bg-yellow-50', ring: 'ring-yellow-200' };
    return { label: 'Perlu Latihan', color: 'text-red-500', bg: 'bg-red-50', ring: 'ring-red-200' };
}

function scoreBarColor(score) {
    const s = Number(score);
    if (s >= 90) return 'bg-emerald-500';
    if (s >= 75) return 'bg-blue-500';
    if (s >= 60) return 'bg-yellow-500';
    return 'bg-red-500';
}

const filteredSessions = computed(() => {
    let list = [...props.sessions];
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(s => (s.package?.title || '').toLowerCase().includes(q));
    }
    if (sortBy.value === 'newest') list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    else if (sortBy.value === 'oldest') list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    else if (sortBy.value === 'highest') list.sort((a, b) => (b.total_score || 0) - (a.total_score || 0));
    else if (sortBy.value === 'lowest') list.sort((a, b) => (a.total_score || 0) - (b.total_score || 0));
    return list;
});

const avgScore = computed(() => {
    if (!props.sessions.length) return 0;
    return props.sessions.reduce((s, e) => s + Number(e.total_score || 0), 0) / props.sessions.length;
});

const bestScore = computed(() => {
    if (!props.sessions.length) return 0;
    return Math.max(...props.sessions.map(s => Number(s.total_score || 0)));
});
</script>

<template>
    <UserLayout>
        <Head title="Riwayat Tugas - KPM SMART" />

        <template #header-title>📋 Riwayat Tugas</template>
        <template #header-sub>
            Semua tugas yang sudah kamu kerjakan
        </template>

        <!-- Summary Stats -->
        <div v-if="sessions.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-card border rounded-xl p-4 text-center hover:shadow-card-hover transition-shadow">
                <p class="text-2xl font-bold text-primary">{{ sessions.length }}</p>
                <p class="text-xs text-muted-foreground mt-0.5">Total Tugas</p>
            </div>
            <div class="bg-card border rounded-xl p-4 text-center hover:shadow-card-hover transition-shadow">
                <p class="text-2xl font-bold text-yellow-600">{{ bestScore.toFixed(0) }}</p>
                <p class="text-xs text-muted-foreground mt-0.5">Nilai Tertinggi</p>
            </div>
            <div class="bg-card border rounded-xl p-4 text-center hover:shadow-card-hover transition-shadow">
                <p class="text-2xl font-bold text-fern">{{ avgScore.toFixed(0) }}</p>
                <p class="text-xs text-muted-foreground mt-0.5">Rata-rata</p>
            </div>
            <div class="bg-card border rounded-xl p-4 text-center hover:shadow-card-hover transition-shadow">
                <Link :href="route('practice.statistics')" class="block">
                    <p class="text-2xl font-bold text-pine-teal">📊</p>
                    <p class="text-xs text-muted-foreground mt-0.5">Lihat Statistik</p>
                </Link>
            </div>
        </div>

        <!-- Search & Sort Bar -->
        <div v-if="sessions.length > 0" class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                <input v-model="search" type="text" placeholder="Cari paket..." class="w-full pl-9 pr-4 py-2 text-sm border border-input rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring" />
            </div>
            <select v-model="sortBy" class="px-3 py-2 text-sm border border-input rounded-lg bg-background focus:outline-none focus:ring-2 focus:ring-ring min-w-[140px]">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="highest">Nilai Tertinggi</option>
                <option value="lowest">Nilai Terendah</option>
            </select>
        </div>

        <!-- Empty State -->
        <div v-if="sessions.length === 0" class="flex flex-col items-center justify-center py-20 bg-card rounded-2xl border">
            <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">Belum Ada Riwayat</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-xs">Kamu belum mengerjakan tugas apapun. Yuk mulai sekarang!</p>
            <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-primary/90 transition">
                📦 Lihat Paket Tugas
            </Link>
        </div>

        <!-- No Result State (search) -->
        <div v-else-if="filteredSessions.length === 0" class="flex flex-col items-center justify-center py-16 bg-card rounded-2xl border">
            <p class="text-4xl mb-3">🔍</p>
            <p class="text-base font-semibold text-foreground mb-1">Tidak Ditemukan</p>
            <p class="text-sm text-muted-foreground">Coba kata kunci lain</p>
            <button @click="search = ''" class="mt-4 text-sm text-primary hover:underline">Reset pencarian</button>
        </div>

        <!-- Sessions Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="(session, idx) in filteredSessions" :key="session.id"
                 class="group bg-card border rounded-2xl overflow-hidden hover:shadow-card-hover hover:-translate-y-0.5 transition-all duration-200">

                <!-- Score Header Bar -->
                <div class="h-1.5 w-full bg-muted">
                    <div :class="['h-full rounded-full transition-all duration-700', scoreBarColor(Number(session.total_score || 0))]"
                         :style="{ width: Math.min(Number(session.total_score || 0), 100) + '%' }"></div>
                </div>

                <div class="p-5">
                    <!-- Package Title & Date -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-sm leading-tight line-clamp-2 mb-1">{{ session.package?.title || 'Tugas' }}</h3>
                        <p class="text-xs text-muted-foreground flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                            {{ formatDate(session.created_at) }}
                        </p>
                    </div>

                    <!-- Score Badge -->
                    <div :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ring-1 mb-4', scoreGrade(Number(session.total_score || 0)).bg, scoreGrade(Number(session.total_score || 0)).color, scoreGrade(Number(session.total_score || 0)).ring]">
                        <span class="text-base leading-none">{{ Number(session.total_score || 0) >= 90 ? '🏆' : Number(session.total_score || 0) >= 75 ? '✅' : Number(session.total_score || 0) >= 60 ? '📊' : '📚' }}</span>
                        <span>Nilai: {{ Number(session.total_score || 0).toFixed(0) }}</span>
                        <span class="opacity-70">• {{ scoreGrade(Number(session.total_score || 0)).label }}</span>
                    </div>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="bg-emerald-50 rounded-lg p-2 text-center">
                            <p class="text-sm font-bold text-emerald-600">{{ session.correct_answer || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground">Benar</p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-2 text-center">
                            <p class="text-sm font-bold text-red-500">{{ session.wrong_answer || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground">Salah</p>
                        </div>
                        <div class="bg-muted rounded-lg p-2 text-center">
                            <p class="text-sm font-bold text-muted-foreground">{{ session.unanswered || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground">Kosong</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-3 border-t border-border/50">
                        <span class="text-xs text-muted-foreground flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ formatDuration(session.duration_seconds) }}
                        </span>
                        <Link :href="route('practice.show', session.id)"
                              class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary/80 transition group-hover:gap-2">
                            Lihat Detail
                            <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Link (bottom) -->
        <div v-if="sessions.length > 0" class="mt-6 text-center">
            <Link :href="route('practice.statistics')" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Lihat Statistik Lengkap
            </Link>
        </div>
    </UserLayout>
</template>
