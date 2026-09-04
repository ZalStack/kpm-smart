<script setup>
import { inject, computed } from 'vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
    if (score >= 90) return { label: 'Sangat Baik', icon: 'mdi:trophy', color: 'text-emerald-600', bg: 'bg-emerald-50', ring: 'ring-emerald-200' };
    if (score >= 75) return { label: 'Baik', icon: 'mdi:check-circle', color: 'text-blue-600', bg: 'bg-blue-50', ring: 'ring-blue-200' };
    if (score >= 60) return { label: 'Cukup', icon: 'mdi:chart-bar', color: 'text-yellow-600', bg: 'bg-yellow-50', ring: 'ring-yellow-200' };
    return { label: 'Perlu Latihan', icon: 'mdi:book-open-page-variant', color: 'text-red-500', bg: 'bg-red-50', ring: 'ring-red-200' };
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
                            <p class="text-3xl mb-1"><Icon :icon="grade.icon" class="w-8 h-8" /></p>
                            <h2 class="text-xl font-bold">{{ grade.label }}</h2>
                            <p class="text-white/80 text-sm mt-1">{{ session.package?.title || '-' }}</p>
                            <div class="flex items-center gap-3 mt-2 text-white/60 text-xs">
                                <span class="inline-flex items-center gap-1"><Icon icon="mdi:account-outline" class="w-4 h-4 inline-block align-middle" /> {{ session.user?.name || '-' }}</span>
                                <span class="inline-flex items-center gap-1"><Icon icon="mdi:email-outline" class="w-4 h-4 inline-block align-middle" /> {{ session.user?.email || '' }}</span>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 text-center hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-1">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-1">Tanggal</p>
                    <p class="text-sm font-semibold">{{ formatDate(session.created_at) }}</p>
                </div>
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 text-center hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-2">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-1">Status</p>
                    <Badge :variant="session.status === 'completed' ? 'success' : 'warning'" class="text-[10px] gap-1 px-2.5 py-1 rounded-lg font-semibold"><span class="w-1.5 h-1.5 rounded-full" :class="session.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>{{ session.status === 'completed' ? 'Selesai' : 'Berlangsung' }}</Badge>
                </div>
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 text-center hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-3">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-1">Card</p>
                    <p class="text-sm font-semibold">{{ session.card_id || '-' }}</p>
                </div>
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 text-center hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-4">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-1">Waktu Limit</p>
                    <p class="text-sm font-semibold">{{ timeLimitMinutes > 0 ? timeLimitMinutes + ' menit' : 'Tanpa batas' }}</p>
                </div>
            </div>

            <!-- Answer Details - 4 Column Grid -->
            <div>
                <h3 class="text-lg font-bold mb-4 inline-flex items-center gap-2 anim-fade-in-up">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 text-primary" /></div>
                    Detail Jawaban
                </h3>

                <div v-if="results.length === 0" class="bg-card rounded-2xl border border-border/60 p-10 text-center anim-fade-in-up">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                            <Icon icon="mdi:pencil-outline" class="w-10 h-10 text-muted-foreground/40" />
                        </div>
                        <div>
                            <p class="font-semibold text-muted-foreground">Tidak ada data jawaban tersedia</p>
                            <p class="text-sm text-muted-foreground/60 mt-1">Data jawaban akan muncul di sini</p>
                        </div>
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['bg-card rounded-2xl border border-border/60 p-4 border-l-4 transition-all duration-300 hover:shadow-md hover:border-primary/20',
                                  result.is_correct ? 'border-l-emerald-500' : (result.user_answer ? 'border-l-red-500' : 'border-l-amber-400')]"
                         :style="{ animationDelay: `${idx * 30}ms` }">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-muted flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
                            <Badge :variant="result.is_correct ? 'success' : (result.user_answer ? 'destructive' : 'outline')" class="text-[10px] gap-1 px-2 py-0.5 rounded-lg font-semibold">
                                <template v-if="result.is_correct"><Icon icon="mdi:check-circle" class="w-3 h-3 inline-block align-middle" /> Benar</template>
                                <template v-else-if="result.user_answer"><Icon icon="mdi:close-circle" class="w-3 h-3 inline-block align-middle" /> Salah</template>
                                <template v-else><Icon icon="mdi:checkbox-blank-circle-outline" class="w-3 h-3 inline-block align-middle" /> Kosong</template>
                            </Badge>
                        </div>

                        <div v-if="result.image" class="mb-2">
                            <img :src="result.image" alt="Gambar soal" class="w-full h-auto rounded-xl border max-h-32 object-contain" @error="$event.target.style.display='none'" />
                        </div>

                        <p class="text-xs leading-relaxed mb-2 line-clamp-4" v-html="result.question"></p>

                        <div class="space-y-1 text-xs">
                            <p>
                                <span class="text-muted-foreground">User:</span>
                                <span :class="result.is_correct ? 'text-emerald-600 font-semibold' : 'text-red-500 font-semibold'">
                                    {{ result.user_answer || '-' }}
                                </span>
                            </p>
                            <p v-if="showAnswerKey && !result.is_correct">
                                <span class="text-muted-foreground">Benar:</span>
                                <span class="text-emerald-600 font-semibold">{{ result.correct_answer }}</span>
                            </p>
                        </div>

                        <div v-if="showExplanation && result.explanation" class="mt-2 flex items-start gap-1.5 bg-blue-50 border border-blue-100 rounded-xl p-2.5">
                            <span class="text-blue-500 flex-shrink-0 text-xs"><Icon icon="mdi:lightbulb-on-outline" class="w-3 h-3" /></span>
                            <p class="text-[10px] text-blue-800 leading-relaxed line-clamp-3">{{ result.explanation }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
