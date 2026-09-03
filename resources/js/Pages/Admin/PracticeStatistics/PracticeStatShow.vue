<script setup>
import { inject, computed } from 'vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Card from '@/Components/ui/card/Card.vue';

const props = defineProps({
    session: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    showExplanation: { type: Boolean, default: true },
    showAnswerKey: { type: Boolean, default: true },
    showScore: { type: Boolean, default: true },
    timeLimitMinutes: { type: Number, default: 0 },
});

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatDuration(s) {
    if (!s) return '0:00';
    const m = Math.floor(s / 60);
    return m + ':' + String(s % 60).padStart(2, '0');
}

function scoreGrade(score) {
    if (score >= 90) return { label: 'Sangat Baik', emoji: '🏆', color: 'text-emerald-600', bg: 'bg-emerald-50', ring: 'ring-emerald-200' };
    if (score >= 75) return { label: 'Baik', emoji: '✅', color: 'text-blue-600', bg: 'bg-blue-50', ring: 'ring-blue-200' };
    if (score >= 60) return { label: 'Cukup', emoji: '📊', color: 'text-yellow-600', bg: 'bg-yellow-50', ring: 'ring-yellow-200' };
    return { label: 'Perlu Latihan', emoji: '📚', color: 'text-red-500', bg: 'bg-red-50', ring: 'ring-red-200' };
}

const grade = computed(() => scoreGrade(Number(props.session.total_score) || 0));

const correctCount = computed(() => props.results.filter(r => r.is_correct).length);
const wrongCount = computed(() => props.results.filter(r => !r.is_correct && r.user_answer).length);
const unansweredCount = computed(() => props.results.filter(r => !r.user_answer).length);

const circumference = 2 * Math.PI * 40;
const scoreOffset = computed(() => circumference - (Math.min(Number(props.session.total_score) || 0, 100) / 100) * circumference);
</script>

<template>
    <AdminLayout>
        <Head :title="'Sesi #' + session.id + ' - Detail Statistik'" />

        <template #header-title>Detail Sesi Praktik</template>
        <template #header-sub>Sesi #{{ session.id }}</template>

        <Link :href="route('admin.practice-statistics.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Statistik
        </Link>

        <div class="space-y-6">

            <!-- Score Hero Card -->
            <div :class="['rounded-2xl overflow-hidden shadow-card-lg text-white bg-gradient-to-br',
                Number(session.total_score || 0) >= 90 ? 'from-emerald-500 to-emerald-700' :
                Number(session.total_score || 0) >= 75 ? 'from-blue-500 to-blue-700' :
                Number(session.total_score || 0) >= 60 ? 'from-yellow-500 to-yellow-600' :
                'from-red-400 to-red-600']">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <div v-if="showScore" class="relative flex-shrink-0">
                            <svg class="w-32 h-32 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="10" stroke-linecap="round" :stroke-dasharray="circumference" :stroke-dashoffset="scoreOffset" class="transition-all duration-1000" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black leading-none">{{ Number(session.total_score || 0).toFixed(0) }}</span>
                                <span class="text-[10px] text-white/70 mt-0.5">Nilai</span>
                            </div>
                        </div>
                        <div class="text-center sm:text-left flex-1">
                            <p class="text-3xl mb-1">{{ grade.emoji }}</p>
                            <h2 class="text-xl font-bold">{{ grade.label }}</h2>
                            <p class="text-white/80 text-sm mt-1">{{ session.package?.title || '-' }}</p>
                            <div class="flex items-center gap-3 mt-2 text-white/60 text-xs">
                                <span>👤 {{ session.user?.name || '-' }}</span>
                                <span>📧 {{ session.user?.email || '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-white/10 border-t border-white/10">
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold">{{ session.total_question || 0 }}</p>
                        <p class="text-[10px] text-white/70">Total Soal</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold text-emerald-300">{{ session.correct_answer || 0 }}</p>
                        <p class="text-[10px] text-white/70">Benar</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-xl font-bold text-red-300">{{ session.wrong_answer || 0 }}</p>
                        <p class="text-[10px] text-white/70">Salah</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3 text-center">
                        <p class="text-sm font-bold">{{ formatDuration(session.duration_seconds) }}</p>
                        <p class="text-[10px] text-white/70">Durasi</p>
                    </div>
                </div>
            </div>

            <!-- Session Info - 4 Column Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-card rounded-xl border shadow-sm p-5 text-center">
                    <p class="text-xs text-muted-foreground mb-1">Tanggal</p>
                    <p class="text-sm font-semibold">{{ formatDate(session.created_at) }}</p>
                </div>
                <div class="bg-card rounded-xl border shadow-sm p-5 text-center">
                    <p class="text-xs text-muted-foreground mb-1">Status</p>
                    <Badge :variant="session.status === 'completed' ? 'success' : 'warning'" class="text-[10px]">{{ session.status === 'completed' ? 'Selesai' : 'Berlangsung' }}</Badge>
                </div>
                <div class="bg-card rounded-xl border shadow-sm p-5 text-center">
                    <p class="text-xs text-muted-foreground mb-1">Card</p>
                    <p class="text-sm font-semibold">{{ session.card_id || '-' }}</p>
                </div>
                <div class="bg-card rounded-xl border shadow-sm p-5 text-center">
                    <p class="text-xs text-muted-foreground mb-1">Waktu Limit</p>
                    <p class="text-sm font-semibold">{{ timeLimitMinutes > 0 ? timeLimitMinutes + ' menit' : 'Tanpa batas' }}</p>
                </div>
            </div>

            <!-- Answer Details - 4 Column Grid -->
            <div>
                <h3 class="text-lg font-semibold mb-4">📋 Detail Jawaban</h3>

                <div v-if="results.length === 0" class="bg-card rounded-xl border p-8 text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <p class="text-muted-foreground">Tidak ada data jawaban tersedia</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['bg-card rounded-xl border p-4 border-l-4 transition-all hover:shadow-sm',
                                  result.is_correct ? 'border-l-green-500' : (result.user_answer ? 'border-l-red-500' : 'border-l-yellow-400')]">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-muted flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
                            <Badge :variant="result.is_correct ? 'success' : (result.user_answer ? 'destructive' : 'outline')" class="text-[10px]">
                                {{ result.is_correct ? '✅ Benar' : (result.user_answer ? '❌ Salah' : '⬜ Kosong') }}
                            </Badge>
                        </div>

                        <div v-if="result.image" class="mb-2">
                            <img :src="result.image" alt="Gambar soal" class="w-full h-auto rounded-lg border max-h-32 object-contain" @error="$event.target.style.display='none'" />
                        </div>

                        <p class="text-xs leading-relaxed mb-2 line-clamp-4" v-html="result.question"></p>

                        <div class="space-y-1 text-xs">
                            <p>
                                <span class="text-muted-foreground">User:</span>
                                <span :class="result.is_correct ? 'text-green-600 font-medium' : 'text-red-500'">
                                    {{ result.user_answer || '-' }}
                                </span>
                            </p>
                            <p v-if="showAnswerKey && !result.is_correct">
                                <span class="text-muted-foreground">Benar:</span>
                                <span class="text-green-600 font-medium">{{ result.correct_answer }}</span>
                            </p>
                        </div>

                        <div v-if="showExplanation && result.explanation" class="mt-2 flex items-start gap-1.5 bg-blue-50 border border-blue-100 rounded-lg p-2">
                            <span class="text-blue-500 flex-shrink-0 text-xs">💡</span>
                            <p class="text-[10px] text-blue-800 leading-relaxed line-clamp-3">{{ result.explanation }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
