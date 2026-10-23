<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue/offline';
const route = inject('route');

const props = defineProps({
    totalAttempts: { type: [Number, String], default: 0 },
    bestScore: { type: [Number, String], default: 0 },
    averageScore: { type: [Number, String], default: 0 },
    totalQuestions: { type: [Number, String], default: 0 },
    correctAnswers: { type: [Number, String], default: 0 },
    accuracy: { type: [Number, String], default: 0 },
    sessionsByPackage: { type: Array, default: () => [] },
});

const numBestScore = computed(() => Number(props.bestScore) || 0);
const numAverageScore = computed(() => Number(props.averageScore) || 0);
const numTotalQuestions = computed(() => Number(props.totalQuestions) || 0);
const numCorrectAnswers = computed(() => Number(props.correctAnswers) || 0);
const numAccuracy = computed(() => Number(props.accuracy) || 0);
const numTotalAttempts = computed(() => Number(props.totalAttempts) || 0);

function scoreGrade(score) {
    const s = Number(score);
    if (s >= 90) return { label: 'Luar Biasa!', icon: 'mdi:trophy', color: 'text-emerald-600' };
    if (s >= 75) return { label: 'Bagus!', icon: 'mdi:check-circle', color: 'text-blue-600' };
    if (s >= 60) return { label: 'Cukup Baik', icon: 'mdi:chart-bar', color: 'text-yellow-600' };
    return { label: 'Perlu Latihan', icon: 'mdi:book-open-variant', color: 'text-red-500' };
}

function pkgBarColor(score) {
    const s = Number(score);
    if (s >= 90) return 'from-emerald-400 to-emerald-600';
    if (s >= 75) return 'from-blue-400 to-blue-600';
    if (s >= 60) return 'from-yellow-400 to-yellow-600';
    return 'from-red-400 to-red-500';
}

const performanceGrade = computed(() => scoreGrade(numAverageScore.value));

const wrongAnswers = computed(() => numTotalQuestions.value - numCorrectAnswers.value);

const circumference = 2 * Math.PI * 40;
const accuracyOffset = computed(() => circumference - (numAccuracy.value / 100) * circumference);

const tips = [
    { icon: 'mdi:calendar-outline', text: 'Kerjakan tugas secara <strong class="text-foreground">rutin</strong> untuk meningkatkan pemahaman.' },
    { icon: 'mdi:magnify', text: 'Periksa <strong class="text-foreground">pembahasan</strong> untuk setiap soal yang salah.' },
    { icon: 'mdi:clock-outline', text: 'Latih <strong class="text-foreground">kecepatan menjawab</strong> agar lebih efisien.' },
    { icon: 'mdi:pencil-outline', text: 'Catat <strong class="text-foreground">pola soal</strong> yang sering keliru dijawab.' },
];
</script>

