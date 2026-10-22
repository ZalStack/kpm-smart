<script setup>
import { inject, computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import confetti from 'canvas-confetti';
import MathRenderer from '@/Components/shared/MathRenderer.vue';

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
    if (!seconds) return '0 dtk';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    if (h > 0) return `${h} jam ${m} mnt`;
    if (m > 0) return `${m} mnt ${s} dtk`;
    return `${s} dtk`;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

const scoreGrade = computed(() => {
    const s = props.totalScore;
    if (s >= 90) return { label: 'Sangat Memuaskan!', icon: 'mdi:trophy', bg: 'from-emerald-800 via-emerald-700 to-teal-800', light: 'text-emerald-300' };
    if (s >= 75) return { label: 'Bagus Sekali!', icon: 'mdi:star-face', bg: 'from-teal-800 via-teal-700 to-cyan-800', light: 'text-teal-300' };
    if (s >= 60) return { label: 'Cukup Baik', icon: 'mdi:thumb-up-outline', bg: 'from-amber-700 via-amber-600 to-orange-700', light: 'text-amber-200' };
    return { label: 'Tetap Semangat!', icon: 'mdi:arm-flex', bg: 'from-rose-800 via-rose-700 to-orange-800', light: 'text-rose-200' };
});

const accuracyPct = computed(() => {
    const total = props.correct + props.wrong + props.unanswered;
    if (!total) return 0;
    return Math.round((props.correct / total) * 100);
});

const circumference = 2 * Math.PI * 40;
const scoreOffset = computed(() => circumference - (Math.min(props.totalScore, 100) / 100) * circumference);

onMounted(() => {
    if (props.totalScore >= 80) {
        const duration = 2200;
        const end = Date.now() + duration;
        const colors = ['#10b981', '#059669', '#f59e0b', '#3b82f6', '#14b8a6'];
        (function frame() {
            confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors });
            confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors });
            if (Date.now() < end) requestAnimationFrame(frame);
        })();
    }
});
</script>

