<script setup>
import { inject, computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
const route = inject('route');

const props = defineProps({
    session: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    correct: { type: Number, default: 0 },
    wrong: { type: Number, default: 0 },
    unanswered: { type: Number, default: 0 },
    totalScore: { type: Number, default: 0 },
    showAnswerKey: { type: Boolean, default: true },
    showExplanation: { type: Boolean, default: true },
    showScore: { type: Boolean, default: true },
});

const activeTab = ref('summary');

function formatDuration(seconds) {
    if (!seconds) return '0 detik';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    if (h > 0) return `${h} jam ${m} mnt`;
    if (m > 0) return `${m} mnt ${s} dtk`;
    return `${s} detik`;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

const scoreGrade = computed(() => {
    const s = props.totalScore;
    if (s >= 90) return { label: 'Luar Biasa!', emoji: '🏆', color: 'text-emerald-600', bg: 'from-emerald-500 to-emerald-700', ring: 'ring-emerald-200', light: 'bg-emerald-50' };
    if (s >= 75) return { label: 'Bagus!', emoji: '🎉', color: 'text-blue-600', bg: 'from-blue-500 to-blue-700', ring: 'ring-blue-200', light: 'bg-blue-50' };
    if (s >= 60) return { label: 'Cukup Baik', emoji: '👍', color: 'text-yellow-600', bg: 'from-yellow-500 to-yellow-600', ring: 'ring-yellow-200', light: 'bg-yellow-50' };
    return { label: 'Perlu Semangat!', emoji: '💪', color: 'text-red-500', bg: 'from-red-400 to-red-600', ring: 'ring-red-200', light: 'bg-red-50' };
});

const accuracyPct = computed(() => {
    const total = props.correct + props.wrong + props.unanswered;
    if (!total) return 0;
    return Math.round((props.correct / total) * 100);
});

// Circumference for SVG circle (r=40)
const circumference = 2 * Math.PI * 40;
const scoreOffset = computed(() => circumference - (Math.min(props.totalScore, 100) / 100) * circumference);
</script>

<template>
    <UserLayout>
        <Head title="Hasil Tugas - KPM Belajar Online" />

        <template #header-title>🎯 Hasil Tugas</template>
        <template #header-sub>{{ session.package?.title }}</template>

        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Score Hero Card -->
            <div :class="['rounded-2xl overflow-hidden shadow-card-lg text-white bg-gradient-to-br', scoreGrade.bg]">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <!-- Score Ring -->
                        <div class="relative flex-shrink-0">
                            <svg class="w-32 h-32 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="white"
                                        stroke-width="10" stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="scoreOffset"
                                        class="transition-all duration-1000" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black leading-none">
                                    {{ showScore ? totalScore.toFixed(0) : '?' }}
                                </span>
                                <span class="text-[10px] text-white/70 mt-0.5">{{ showScore ? 'Nilai' : 'Disembunyikan' }}</span>
                            </div>
                        </div>

                        <!-- Grade Info -->
                        <div class="text-center sm:text-left flex-1">
                            <p class="text-3xl mb-1">{{ scoreGrade.emoji }}</p>
                            <h2 class="text-xl font-bold">{{ scoreGrade.label }}</h2>
                            <p class="text-white/80 text-sm mt-1">{{ session.package?.title }}</p>
                            <p class="text-white/60 text-xs mt-1">📅 {{ formatDate(session.finished_at || session.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-4 gap-px bg-white/10 border-t border-white/10">
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold">{{ correct }}</p>
                        <p class="text-[10px] text-white/70">Benar</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold">{{ wrong }}</p>
                        <p class="text-[10px] text-white/70">Salah</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold">{{ unanswered }}</p>
                        <p class="text-[10px] text-white/70">Kosong</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-sm font-bold">{{ formatDuration(session.duration_seconds) }}</p>
                        <p class="text-[10px] text-white/70">Waktu</p>
                    </div>
                </div>
            </div>

            <!-- Info hidden notice -->
            <div v-if="!showScore || !showAnswerKey" class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <p class="text-xs text-amber-700">Beberapa informasi disembunyikan oleh pengatur paket</p>
            </div>

            <!-- 1-attempt notice -->
            <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 text-lg">📌</div>
                <div>
                    <p class="text-sm font-semibold">Tugas Sudah Selesai</p>
                    <p class="text-xs text-muted-foreground mt-0.5">Soal ini hanya bisa dikerjakan <strong>1 kali</strong>. Kamu bisa melihat riwayat dan statistik belajarmu di bawah.</p>
                </div>
            </div>

            <!-- Tabs -->
            <div v-if="showAnswerKey" class="bg-card border rounded-2xl overflow-hidden">
                <!-- Tab Nav -->
                <div class="flex border-b border-border bg-muted/30">
                    <button @click="activeTab = 'summary'"
                            :class="['flex-1 py-3 text-sm font-medium transition-colors', activeTab === 'summary' ? 'text-primary border-b-2 border-primary bg-card' : 'text-muted-foreground hover:text-foreground']">
                        📊 Ringkasan
                    </button>
                    <button @click="activeTab = 'detail'"
                            :class="['flex-1 py-3 text-sm font-medium transition-colors', activeTab === 'detail' ? 'text-primary border-b-2 border-primary bg-card' : 'text-muted-foreground hover:text-foreground']">
                        📋 Detail Jawaban ({{ results.length }})
                    </button>
                </div>

                <!-- Tab: Summary -->
                <div v-if="activeTab === 'summary'" class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
                        <div class="text-center p-4 bg-emerald-50 rounded-xl">
                            <p class="text-2xl font-bold text-emerald-600">{{ correct }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Jawaban Benar</p>
                        </div>
                        <div class="text-center p-4 bg-red-50 rounded-xl">
                            <p class="text-2xl font-bold text-red-500">{{ wrong }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Jawaban Salah</p>
                        </div>
                        <div class="text-center p-4 bg-muted rounded-xl col-span-2 sm:col-span-1">
                            <p class="text-2xl font-bold text-muted-foreground">{{ unanswered }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Tidak Dijawab</p>
                        </div>
                    </div>

                    <!-- Accuracy bar -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span>Akurasi jawaban</span>
                            <span class="font-semibold text-foreground">{{ accuracyPct }}%</span>
                        </div>
                        <div class="h-3 bg-muted rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :class="accuracyPct >= 75 ? 'bg-emerald-500' : accuracyPct >= 60 ? 'bg-yellow-500' : 'bg-red-400'"
                                 :style="{ width: accuracyPct + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-muted-foreground">
                            <span>0%</span><span>50%</span><span>100%</span>
                        </div>
                    </div>
                </div>

                <!-- Tab: Detail -->
                <div v-else class="divide-y divide-border/50">
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['p-4', result.is_correct ? 'bg-emerald-50/30' : (result.user_answer ? 'bg-red-50/30' : 'bg-muted/20')]">
                        <div class="flex items-start gap-3">
                            <!-- Number + Status icon -->
                            <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</div>
                                <span class="text-base leading-none">{{ result.is_correct ? '✅' : (result.user_answer ? '❌' : '⬜') }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Question image -->
                                <div v-if="result.image" class="mb-2">
                                    <img :src="result.image" alt="Gambar soal" class="max-w-full h-auto rounded-lg border max-h-48 object-contain" @error="$event.target.style.display='none'" />
                                </div>

                                <!-- Question text -->
                                <p class="text-sm leading-relaxed mb-3 font-medium" v-html="result.question"></p>

                                <!-- Options (show all with highlight) -->
                                <div class="space-y-1.5 mb-3">
                                    <div v-for="opt in result.options" :key="opt"
                                         :class="[
                                             'flex items-center gap-2 px-3 py-2 rounded-lg text-xs border',
                                             opt === result.correct_answer ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-medium' :
                                             opt === result.user_answer && !result.is_correct ? 'bg-red-50 border-red-300 text-red-700 line-through' :
                                             'bg-transparent border-border/50 text-muted-foreground'
                                         ]">
                                        <span class="flex-shrink-0">
                                            {{ opt === result.correct_answer ? '✅' : (opt === result.user_answer && !result.is_correct ? '❌' : '○') }}
                                        </span>
                                        {{ opt }}
                                    </div>
                                </div>

                                <!-- User answer vs correct -->
                                <div class="text-xs space-y-0.5">
                                    <div v-if="!result.user_answer" class="text-muted-foreground italic">⬜ Tidak dijawab</div>
                                    <div v-if="!result.is_correct && result.user_answer" class="flex items-center gap-1">
                                        <span class="text-muted-foreground">Jawaban kamu:</span>
                                        <span class="text-red-500 font-medium">{{ result.user_answer }}</span>
                                    </div>
                                    <div v-if="!result.is_correct" class="flex items-center gap-1">
                                        <span class="text-muted-foreground">Jawaban benar:</span>
                                        <span class="text-emerald-600 font-semibold">{{ result.correct_answer }}</span>
                                    </div>
                                </div>

                                <!-- Explanation -->
                                <div v-if="showExplanation && result.explanation" class="mt-3 flex items-start gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3">
                                    <span class="text-blue-500 flex-shrink-0 mt-0.5">💡</span>
                                    <div>
                                        <p class="text-[10px] font-semibold text-blue-700 mb-0.5">Pembahasan</p>
                                        <p class="text-xs text-blue-800 leading-relaxed">{{ result.explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Answer key hidden -->
            <div v-else class="bg-card border rounded-2xl p-10 text-center">
                <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center text-2xl mx-auto mb-4">🔒</div>
                <p class="font-semibold text-foreground">Kunci Jawaban Disembunyikan</p>
                <p class="text-sm text-muted-foreground mt-1">Pengatur paket belum mengaktifkan tampilan kunci jawaban</p>
            </div>

            <!-- Action Buttons - NO RETRY -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-4">
                <Link :href="route('practice.history')"
                      class="flex items-center justify-center gap-2 bg-primary text-primary-foreground px-5 py-3 rounded-xl text-sm font-semibold hover:bg-primary/90 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    Riwayat Tugas
                </Link>
                <Link :href="route('practice.statistics')"
                      class="flex items-center justify-center gap-2 bg-card border border-border text-foreground px-5 py-3 rounded-xl text-sm font-semibold hover:bg-muted transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Statistik Saya
                </Link>
                <Link :href="route('packages.index')"
                      class="sm:col-span-2 flex items-center justify-center gap-2 bg-muted text-muted-foreground px-5 py-3 rounded-xl text-sm font-medium hover:bg-muted/80 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    Lihat Paket Lain
                </Link>
            </div>

        </div>
    </UserLayout>
</template>
