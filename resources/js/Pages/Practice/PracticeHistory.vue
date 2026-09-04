<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import { Icon } from '@iconify/vue';
import { timeAgo } from '@/lib/utils';
const route = inject('route');

const props = defineProps({
    sessions: { type: Array, default: () => [] },
});

const search = ref('');
const sortBy = ref('newest');

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

        <template #header-title><Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 inline-block mr-1.5 align-middle" /> Riwayat Tugas</template>
        <template #header-sub>
            Semua tugas yang sudah kamu kerjakan
        </template>

        <!-- Summary Stats -->
        <div v-if="sessions.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-card border border-border/60 rounded-2xl p-5 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                    <Icon icon="mdi:clipboard-text-outline" class="w-6 h-6 text-primary" />
                </div>
                <p class="text-3xl font-extrabold bg-gradient-to-br from-primary to-primary/70 bg-clip-text text-transparent">{{ sessions.length }}</p>
                <p class="text-xs text-muted-foreground mt-1 font-medium">Total Tugas</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-yellow-100 to-yellow-50 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                    <Icon icon="mdi:trophy" class="w-6 h-6 text-yellow-600" />
                </div>
                <p class="text-3xl font-extrabold bg-gradient-to-br from-yellow-600 to-yellow-500 bg-clip-text text-transparent">{{ bestScore.toFixed(0) }}</p>
                <p class="text-xs text-muted-foreground mt-1 font-medium">Nilai Tertinggi</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                    <Icon icon="mdi:chart-line" class="w-6 h-6 text-emerald-600" />
                </div>
                <p class="text-3xl font-extrabold bg-gradient-to-br from-emerald-600 to-emerald-500 bg-clip-text text-transparent">{{ avgScore.toFixed(0) }}</p>
                <p class="text-xs text-muted-foreground mt-1 font-medium">Rata-rata</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 text-center shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <Link :href="route('practice.statistics')" class="block">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-pine-teal/15 to-pine-teal/5 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                        <Icon icon="mdi:chart-bar" class="w-6 h-6 text-pine-teal" />
                    </div>
                    <p class="text-xs text-muted-foreground mt-1 font-medium">Lihat Statistik</p>
                </Link>
            </div>
        </div>

        <!-- Search & Sort Bar -->
        <div v-if="sessions.length > 0" class="flex flex-col sm:flex-row gap-3 mb-5 anim-fade-in">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                <Input v-model="search" type="text" placeholder="Cari soal..." class="pl-10 h-11 rounded-xl border-border/60 focus:border-primary transition-colors" />
            </div>
            <Select v-model="sortBy" class="min-w-[160px] h-11 rounded-xl border-border/60">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="highest">Nilai Tertinggi</option>
                <option value="lowest">Nilai Terendah</option>
            </Select>
        </div>

        <!-- Empty State -->
        <div v-if="sessions.length === 0" class="flex flex-col items-center justify-center py-24 bg-card rounded-2xl border border-border/60 shadow-sm anim-fade-in">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-muted/80 to-muted/40 flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-12 h-12 text-muted-foreground/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">Belum Ada Riwayat</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-xs leading-relaxed">Kamu belum mengerjakan tugas apapun. Yuk mulai sekarang!</p>
            <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-primary/80 text-primary-foreground px-7 py-3 rounded-xl text-sm font-semibold hover:from-primary/90 hover:to-primary/70 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                <Icon icon="mdi:package-variant" class="w-4 h-4" /> Lihat Soal Tugas
            </Link>
        </div>

        <!-- No Result State (search) -->
        <div v-else-if="filteredSessions.length === 0" class="flex flex-col items-center justify-center py-20 bg-card rounded-2xl border border-border/60 shadow-sm anim-fade-in">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-muted/80 to-muted/40 flex items-center justify-center mb-4 shadow-inner">
                <Icon icon="mdi:magnify" class="w-10 h-10 text-muted-foreground/70" />
            </div>
            <p class="text-base font-semibold text-foreground mb-1">Tidak Ditemukan</p>
            <p class="text-sm text-muted-foreground mb-4">Coba kata kunci lain</p>
            <button @click="search = ''" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors underline underline-offset-2">Reset pencarian</button>
        </div>

        <!-- Sessions Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="(session, idx) in filteredSessions" :key="session.id"
                 class="anim-history-card bg-card border border-border/60 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-default">

                <!-- Score Header Bar -->
                <div class="h-1.5 w-full bg-muted/50">
                    <div :class="['h-full rounded-full transition-all duration-700 ease-out', scoreBarColor(Number(session.total_score || 0))]"
                         :style="{ width: Math.min(Number(session.total_score || 0), 100) + '%' }"></div>
                </div>

                <div class="p-5">
                    <!-- Package Title & Date -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-sm leading-tight line-clamp-2 mb-1.5 group-hover:text-primary transition-colors">{{ session.package?.title || 'Tugas' }}</h3>
                        <p class="text-xs text-muted-foreground flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                            {{ timeAgo(session.created_at) }}
                        </p>
                    </div>

                    <!-- Score Badge -->
                    <div :class="['inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold ring-1 mb-4 shadow-sm', scoreGrade(Number(session.total_score || 0)).bg, scoreGrade(Number(session.total_score || 0)).color, scoreGrade(Number(session.total_score || 0)).ring]">
                        <span class="text-base leading-none"><Icon :icon="Number(session.total_score || 0) >= 90 ? 'mdi:trophy' : Number(session.total_score || 0) >= 75 ? 'mdi:check-circle' : Number(session.total_score || 0) >= 60 ? 'mdi:chart-bar' : 'mdi:book-open-variant'" class="w-4 h-4" /></span>
                        <span>Nilai: {{ Number(session.total_score || 0).toFixed(0) }}</span>
                        <span class="opacity-70">• {{ scoreGrade(Number(session.total_score || 0)).label }}</span>
                    </div>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="bg-emerald-50/80 rounded-xl p-2.5 text-center border border-emerald-100/60">
                            <p class="text-sm font-bold text-emerald-600">{{ session.correct_answer || 0 }}</p>
                            <p class="text-[10px] text-emerald-700/60 mt-0.5">Benar</p>
                        </div>
                        <div class="bg-red-50/80 rounded-xl p-2.5 text-center border border-red-100/60">
                            <p class="text-sm font-bold text-red-500">{{ session.wrong_answer || 0 }}</p>
                            <p class="text-[10px] text-red-700/60 mt-0.5">Salah</p>
                        </div>
                        <div class="bg-muted/50 rounded-xl p-2.5 text-center border border-border/40">
                            <p class="text-sm font-bold text-muted-foreground">{{ session.unanswered || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground/60 mt-0.5">Kosong</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-3 border-t border-border/40">
                        <span class="text-xs text-muted-foreground flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ formatDuration(session.duration_seconds) }}
                        </span>
                        <Link :href="route('practice.show', session.id)"
                              class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/70 transition-all duration-300 group-hover:gap-2.5">
                            Lihat Detail
                            <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Link (bottom) -->
        <div v-if="sessions.length > 0" class="mt-8 text-center anim-fade-in">
            <Link :href="route('practice.statistics')" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/70 transition-all duration-300 hover:gap-3 group">
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Lihat Statistik Lengkap
                <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </Link>
        </div>
    </UserLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.anim-history-card {
    animation: fadeInUp 0.5s ease-out both;
}
.anim-history-card:nth-child(1) { animation-delay: 0s; }
.anim-history-card:nth-child(2) { animation-delay: 0.07s; }
.anim-history-card:nth-child(3) { animation-delay: 0.14s; }
.anim-history-card:nth-child(4) { animation-delay: 0.21s; }
.anim-history-card:nth-child(5) { animation-delay: 0.28s; }
.anim-history-card:nth-child(6) { animation-delay: 0.35s; }
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.anim-fade-in { animation: fadeIn 0.4s ease-out both; }
</style>