<template>
    <UserLayout>
        <Head title="Hasil Pengerjaan Tugas - KPM SMART" />

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Score Hero Card -->
            <div :class="['rounded-3xl overflow-hidden shadow-xl text-white bg-gradient-to-br anim-fade-in border border-white/10', scoreGrade.bg]">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-center gap-6 text-center sm:text-left">
                        <!-- Score Radial Ring -->
                        <div class="relative flex-shrink-0">
                            <svg class="w-36 h-36 -rotate-90 drop-shadow-md" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="40"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="scoreOffset"
                                    class="transition-all duration-1000 ease-out"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl sm:text-4xl font-black leading-none drop-shadow">
                                    {{ showScore ? totalScore.toFixed(0) : '?' }}
                                </span>
                                <span class="text-[10px] text-white/80 mt-1 font-bold tracking-wider uppercase">
                                    {{ showScore ? 'Nilai Akhir' : 'Disembunyikan' }}
                                </span>
                            </div>
                        </div>

                        <!-- Info & Grade -->
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-xs font-bold mb-2">
                                <Icon :icon="scoreGrade.icon" class="w-4 h-4 text-amber-300" />
                                <span>{{ scoreGrade.label }}</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white mb-1">
                                {{ session.package?.title }}
                            </h2>
                            <p class="text-xs text-white/75 flex items-center justify-center sm:justify-start gap-1.5 mt-2 font-medium">
                                <Icon icon="mdi:calendar-check" class="w-4 h-4" />
                                Diselesaikan pada {{ formatDate(session.finished_at || session.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid inside Hero -->
                <div class="grid grid-cols-4 gap-px bg-white/15 border-t border-white/20">
                    <div class="bg-black/15 px-3 py-3 text-center">
                        <p class="text-lg sm:text-xl font-black">{{ correct }}</p>
                        <p class="text-[10px] text-white/75 font-semibold">Benar</p>
                    </div>
                    <div class="bg-black/15 px-3 py-3 text-center">
                        <p class="text-lg sm:text-xl font-black">{{ wrong }}</p>
                        <p class="text-[10px] text-white/75 font-semibold">Salah</p>
                    </div>
                    <div class="bg-black/15 px-3 py-3 text-center">
                        <p class="text-lg sm:text-xl font-black">{{ unanswered }}</p>
                        <p class="text-[10px] text-white/75 font-semibold">Kosong</p>
                    </div>
                    <div class="bg-black/15 px-3 py-3 text-center">
                        <p class="text-xs sm:text-sm font-bold truncate mt-1">{{ formatDuration(session.duration_seconds) }}</p>
                        <p class="text-[10px] text-white/75 font-semibold">Durasi</p>
                    </div>
                </div>
            </div>

            <!-- Notice 1-Attempt -->
            <div class="bg-card border border-border/80 rounded-2xl p-4 sm:p-5 flex items-center gap-3.5 shadow-2xs">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <Icon icon="mdi:information-outline" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-xs font-bold text-foreground">Sesi Tugas Telah Diselesaikan</p>
                    <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                        Nilai telah tercatat di statistik belajarmu. Kamu bisa meninjau ringkasan dan pembahasan di bawah ini.
                    </p>
                </div>
            </div>

            <!-- Tabs Review -->
            <div v-if="showAnswerKey" class="bg-card border border-border/80 rounded-3xl overflow-hidden shadow-xs">
                <!-- Tab Headers -->
                <div class="flex border-b border-border/60 bg-muted/40 p-1.5">
                    <button
                        @click="activeTab = 'summary'"
                        :class="[
                            'flex-1 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center justify-center gap-2',
                            activeTab === 'summary'
                                ? 'bg-card text-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        <Icon icon="mdi:chart-bar" class="w-4 h-4 text-primary" />
                        Ringkasan Performa
                    </button>
                    <button
                        @click="activeTab = 'detail'"
                        :class="[
                            'flex-1 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center justify-center gap-2',
                            activeTab === 'detail'
                                ? 'bg-card text-foreground shadow-xs'
                                : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        <Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 text-primary" />
                        Review Butir Soal ({{ results.length }})
                    </button>
                </div>

                <!-- Tab: Summary -->
                <div v-if="activeTab === 'summary'" class="p-6 sm:p-8 space-y-6 anim-fade-in">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="text-center p-5 bg-emerald-50/70 rounded-2xl border border-emerald-200/80">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center mx-auto mb-2 shadow-2xs">
                                <Icon icon="mdi:check" class="w-5 h-5" />
                            </div>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-700">{{ correct }}</p>
                            <p class="text-xs text-emerald-800 font-bold mt-1">Jawaban Benar</p>
                        </div>

                        <div class="text-center p-5 bg-rose-50/70 rounded-2xl border border-rose-200/80">
                            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center mx-auto mb-2 shadow-2xs">
                                <Icon icon="mdi:close" class="w-5 h-5" />
                            </div>
                            <p class="text-2xl sm:text-3xl font-black text-rose-700">{{ wrong }}</p>
                            <p class="text-xs text-rose-800 font-bold mt-1">Jawaban Salah</p>
                        </div>

                        <div class="text-center p-5 bg-muted/60 rounded-2xl border border-border/80 col-span-2 sm:col-span-1">
                            <div class="w-10 h-10 rounded-xl bg-muted text-muted-foreground flex items-center justify-center mx-auto mb-2 shadow-2xs">
                                <Icon icon="mdi:checkbox-blank-circle-outline" class="w-5 h-5" />
                            </div>
                            <p class="text-2xl sm:text-3xl font-black text-muted-foreground">{{ unanswered }}</p>
                            <p class="text-xs text-muted-foreground font-bold mt-1">Tidak Dijawab</p>
                        </div>
                    </div>

                    <!-- Accuracy Bar -->
                    <div class="space-y-2 pt-2 border-t border-border/60">
                        <div class="flex justify-between text-xs">
                            <span class="font-bold text-foreground">Tingkat Akurasi Jawaban</span>
                            <span class="font-black text-primary">{{ accuracyPct }}%</span>
                        </div>
                        <div class="h-3 bg-muted rounded-full overflow-hidden p-0.5">
                            <div
                                class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-700"
                                :style="{ width: accuracyPct + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Detail Answers -->
                <div v-else class="divide-y divide-border/40 anim-fade-in">
                    <div
                        v-for="(result, idx) in results"
                        :key="idx"
                        :class="[
                            'p-5 sm:p-6 transition-colors',
                            result.is_correct ? 'bg-emerald-50/15' : (result.user_answer ? 'bg-rose-50/15' : 'bg-muted/10')
                        ]"
                    >
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-muted font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                {{ idx + 1 }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="mb-2">
                                    <span
                                        :class="[
                                            'text-[10px] font-bold px-2 py-0.5 rounded-full border inline-flex items-center gap-1',
                                            result.is_correct ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'
                                        ]"
                                    >
                                        <Icon :icon="result.is_correct ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                                        {{ result.is_correct ? 'Benar' : (result.user_answer ? 'Salah' : 'Kosong') }}
                                    </span>
                                </div>

                                <div v-if="result.image" class="mb-3">
                                    <img
                                        :src="result.image.startsWith('/') || result.image.startsWith('http') ? result.image : '/storage/' + result.image"
                                        alt="Gambar soal"
                                        class="max-w-full h-auto rounded-xl border border-border/60 max-h-48 object-contain shadow-2xs"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>

                                <p class="text-sm font-medium leading-relaxed mb-3 text-foreground">
                                    <MathRenderer :text="result.question" />
                                </p>

                                <!-- Pilihan Ganda Option Review -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="space-y-1.5 mb-3">
                                    <div
                                        v-for="opt in result.options"
                                        :key="opt"
                                        :class="[
                                            'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs border transition-all',
                                            opt === result.correct_answer
                                                ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-bold'
                                                : opt === result.user_answer && !result.is_correct
                                                    ? 'bg-rose-50 border-rose-300 text-rose-800 line-through font-medium'
                                                    : 'bg-card border-border/60 text-muted-foreground'
                                        ]"
                                    >
                                        <Icon v-if="opt === result.correct_answer" icon="mdi:check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                                        <Icon v-else-if="opt === result.user_answer && !result.is_correct" icon="mdi:close-circle" class="w-4 h-4 text-rose-500 shrink-0" />
                                        <span v-else class="w-4 h-4 rounded-full border border-muted-foreground/30 inline-block shrink-0"></span>
                                        <MathRenderer :text="opt" />
                                    </div>
                                </div>

                                <!-- Isian Singkat Review -->
                                <div v-else class="mb-3 text-xs space-y-1.5">
                                    <div class="p-3 bg-muted/40 rounded-xl border border-border/60 space-y-1">
                                        <p class="text-muted-foreground">
                                            Jawabanmu:
                                            <span :class="result.is_correct ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold line-through'">
                                                {{ result.user_answer || 'Tidak dijawab' }}
                                            </span>
                                        </p>
                                        <p class="text-emerald-700 font-bold">
                                            Kunci: {{ result.correct_answer }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Explanation -->
                                <div v-if="showExplanation && result.explanation" class="p-3.5 bg-sky-50/80 border border-sky-200/80 rounded-2xl text-xs text-sky-950 space-y-1 leading-relaxed">
                                    <p class="font-bold text-sky-800 flex items-center gap-1.5">
                                        <Icon icon="mdi:lightbulb-outline" class="w-4 h-4 text-sky-600" />
                                        Pembahasan:
                                    </p>
                                    <div class="text-sky-900">
                                        <MathRenderer :text="result.explanation" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Navigation -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-6">
                <Link
                    :href="route('user.practice.history')"
                    class="flex items-center justify-center gap-2 bg-primary text-primary-foreground py-3 px-5 rounded-xl text-xs sm:text-sm font-bold shadow-xs hover:bg-primary/90 transition active:scale-98"
                >
                    <Icon icon="mdi:history" class="w-4 h-4" />
                    Lihat Riwayat Pengerjaan
                </Link>

                <Link
                    :href="route('user.practice.statistics')"
                    class="flex items-center justify-center gap-2 bg-card border border-border/80 text-foreground py-3 px-5 rounded-xl text-xs sm:text-sm font-bold shadow-xs hover:bg-accent/70 transition active:scale-98"
                >
                    <Icon icon="mdi:chart-box-outline" class="w-4 h-4 text-primary" />
                    Buka Statistik Saya
                </Link>

                <a
                    v-if="session.status === 'completed'"
                    :href="route('user.practice.certificate', session.id)"
                    class="sm:col-span-2 flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground py-3 px-5 rounded-xl text-xs sm:text-sm font-bold shadow-sm transition active:scale-98"
                >
                    <Icon icon="mdi:certificate-outline" class="w-4 h-4" />
                    Unduh Sertifikat PDF
                </a>
            </div>
        </div>
    </UserLayout>
</template>