<template>
    <UserLayout>
        <Head title="Statistik Belajar - KPM SMART" />

        <template #header-title>Statistik Belajar</template>
        <template #header-sub>Ringkasan performa belajarmu secara keseluruhan.</template>

        <!-- Keadaan Kosong -->
        <div v-if="numTotalAttempts === 0" class="empty-state anim-fade-in">
            <div class="empty-state-icon">
                <Icon icon="mdi:chart-box-outline" class="h-8 w-8" />
            </div>
            <h3 class="text-lg font-bold text-foreground">Belum Ada Statistik</h3>
            <p class="mt-2 max-w-xs text-sm leading-relaxed text-muted-foreground">
                Kerjakan PR Pekanan terlebih dahulu untuk melihat statistik belajarmu!
            </p>
            <Link :href="route('user.packages.index')" class="btn-primary mt-6 px-7 py-3">
                <Icon icon="mdi:package-variant" class="h-4 w-4" />
                Lihat PR Pekanan
            </Link>
        </div>

        <div v-else class="space-y-6">
            <!-- Kartu Utama -->
            <div class="anim-fade-in-up grid-stats">
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Total</span>
                        <span class="icon-box-sm bg-primary/10 text-primary">
                            <Icon icon="mdi:pencil-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3">{{ numTotalAttempts }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Tugas dikerjakan</p>
                </div>

                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Terbaik</span>
                        <span class="icon-box-sm bg-amber-50 text-amber-600">
                            <Icon icon="mdi:trophy" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3 text-amber-600">{{ numBestScore.toFixed(0) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Nilai tertinggi</p>
                </div>

                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Rata-rata</span>
                        <span class="icon-box-sm bg-indigo-50 text-indigo-600">
                            <Icon icon="mdi:trending-up" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3" :class="performanceGrade.color">{{ numAverageScore.toFixed(0) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Nilai rata-rata</p>
                </div>

                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Akurasi</span>
                        <span class="icon-box-sm bg-emerald-50 text-emerald-600">
                            <Icon icon="mdi:target" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3 text-emerald-600">{{ numAccuracy.toFixed(0) }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">Jawaban benar</p>
                </div>
            </div>

            <!-- Akurasi & Level -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="ui-card anim-fade-in-up p-5 sm:p-6">
                    <h3 class="section-title mb-5 flex items-center gap-1.5">
                        <Icon icon="mdi:target" class="h-4 w-4 text-primary" />
                        Tingkat Akurasi
                    </h3>
                    <div class="flex flex-col items-center gap-6 sm:flex-row">
                        <div class="relative shrink-0">
                            <svg class="h-32 w-32 -rotate-90 drop-shadow-sm" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="hsl(var(--muted))" stroke-width="8" />
                                <circle
                                    cx="50" cy="50" r="40" fill="none" stroke="hsl(var(--primary))" stroke-width="8" stroke-linecap="round"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="accuracyOffset"
                                    class="transition-all duration-1000 ease-out"
                                />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xl font-bold text-emerald-600">{{ numAccuracy.toFixed(0) }}%</span>
                            </div>
                        </div>
                        <div class="w-full flex-1 space-y-3.5">
                            <div>
                                <div class="mb-1.5 flex justify-between text-xs">
                                    <span class="font-medium text-muted-foreground">Benar</span>
                                    <span class="font-bold text-emerald-600">{{ numCorrectAnswers }}</span>
                                </div>
                                <div class="progress-track">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-700" :style="{ width: numTotalQuestions > 0 ? (numCorrectAnswers / numTotalQuestions * 100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1.5 flex justify-between text-xs">
                                    <span class="font-medium text-muted-foreground">Salah</span>
                                    <span class="font-bold text-red-500">{{ wrongAnswers }}</span>
                                </div>
                                <div class="progress-track">
                                    <div class="h-full rounded-full bg-gradient-to-r from-red-400 to-red-500 transition-all duration-700" :style="{ width: numTotalQuestions > 0 ? (wrongAnswers / numTotalQuestions * 100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <p class="border-t border-border/60 pt-1 text-xs text-muted-foreground">
                                Total: <strong class="font-bold text-foreground">{{ numTotalQuestions }}</strong> soal dikerjakan
                            </p>
                        </div>
                    </div>
                </div>

                <div class="anim-fade-in-up relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-violet-700 p-5 text-white shadow-lg sm:p-6">
                    <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
                    <h3 class="mb-4 flex items-center gap-1.5 text-sm font-semibold text-white/90">
                        <Icon icon="mdi:star" class="h-4 w-4" />
                        Level Performa
                    </h3>
                    <div class="mb-5 flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 shadow-lg backdrop-blur-sm">
                            <Icon :icon="performanceGrade.icon" class="h-9 w-9 drop-shadow" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-2xl font-bold drop-shadow-sm">{{ performanceGrade.label }}</p>
                            <p class="mt-0.5 text-sm text-white/70">Rata-rata nilai: {{ numAverageScore.toFixed(1) }}</p>
                        </div>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-white/15 shadow-inner">
                        <div class="h-full rounded-full bg-white transition-all duration-700" :style="{ width: Math.min(numAverageScore, 100) + '%' }"></div>
                    </div>
                    <div class="mt-1.5 flex justify-between text-[10px] font-medium text-white/60">
                        <span>0</span><span>50</span><span>100</span>
                    </div>
                </div>
            </div>

            <!-- Per Paket -->
            <div v-if="sessionsByPackage.length > 0" class="ui-card anim-fade-in-up p-5 sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="section-title flex items-center gap-1.5">
                        <Icon icon="mdi:package-variant" class="h-5 w-5 text-primary" />
                        Statistik per Paket Soal
                    </h3>
                    <span class="chip bg-muted text-muted-foreground">{{ sessionsByPackage.length }} paket</span>
                </div>
                <div class="space-y-5">
                    <div v-for="pkg in sessionsByPackage" :key="pkg.package" class="group">
                        <div class="mb-2 flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium transition-colors group-hover:text-primary">{{ pkg.package }}</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ pkg.attempts }}x dikerjakan</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-emerald-600">{{ Number(pkg.avg_score || 0).toFixed(0) }}</p>
                                <p class="text-[10px] text-muted-foreground">rata-rata</p>
                            </div>
                        </div>
                        <div class="progress-track">
                            <div
                                :class="['h-full rounded-full transition-all duration-700', pkgBarColor(pkg.avg_score)]"
                                :style="{ width: Math.min(Number(pkg.avg_score || 0), 100) + '%' }"
                            ></div>
                        </div>
                        <div class="mt-1.5 flex justify-between text-[10px] font-medium text-muted-foreground">
                            <span>Rata-rata: <strong class="text-foreground">{{ Number(pkg.avg_score || 0).toFixed(1) }}</strong></span>
                            <span>Terbaik: <strong class="text-emerald-600">{{ Number(pkg.best_score || 0).toFixed(1) }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="ui-card anim-fade-in-up overflow-hidden">
                <div class="bg-gradient-to-r from-primary to-violet-700 p-4 text-white">
                    <h3 class="flex items-center gap-1.5 text-sm font-semibold">
                        <Icon icon="mdi:lightbulb-on-outline" class="h-5 w-5" />
                        Tips untuk Meningkatkan Nilai
                    </h3>
                </div>
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                    <div
                        v-for="tip in tips"
                        :key="tip.icon"
                        class="flex items-start gap-3 rounded-xl border border-border/30 bg-gradient-to-br from-muted/40 to-muted/20 p-3.5 transition-shadow duration-300 hover:shadow-md"
                    >
                        <span class="icon-box-sm shrink-0 bg-primary/10 text-primary">
                            <Icon :icon="tip.icon" class="h-5 w-5" />
                        </span>
                        <p class="text-sm leading-relaxed text-muted-foreground" v-html="tip.text"></p>
                    </div>
                </div>
            </div>

            <!-- Tautan Riwayat -->
            <div class="text-center">
                <Link :href="route('user.practice.history')" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:gap-3">
                    <Icon icon="mdi:history" class="h-4 w-4" />
                    Lihat Riwayat Lengkap
                    <Icon icon="mdi:arrow-right" class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
