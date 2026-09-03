<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
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
    if (s >= 90) return { label: 'Luar Biasa!', emoji: '🏆', color: 'text-emerald-600' };
    if (s >= 75) return { label: 'Bagus!', emoji: '✅', color: 'text-blue-600' };
    if (s >= 60) return { label: 'Cukup Baik', emoji: '📊', color: 'text-yellow-600' };
    return { label: 'Perlu Latihan', emoji: '📚', color: 'text-red-500' };
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
</script>

<template>
    <UserLayout>
        <Head title="Statistik Belajar - KPM Belajar Online" />

        <template #header-title>📊 Statistik Belajar</template>
        <template #header-sub>
            Ringkasan performa belajarmu secara keseluruhan
        </template>

        <!-- Empty State -->
        <div v-if="numTotalAttempts === 0" class="flex flex-col items-center justify-center py-20 bg-card rounded-2xl border">
            <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">Belum Ada Statistik</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-xs">Kerjakan tugas terlebih dahulu untuk melihat statistikmu!</p>
            <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-primary/90 transition">
                📦 Lihat Paket Tugas
            </Link>
        </div>

        <div v-else class="space-y-6">

            <!-- Top Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-card border rounded-2xl p-5 hover:shadow-card-hover transition-shadow group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-xl">📝</div>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">Total</span>
                    </div>
                    <p class="text-3xl font-bold text-foreground">{{ numTotalAttempts }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Tugas Dikerjakan</p>
                </div>

                <div class="bg-card border rounded-2xl p-5 hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center text-xl">🏆</div>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">Terbaik</span>
                    </div>
                    <p class="text-3xl font-bold text-yellow-600">{{ numBestScore.toFixed(0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Nilai Tertinggi</p>
                </div>

                <div class="bg-card border rounded-2xl p-5 hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-xl">📈</div>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">Avg</span>
                    </div>
                    <p :class="['text-3xl font-bold', performanceGrade.color]">{{ numAverageScore.toFixed(0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1">Rata-rata Nilai</p>
                </div>

                <div class="bg-card border rounded-2xl p-5 hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-fern/10 flex items-center justify-center text-xl">🎯</div>
                        <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">Akurasi</span>
                    </div>
                    <p class="text-3xl font-bold text-fern">{{ numAccuracy.toFixed(0) }}%</p>
                    <p class="text-xs text-muted-foreground mt-1">Jawaban Benar</p>
                </div>
            </div>

            <!-- Accuracy & Answer Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="bg-card border rounded-2xl p-6">
                    <h3 class="font-semibold text-sm mb-5">🎯 Tingkat Akurasi</h3>
                    <div class="flex items-center gap-6">
                        <div class="relative flex-shrink-0">
                            <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="hsl(var(--muted))" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#588157"
                                        stroke-width="10" stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="accuracyOffset"
                                        class="transition-all duration-1000" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-fern">{{ numAccuracy.toFixed(0) }}%</span>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <div class="flex justify-between text-xs mb-1"><span class="text-muted-foreground">Benar</span><span class="font-semibold text-emerald-600">{{ numCorrectAnswers }}</span></div>
                                <div class="h-2 bg-muted rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-700" :style="{ width: numTotalQuestions > 0 ? (numCorrectAnswers/numTotalQuestions*100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1"><span class="text-muted-foreground">Salah</span><span class="font-semibold text-red-500">{{ wrongAnswers }}</span></div>
                                <div class="h-2 bg-muted rounded-full overflow-hidden">
                                    <div class="h-full bg-red-400 rounded-full transition-all duration-700" :style="{ width: numTotalQuestions > 0 ? (wrongAnswers/numTotalQuestions*100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground pt-1">Total: <strong class="text-foreground">{{ numTotalQuestions }}</strong> soal dikerjakan</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-primary to-pine-teal rounded-2xl p-6 text-white">
                    <h3 class="font-semibold text-sm mb-4 text-white/90">⭐ Level Performa</h3>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="text-5xl">{{ performanceGrade.emoji }}</div>
                        <div>
                            <p class="text-2xl font-bold">{{ performanceGrade.label }}</p>
                            <p class="text-white/70 text-sm">Rata-rata nilai: {{ numAverageScore.toFixed(1) }}</p>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-full h-2 overflow-hidden">
                        <div class="h-full bg-white rounded-full transition-all duration-700" :style="{ width: Math.min(numAverageScore, 100) + '%' }"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-white/60 mt-1">
                        <span>0</span><span>50</span><span>100</span>
                    </div>
                </div>
            </div>

            <!-- Per Package Stats -->
            <div v-if="sessionsByPackage.length > 0" class="bg-card border rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold">📦 Statistik per Paket</h3>
                    <span class="text-xs text-muted-foreground bg-muted px-2 py-1 rounded-full">{{ sessionsByPackage.length }} paket</span>
                </div>
                <div class="space-y-5">
                    <div v-for="pkg in sessionsByPackage" :key="pkg.package">
                        <div class="flex items-start justify-between mb-2">
                            <div class="min-w-0 flex-1 mr-4">
                                <p class="text-sm font-medium truncate">{{ pkg.package }}</p>
                                <p class="text-xs text-muted-foreground">{{ pkg.attempts }}x dikerjakan</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-fern">{{ Number(pkg.avg_score || 0).toFixed(0) }}</p>
                                <p class="text-[10px] text-muted-foreground">avg</p>
                            </div>
                        </div>
                        <div class="h-2.5 bg-muted rounded-full overflow-hidden">
                            <div :class="['h-full bg-gradient-to-r rounded-full transition-all duration-700', pkgBarColor(pkg.avg_score)]"
                                 :style="{ width: Math.min(Number(pkg.avg_score || 0), 100) + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-muted-foreground mt-1">
                            <span>Rata-rata: <strong class="text-foreground">{{ Number(pkg.avg_score || 0).toFixed(1) }}</strong></span>
                            <span>Terbaik: <strong class="text-emerald-600">{{ Number(pkg.best_score || 0).toFixed(1) }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="bg-card border rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-hunter-green to-pine-teal p-4 text-white">
                    <h3 class="font-semibold">💡 Tips untuk Meningkatkan Nilai</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-3 bg-muted/50 rounded-xl p-3">
                        <span class="text-xl flex-shrink-0">📅</span>
                        <p class="text-sm text-muted-foreground">Kerjakan tugas secara <strong class="text-foreground">rutin</strong> untuk meningkatkan pemahaman</p>
                    </div>
                    <div class="flex items-start gap-3 bg-muted/50 rounded-xl p-3">
                        <span class="text-xl flex-shrink-0">🔍</span>
                        <p class="text-sm text-muted-foreground">Periksa <strong class="text-foreground">pembahasan</strong> untuk soal yang salah</p>
                    </div>
                    <div class="flex items-start gap-3 bg-muted/50 rounded-xl p-3">
                        <span class="text-xl flex-shrink-0">⏱️</span>
                        <p class="text-sm text-muted-foreground">Latih <strong class="text-foreground">kecepatan menjawab</strong> agar lebih efisien</p>
                    </div>
                    <div class="flex items-start gap-3 bg-muted/50 rounded-xl p-3">
                        <span class="text-xl flex-shrink-0">📝</span>
                        <p class="text-sm text-muted-foreground">Catat <strong class="text-foreground">pola soal</strong> yang sering keliru dijawab</p>
                    </div>
                </div>
            </div>

            <!-- Link to History -->
            <div class="text-center">
                <Link :href="route('practice.history')" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    Lihat Riwayat Lengkap
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
