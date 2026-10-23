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
    if (s >= 90) return { label: 'Sangat Memuaskan!', icon: 'mdi:trophy', bg: 'from-emerald-700 via-emerald-600 to-teal-700', accent: 'text-emerald-200' };
    if (s >= 75) return { label: 'Bagus Sekali!', icon: 'mdi:star-face', bg: 'from-teal-700 via-teal-600 to-cyan-700', accent: 'text-teal-200' };
    if (s >= 60) return { label: 'Cukup Baik', icon: 'mdi:thumb-up-outline', bg: 'from-amber-600 via-amber-500 to-orange-600', accent: 'text-amber-100' };
    return { label: 'Tetap Semangat!', icon: 'mdi:arm-flex', bg: 'from-rose-700 via-rose-600 to-orange-600', accent: 'text-rose-100' };
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

        <div class="mx-auto max-w-4xl space-y-6">
            <!-- ====== HERO NILAI ====== -->
            <section :class="['anim-fade-in relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br text-white shadow-xl', scoreGrade.bg]">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-dots absolute inset-0 opacity-20"></div>
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/15 blur-3xl"></div>
                </div>

                <div class="relative z-10 p-6 sm:p-8">
                    <div class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left">
                        <!-- Cincin Nilai -->
                        <div class="relative shrink-0">
                            <svg class="h-36 w-36 -rotate-90 drop-shadow-md" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8" />
                                <circle
                                    cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="8" stroke-linecap="round"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="scoreOffset"
                                    class="transition-all duration-1000 ease-out"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black leading-none drop-shadow">{{ showScore ? totalScore.toFixed(0) : '?' }}</span>
                                <span class="mt-1 text-[10px] font-bold uppercase tracking-wider text-white/80">
                                    {{ showScore ? 'Nilai Akhir' : 'Disembunyikan' }}
                                </span>
                            </div>
                        </div>

                        <!-- Info Nilai -->
                        <div class="flex-1">
                            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold backdrop-blur">
                                <Icon :icon="scoreGrade.icon" class="h-4 w-4 text-amber-300" />
                                <span>{{ scoreGrade.label }}</span>
                            </div>
                            <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">{{ session.package?.title }}</h2>
                            <p class="mt-2 flex items-center justify-center gap-1.5 text-xs font-medium text-white/75 sm:justify-start">
                                <Icon icon="mdi:calendar-check" class="h-4 w-4" />
                                Diselesaikan pada {{ formatDate(session.finished_at || session.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Statistik -->
                <div class="relative z-10 grid grid-cols-4 gap-px border-t border-white/20 bg-white/15">
                    <div class="bg-black/15 px-2 py-3 text-center">
                        <p class="text-xl font-black">{{ correct }}</p>
                        <p class="text-[10px] font-semibold text-white/75">Benar</p>
                    </div>
                    <div class="bg-black/15 px-2 py-3 text-center">
                        <p class="text-xl font-black">{{ wrong }}</p>
                        <p class="text-[10px] font-semibold text-white/75">Salah</p>
                    </div>
                    <div class="bg-black/15 px-2 py-3 text-center">
                        <p class="text-xl font-black">{{ unanswered }}</p>
                        <p class="text-[10px] font-semibold text-white/75">Kosong</p>
                    </div>
                    <div class="bg-black/15 px-2 py-3 text-center">
                        <p class="mt-1 truncate text-sm font-bold">{{ formatDuration(session.duration_seconds) }}</p>
                        <p class="text-[10px] font-semibold text-white/75">Durasi</p>
                    </div>
                </div>
            </section>

            <!-- Catatan -->
            <div class="anim-fade-in-up flex items-center gap-3.5 rounded-2xl border border-border/80 bg-card p-4 sm:p-5">
                <span class="icon-box shrink-0 bg-primary/10 text-primary">
                    <Icon icon="mdi:information-outline" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-bold text-foreground">Sesi Tugas Telah Diselesaikan</p>
                    <p class="mt-0.5 text-sm leading-relaxed text-muted-foreground">
                        Nilai telah tercatat di statistik belajarmu. Kamu bisa meninjau ringkasan dan pembahasan di bawah ini.
                    </p>
                </div>
            </div>

            <!-- ====== TINJAUAN ====== -->
            <div v-if="showAnswerKey" class="ui-card anim-fade-in-up overflow-hidden">
                <!-- Tab -->
                <div class="flex border-b border-border/60 bg-muted/40 p-1.5">
                    <button
                        @click="activeTab = 'summary'"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all',
                            activeTab === 'summary' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        <Icon icon="mdi:chart-bar" class="h-4 w-4 text-primary" />
                        <span class="hidden sm:inline">Ringkasan Performa</span><span class="sm:hidden">Ringkasan</span>
                    </button>
                    <button
                        @click="activeTab = 'detail'"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-xs font-bold transition-all',
                            activeTab === 'detail' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        <Icon icon="mdi:clipboard-text-outline" class="h-4 w-4 text-primary" />
                        <span class="hidden sm:inline">Review Butir Soal ({{ results.length }})</span><span class="sm:hidden">Butir Soal</span>
                    </button>
                </div>

                <!-- Tab Ringkasan -->
                <div v-if="activeTab === 'summary'" class="anim-fade-in space-y-6 p-5 sm:p-7">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/70 p-5 text-center">
                            <span class="icon-box mx-auto mb-2 bg-emerald-500 text-white">
                                <Icon icon="mdi:check" class="h-5 w-5" />
                            </span>
                            <p class="text-3xl font-black text-emerald-700">{{ correct }}</p>
                            <p class="mt-1 text-xs font-bold text-emerald-800">Jawaban Benar</p>
                        </div>

                        <div class="rounded-2xl border border-rose-200/80 bg-rose-50/70 p-5 text-center">
                            <span class="icon-box mx-auto mb-2 bg-rose-500 text-white">
                                <Icon icon="mdi:close" class="h-5 w-5" />
                            </span>
                            <p class="text-3xl font-black text-rose-700">{{ wrong }}</p>
                            <p class="mt-1 text-xs font-bold text-rose-800">Jawaban Salah</p>
                        </div>

                        <div class="rounded-2xl border border-border/80 bg-muted/50 p-5 text-center">
                            <span class="icon-box mx-auto mb-2 bg-muted text-muted-foreground">
                                <Icon icon="mdi:checkbox-blank-circle-outline" class="h-5 w-5" />
                            </span>
                            <p class="text-3xl font-black text-muted-foreground">{{ unanswered }}</p>
                            <p class="mt-1 text-xs font-bold text-muted-foreground">Tidak Dijawab</p>
                        </div>
                    </div>

                    <!-- Akurasi -->
                    <div class="space-y-2 border-t border-border/60 pt-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-bold text-foreground">Tingkat Akurasi Jawaban</span>
                            <span class="font-black text-primary">{{ accuracyPct }}%</span>
                        </div>
                        <div class="progress-track h-3">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-700" :style="{ width: accuracyPct + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Tab Detail Jawaban -->
                <div v-else class="anim-fade-in divide-y divide-border/40">
                    <article
                        v-for="(result, idx) in results"
                        :key="idx"
                        class="p-5 transition-colors sm:p-6"
                        :class="result.is_correct ? 'bg-emerald-50/15' : (result.user_answer ? 'bg-rose-50/15' : 'bg-muted/10')"
                    >
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-muted text-xs font-bold shadow-2xs">
                                {{ idx + 1 }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="mb-2">
                                    <span
                                        class="chip inline-flex border"
                                        :class="result.is_correct
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-rose-200 bg-rose-50 text-rose-700'"
                                    >
                                        <Icon :icon="result.is_correct ? 'mdi:check-circle' : 'mdi:close-circle'" class="h-3.5 w-3.5" />
                                        {{ result.is_correct ? 'Benar' : (result.user_answer ? 'Salah' : 'Kosong') }}
                                    </span>
                                </div>

                                <div v-if="result.image" class="mb-3">
                                    <img
                                        :src="result.image.startsWith('/') || result.image.startsWith('http') ? result.image : '/storage/' + result.image"
                                        alt="Gambar soal"
                                        class="max-h-48 w-auto max-w-full rounded-xl border border-border/60 object-contain shadow-2xs"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>

                                <p class="mb-3 text-sm font-medium leading-relaxed text-foreground">
                                    <MathRenderer :text="result.question" />
                                </p>

                                <!-- Pilihan Ganda -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="mb-3 space-y-1.5">
                                    <div
                                        v-for="opt in result.options"
                                        :key="opt"
                                        :class="[
                                            'flex items-center gap-2.5 rounded-xl border px-3 py-2 text-sm transition-all',
                                            opt === result.correct_answer
                                                ? 'border-emerald-300 bg-emerald-50 font-bold text-emerald-800'
                                                : opt === result.user_answer && !result.is_correct
                                                    ? 'border-rose-300 bg-rose-50 font-medium text-rose-800 line-through'
                                                    : 'border-border/60 bg-card text-muted-foreground'
                                        ]"
                                    >
                                        <Icon v-if="opt === result.correct_answer" icon="mdi:check-circle" class="h-4 w-4 shrink-0 text-emerald-600" />
                                        <Icon v-else-if="opt === result.user_answer && !result.is_correct" icon="mdi:close-circle" class="h-4 w-4 shrink-0 text-rose-500" />
                                        <span v-else class="h-4 w-4 shrink-0 rounded-full border border-muted-foreground/30"></span>
                                        <MathRenderer :text="opt" />
                                    </div>
                                </div>

                                <!-- Isian Singkat -->
                                <div v-else class="mb-3 space-y-1.5 text-sm">
                                    <div class="space-y-1 rounded-xl border border-border/60 bg-muted/40 p-3">
                                        <p class="text-muted-foreground">
                                            Jawabanmu:
                                            <span :class="result.is_correct ? 'font-bold text-emerald-700' : 'font-bold text-rose-600 line-through'">
                                                {{ result.user_answer || 'Tidak dijawab' }}
                                            </span>
                                        </p>
                                        <p class="font-bold text-emerald-700">Kunci: {{ result.correct_answer }}</p>
                                    </div>
                                </div>

                                <!-- Pembahasan -->
                                <div v-if="showExplanation && result.explanation" class="space-y-1 rounded-2xl border border-sky-200/80 bg-sky-50/80 p-3.5 text-sm leading-relaxed text-sky-950">
                                    <p class="flex items-center gap-1.5 font-bold text-sky-800">
                                        <Icon icon="mdi:lightbulb-outline" class="h-4 w-4 text-sky-600" />
                                        Pembahasan:
                                    </p>
                                    <div class="text-sky-900">
                                        <MathRenderer :text="result.explanation" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- ====== AKSI ====== -->
            <div class="grid grid-cols-1 gap-3 pb-6 sm:grid-cols-2">
                <Link :href="route('user.practice.history')" class="btn-primary py-3.5">
                    <Icon icon="mdi:history" class="h-4 w-4" />
                    Lihat Riwayat Pengerjaan
                </Link>

                <Link :href="route('user.practice.statistics')" class="btn-outline py-3.5">
                    <Icon icon="mdi:chart-box-outline" class="h-4 w-4 text-primary" />
                    Buka Statistik Saya
                </Link>

                <a
                    v-if="session.status === 'completed'"
                    :href="route('user.practice.certificate', session.id)"
                    class="btn-outline col-span-1 py-3.5 sm:col-span-2"
                >
                    <Icon icon="mdi:certificate-outline" class="h-4 w-4 text-amber-600" />
                    Unduh Sertifikat PDF
                </a>
            </div>
        </div>
    </UserLayout>
</template>
