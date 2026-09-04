<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue';
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
</script>

<template>
    <UserLayout>
        <Head title="Statistik Belajar - KPM SMART" />

        <template #header-title><Icon icon="mdi:chart-bar" class="w-5 h-5 inline-block mr-1.5 align-middle" /> Statistik Belajar</template>
        <template #header-sub>
            Ringkasan performa belajarmu secara keseluruhan
        </template>


        <!-- Empty State -->
        <div v-if="numTotalAttempts === 0" class="flex flex-col items-center justify-center py-24 bg-card rounded-2xl border border-border/60 shadow-sm anim-stats-fade">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-muted/80 to-muted/40 flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-12 h-12 text-muted-foreground/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-foreground mb-2">Belum Ada Statistik</h3>
            <p class="text-sm text-muted-foreground mb-6 text-center max-w-xs leading-relaxed">Kerjakan tugas terlebih dahulu untuk melihat statistikmu!</p>
            <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-primary/80 text-primary-foreground px-7 py-3 rounded-xl text-sm font-semibold hover:from-primary/90 hover:to-primary/70 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                <Icon icon="mdi:package-variant" class="w-4 h-4" /> Lihat Soal Tugas
            </Link>
        </div>

        <div v-else class="space-y-6">

            <!-- Top Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="anim-stats-card bg-card border border-border/60 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"><Icon icon="mdi:pencil-outline" class="w-6 h-6 text-primary" /></div>
                        <span class="text-[10px] text-muted-foreground bg-muted/80 px-2.5 py-1 rounded-full font-medium uppercase tracking-wider">Total</span>
                    </div>
                    <p class="text-3xl font-extrabold bg-gradient-to-br from-foreground to-foreground/70 bg-clip-text text-transparent">{{ numTotalAttempts }}</p>
                    <p class="text-xs text-muted-foreground mt-1.5 font-medium">Tugas Dikerjakan</p>
                </div>

                <div class="anim-stats-card bg-card border border-border/60 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-yellow-100 to-yellow-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"><Icon icon="mdi:trophy" class="w-6 h-6 text-yellow-600" /></div>
                        <span class="text-[10px] text-muted-foreground bg-muted/80 px-2.5 py-1 rounded-full font-medium uppercase tracking-wider">Terbaik</span>
                    </div>
                    <p class="text-3xl font-extrabold bg-gradient-to-br from-yellow-600 to-yellow-500 bg-clip-text text-transparent">{{ numBestScore.toFixed(0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1.5 font-medium">Nilai Tertinggi</p>
                </div>

                <div class="anim-stats-card bg-card border border-border/60 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"><Icon icon="mdi:trending-up" class="w-6 h-6 text-primary" /></div>
                        <span class="text-[10px] text-muted-foreground bg-muted/80 px-2.5 py-1 rounded-full font-medium uppercase tracking-wider">Avg</span>
                    </div>
                    <p :class="['text-3xl font-extrabold', performanceGrade.color]">{{ numAverageScore.toFixed(0) }}</p>
                    <p class="text-xs text-muted-foreground mt-1.5 font-medium">Rata-rata Nilai</p>
                </div>

                <div class="anim-stats-card bg-card border border-border/60 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-default group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"><Icon icon="mdi:target" class="w-6 h-6 text-emerald-600" /></div>
                        <span class="text-[10px] text-muted-foreground bg-muted/80 px-2.5 py-1 rounded-full font-medium uppercase tracking-wider">Akurasi</span>
                    </div>
                    <p class="text-3xl font-extrabold bg-gradient-to-br from-emerald-600 to-emerald-500 bg-clip-text text-transparent">{{ numAccuracy.toFixed(0) }}%</p>
                    <p class="text-xs text-muted-foreground mt-1.5 font-medium">Jawaban Benar</p>
                </div>
            </div>

            <!-- Accuracy & Answer Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <div class="anim-stats-card bg-card border border-border/60 rounded-2xl p-6 shadow-sm">
                    <h3 class="font-semibold text-sm mb-5 flex items-center gap-1.5"><Icon icon="mdi:target" class="w-4 h-4 text-primary" /> Tingkat Akurasi</h3>
                    <div class="flex items-center gap-6">
                        <div class="relative flex-shrink-0">
                            <svg class="w-32 h-32 -rotate-90 drop-shadow-sm" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="hsl(var(--muted))" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="hsl(var(--primary))"
                                        stroke-width="8" stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="accuracyOffset"
                                        class="transition-all duration-1000 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-emerald-600">{{ numAccuracy.toFixed(0) }}%</span>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3.5">
                            <div>
                                <div class="flex justify-between text-xs mb-1.5"><span class="text-muted-foreground font-medium">Benar</span><span class="font-bold text-emerald-600">{{ numCorrectAnswers }}</span></div>
                                <div class="h-2.5 bg-muted/50 rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700 ease-out shadow-sm" :style="{ width: numTotalQuestions > 0 ? (numCorrectAnswers/numTotalQuestions*100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs mb-1.5"><span class="text-muted-foreground font-medium">Salah</span><span class="font-bold text-red-500">{{ wrongAnswers }}</span></div>
                                <div class="h-2.5 bg-muted/50 rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full transition-all duration-700 ease-out shadow-sm" :style="{ width: numTotalQuestions > 0 ? (wrongAnswers/numTotalQuestions*100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground pt-1">Total: <strong class="text-foreground font-bold">{{ numTotalQuestions }}</strong> soal dikerjakan</p>
                        </div>
                    </div>
                </div>

                <div class="anim-stats-card bg-gradient-to-br from-primary to-pine-teal rounded-2xl p-6 text-white shadow-lg">
                    <h3 class="font-semibold text-sm mb-4 text-white/90 flex items-center gap-1.5"><Icon icon="mdi:star" class="w-4 h-4" /> Level Performa</h3>
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur-sm shadow-lg"><Icon :icon="performanceGrade.icon" class="w-10 h-10 text-white drop-shadow" /></div>
                        <div>
                            <p class="text-2xl font-bold drop-shadow-sm">{{ performanceGrade.label }}</p>
                            <p class="text-white/70 text-sm mt-0.5">Rata-rata nilai: {{ numAverageScore.toFixed(1) }}</p>
                        </div>
                    </div>
                    <div class="bg-white/15 rounded-full h-2.5 overflow-hidden shadow-inner">
                        <div class="h-full bg-white rounded-full transition-all duration-700 ease-out shadow-sm" :style="{ width: Math.min(numAverageScore, 100) + '%' }"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-white/60 mt-1.5 font-medium">
                        <span>0</span><span>50</span><span>100</span>
                    </div>
                </div>
            </div>

            <!-- Per Package Stats -->
            <div v-if="sessionsByPackage.length > 0" class="anim-stats-card bg-card border border-border/60 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-semibold flex items-center gap-1.5"><Icon icon="mdi:package-variant" class="w-5 h-5 text-primary" /> Statistik per Soal</h3>
                    <span class="text-[10px] text-muted-foreground bg-muted/80 px-2.5 py-1 rounded-full font-medium uppercase tracking-wider">{{ sessionsByPackage.length }} soal</span>
                </div>
                <div class="space-y-5">
                    <div v-for="pkg in sessionsByPackage" :key="pkg.package" class="group">
                        <div class="flex items-start justify-between mb-2">
                            <div class="min-w-0 flex-1 mr-4">
                                <p class="text-sm font-medium truncate group-hover:text-primary transition-colors">{{ pkg.package }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ pkg.attempts }}x dikerjakan</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-emerald-600">{{ Number(pkg.avg_score || 0).toFixed(0) }}</p>
                                <p class="text-[10px] text-muted-foreground">avg</p>
                            </div>
                        </div>
                        <div class="h-2.5 bg-muted/50 rounded-full overflow-hidden shadow-inner">
                            <div :class="['h-full bg-gradient-to-r rounded-full transition-all duration-700 ease-out shadow-sm', pkgBarColor(pkg.avg_score)]"
                                 :style="{ width: Math.min(Number(pkg.avg_score || 0), 100) + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-muted-foreground mt-1.5 font-medium">
                            <span>Rata-rata: <strong class="text-foreground">{{ Number(pkg.avg_score || 0).toFixed(1) }}</strong></span>
                            <span>Terbaik: <strong class="text-emerald-600">{{ Number(pkg.best_score || 0).toFixed(1) }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="anim-stats-card bg-card border border-border/60 rounded-2xl overflow-hidden shadow-sm">
                <div class="bg-gradient-to-r from-hunter-green to-pine-teal p-4.5 text-white">
                    <h3 class="font-semibold flex items-center gap-1.5"><Icon icon="mdi:lightbulb-on-outline" class="w-5 h-5" /> Tips untuk Meningkatkan Nilai</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-3 bg-gradient-to-br from-muted/40 to-muted/20 rounded-xl p-3.5 border border-border/30 hover:shadow-md transition-shadow duration-300">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0"><Icon icon="mdi:calendar-outline" class="w-5 h-5 text-primary" /></div>
                        <p class="text-sm text-muted-foreground leading-relaxed">Kerjakan tugas secara <strong class="text-foreground">rutin</strong> untuk meningkatkan pemahaman</p>
                    </div>
                    <div class="flex items-start gap-3 bg-gradient-to-br from-muted/40 to-muted/20 rounded-xl p-3.5 border border-border/30 hover:shadow-md transition-shadow duration-300">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0"><Icon icon="mdi:magnify" class="w-5 h-5 text-primary" /></div>
                        <p class="text-sm text-muted-foreground leading-relaxed">Periksa <strong class="text-foreground">pembahasan</strong> untuk soal yang salah</p>
                    </div>
                    <div class="flex items-start gap-3 bg-gradient-to-br from-muted/40 to-muted/20 rounded-xl p-3.5 border border-border/30 hover:shadow-md transition-shadow duration-300">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0"><Icon icon="mdi:clock-outline" class="w-5 h-5 text-primary" /></div>
                        <p class="text-sm text-muted-foreground leading-relaxed">Latih <strong class="text-foreground">kecepatan menjawab</strong> agar lebih efisien</p>
                    </div>
                    <div class="flex items-start gap-3 bg-gradient-to-br from-muted/40 to-muted/20 rounded-xl p-3.5 border border-border/30 hover:shadow-md transition-shadow duration-300">
                        <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0"><Icon icon="mdi:pencil-outline" class="w-5 h-5 text-primary" /></div>
                        <p class="text-sm text-muted-foreground leading-relaxed">Catat <strong class="text-foreground">pola soal</strong> yang sering keliru dijawab</p>
                    </div>
                </div>
            </div>

            <!-- Link to History -->
            <div class="text-center anim-stats-fade">
                <Link :href="route('practice.history')" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/70 transition-all duration-300 hover:gap-3 group">
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    Lihat Riwayat Lengkap
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
@keyframes statsFadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.anim-stats-card {
    animation: statsFadeInUp 0.5s ease-out both;
}
.anim-stats-card:nth-child(1) { animation-delay: 0s; }
.anim-stats-card:nth-child(2) { animation-delay: 0.07s; }
.anim-stats-card:nth-child(3) { animation-delay: 0.14s; }
.anim-stats-card:nth-child(4) { animation-delay: 0.21s; }
@keyframes statsFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.anim-stats-fade { animation: statsFadeIn 0.4s ease-out both; }
</style>
