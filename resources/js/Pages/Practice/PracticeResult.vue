<script setup>
import { inject, computed, ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import confetti from 'canvas-confetti';
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
    if (s >= 90) return { label: 'Luar Biasa!', icon: 'mdi:trophy', iconColor: 'text-yellow-500', color: 'text-emerald-600', bg: 'from-emerald-500 to-emerald-700', ring: 'ring-emerald-200', light: 'bg-emerald-50' };
    if (s >= 75) return { label: 'Bagus!', icon: 'mdi:party-popper', iconColor: '', color: 'text-blue-600', bg: 'from-blue-500 to-blue-700', ring: 'ring-blue-200', light: 'bg-blue-50' };
    if (s >= 60) return { label: 'Cukup Baik', icon: 'mdi:thumb-up', iconColor: '', color: 'text-yellow-600', bg: 'from-yellow-500 to-yellow-600', ring: 'ring-yellow-200', light: 'bg-yellow-50' };
    return { label: 'Perlu Semangat!', icon: 'mdi:arm-flex', iconColor: '', color: 'text-red-500', bg: 'from-red-400 to-red-600', ring: 'ring-red-200', light: 'bg-red-50' };
});

const accuracyPct = computed(() => {
    const total = props.correct + props.wrong + props.unanswered;
    if (!total) return 0;
    return Math.round((props.correct / total) * 100);
});

// Circumference for SVG circle (r=40)
const circumference = 2 * Math.PI * 40;
const scoreOffset = computed(() => circumference - (Math.min(props.totalScore, 100) / 100) * circumference);

onMounted(() => {
    if (props.totalScore >= 80) {
        const duration = 2000;
        const end = Date.now() + duration;
        const colors = ['#16a34a', '#22c55e', '#facc15', '#f59e0b', '#10b981'];
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
        <Head title="Hasil Tugas - KPM SMART" />

        <template #header-title><Icon icon="mdi:target" class="w-5 h-5 text-emerald-500 inline-block align-middle mr-1.5" /> Hasil Tugas</template>
        <template #header-sub>{{ session.package?.title }}</template>

        <div class="max-w-3xl mx-auto space-y-5">

            <!-- Score Hero Card -->
            <div :class="['anim-result-section rounded-2xl overflow-hidden shadow-xl text-white bg-gradient-to-br', scoreGrade.bg]">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <!-- Score Ring -->
                        <div class="relative flex-shrink-0 anim-pulse-ring">
                            <svg class="w-36 h-36 -rotate-90 drop-shadow-lg" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="8"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="white"
                                        stroke-width="8" stroke-linecap="round"
                                        :stroke-dasharray="circumference"
                                        :stroke-dashoffset="scoreOffset"
                                        class="transition-all duration-1000 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black leading-none drop-shadow-md">
                                    {{ showScore ? totalScore.toFixed(0) : '?' }}
                                </span>
                                <span class="text-[10px] text-white/70 mt-1 font-medium tracking-wide uppercase">{{ showScore ? 'Nilai' : 'Disembunyikan' }}</span>
                            </div>
                        </div>

                        <!-- Grade Info -->
                        <div class="text-center sm:text-left flex-1">
                            <p class="text-4xl mb-2"><Icon :icon="scoreGrade.icon" :class="['w-10 h-10 drop-shadow', scoreGrade.iconColor]" /></p>
                            <h2 class="text-2xl font-bold drop-shadow-sm">{{ scoreGrade.label }}</h2>
                            <p class="text-white/80 text-sm mt-1.5">{{ session.package?.title }}</p>
                            <p class="text-white/60 text-xs mt-1.5 inline-flex items-center gap-1.5"><Icon icon="mdi:calendar-outline" class="w-4 h-4" /> {{ formatDate(session.finished_at || session.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-4 gap-px bg-white/10 border-t border-white/15">
                    <div class="bg-black/10 px-3 py-3.5 text-center hover:bg-black/20 transition-colors">
                        <p class="text-xl font-bold drop-shadow-sm">{{ correct }}</p>
                        <p class="text-[10px] text-white/70 mt-0.5">Benar</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3.5 text-center hover:bg-black/20 transition-colors">
                        <p class="text-xl font-bold drop-shadow-sm">{{ wrong }}</p>
                        <p class="text-[10px] text-white/70 mt-0.5">Salah</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3.5 text-center hover:bg-black/20 transition-colors">
                        <p class="text-xl font-bold drop-shadow-sm">{{ unanswered }}</p>
                        <p class="text-[10px] text-white/70 mt-0.5">Kosong</p>
                    </div>
                    <div class="bg-black/10 px-3 py-3.5 text-center hover:bg-black/20 transition-colors">
                        <p class="text-sm font-bold drop-shadow-sm">{{ formatDuration(session.duration_seconds) }}</p>
                        <p class="text-[10px] text-white/70 mt-0.5">Waktu</p>
                    </div>
                </div>
            </div>

            <!-- Info hidden notice -->
            <div v-if="!showScore || !showAnswerKey" class="anim-result-section bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-center gap-2.5 shadow-sm">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <p class="text-xs text-amber-700 font-medium">Beberapa informasi disembunyikan oleh pengatur soal</p>
            </div>

            <!-- 1-attempt notice -->
            <div class="anim-result-section bg-card border border-border/60 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 shadow-sm"><Icon icon="mdi:pin" class="w-5 h-5 text-primary" /></div>
                <div>
                    <p class="text-sm font-semibold">Tugas Sudah Selesai</p>
                    <p class="text-xs text-muted-foreground mt-0.5">Soal ini hanya bisa dikerjakan <strong>1 kali</strong>. Kamu bisa melihat riwayat dan statistik belajarmu di bawah.</p>
                </div>
            </div>

            <!-- Tabs -->
            <div v-if="showAnswerKey" class="anim-result-section bg-card border border-border/60 rounded-2xl overflow-hidden shadow-sm">
                <!-- Tab Nav -->
                <div class="flex border-b border-border/50 bg-gradient-to-r from-muted/40 to-muted/20">
                    <button @click="activeTab = 'summary'"
                            :class="['flex-1 py-3.5 text-sm font-medium transition-all duration-300 inline-flex items-center justify-center gap-2 relative', activeTab === 'summary' ? 'text-primary bg-card shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/30']">
                        <Icon icon="mdi:chart-bar" class="w-5 h-5" /> Ringkasan
                        <span v-if="activeTab === 'summary'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-0.5 bg-primary rounded-full"></span>
                    </button>
                    <button @click="activeTab = 'detail'"
                            :class="['flex-1 py-3.5 text-sm font-medium transition-all duration-300 inline-flex items-center justify-center gap-2 relative', activeTab === 'detail' ? 'text-primary bg-card shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/30']">
                        <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5" /> Detail Jawaban ({{ results.length }})
                        <span v-if="activeTab === 'detail'" class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-0.5 bg-primary rounded-full"></span>
                    </button>
                </div>

                <!-- Tab: Summary -->
                <div v-if="activeTab === 'summary'" class="p-5 anim-result-fade">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-5 bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl border border-emerald-100/60 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-2">
                                <Icon icon="mdi:check-circle" class="w-6 h-6 text-emerald-600" />
                            </div>
                            <p class="text-3xl font-extrabold text-emerald-600">{{ correct }}</p>
                            <p class="text-xs text-emerald-700/60 mt-1 font-medium">Jawaban Benar</p>
                        </div>
                        <div class="text-center p-5 bg-gradient-to-br from-red-50 to-red-100/50 rounded-xl border border-red-100/60 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center mx-auto mb-2">
                                <Icon icon="mdi:close-circle" class="w-6 h-6 text-red-500" />
                            </div>
                            <p class="text-3xl font-extrabold text-red-500">{{ wrong }}</p>
                            <p class="text-xs text-red-700/60 mt-1 font-medium">Jawaban Salah</p>
                        </div>
                        <div class="text-center p-5 bg-gradient-to-br from-muted/50 to-muted/30 rounded-xl border border-border/40 shadow-sm hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
                            <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center mx-auto mb-2">
                                <Icon icon="mdi:checkbox-blank-circle-outline" class="w-6 h-6 text-muted-foreground" />
                            </div>
                            <p class="text-3xl font-extrabold text-muted-foreground">{{ unanswered }}</p>
                            <p class="text-xs text-muted-foreground/60 mt-1 font-medium">Tidak Dijawab</p>
                        </div>
                    </div>

                    <!-- Accuracy bar -->
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-xs text-muted-foreground">
                            <span class="font-medium">Akurasi jawaban</span>
                            <span class="font-bold text-foreground text-sm">{{ accuracyPct }}%</span>
                        </div>
                        <div class="h-3.5 bg-muted/50 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full rounded-full transition-all duration-700 ease-out shadow-sm"
                                 :class="accuracyPct >= 75 ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : accuracyPct >= 60 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 'bg-gradient-to-r from-red-400 to-red-500'"
                                 :style="{ width: accuracyPct + '%' }"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-muted-foreground font-medium">
                            <span>0%</span><span>50%</span><span>100%</span>
                        </div>
                    </div>
                </div>

                <!-- Tab: Detail -->
                <div v-else class="divide-y divide-border/30 anim-result-fade">
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['p-4 transition-colors duration-200 hover:bg-muted/20', result.is_correct ? 'bg-emerald-50/20' : (result.user_answer ? 'bg-red-50/20' : 'bg-muted/10')]">
                        <div class="flex items-start gap-3">
                            <!-- Number + Status icon -->
                            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-muted to-muted/60 flex items-center justify-center text-xs font-bold shadow-sm">{{ idx + 1 }}</div>
                                <span class="text-base leading-none inline-flex items-center"><Icon v-if="result.is_correct" icon="mdi:check-circle" class="w-5 h-5 text-green-600" /><Icon v-else-if="result.user_answer" icon="mdi:close-circle" class="w-5 h-5 text-red-500" /><Icon v-else icon="mdi:checkbox-blank-circle-outline" class="w-5 h-5 text-muted-foreground/50" /></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Question image -->
                                <div v-if="result.image" class="mb-3">
                                    <img :src="result.image" alt="Gambar soal" class="max-w-full h-auto rounded-xl border border-border/40 max-h-48 object-contain shadow-sm" @error="$event.target.style.display='none'" />
                                </div>

                                <!-- Question text -->
                                <p class="text-sm leading-relaxed mb-3 font-medium" v-html="result.question"></p>

                                <!-- Pilihan Ganda: Options (show all with highlight) -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="space-y-1.5 mb-3">
                                    <div v-for="opt in result.options" :key="opt"
                                         :class="[
                                             'flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs border transition-all duration-200',
                                             opt === result.correct_answer ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-semibold shadow-sm' :
                                             opt === result.user_answer && !result.is_correct ? 'bg-red-50 border-red-300 text-red-700 line-through shadow-sm' :
                                             'bg-transparent border-border/40 text-muted-foreground hover:bg-muted/20'
                                         ]">
                                        <span class="flex-shrink-0">
                                             <Icon v-if="opt === result.correct_answer" icon="mdi:check-circle" class="w-4 h-4 text-green-600" /><Icon v-else-if="opt === result.user_answer && !result.is_correct" icon="mdi:close-circle" class="w-4 h-4 text-red-500" /><span v-else class="text-muted-foreground/50">○</span>
                                        </span>
                                        {{ opt }}
                                    </div>
                                </div>

                                <!-- Isian Singkat: Text answer comparison -->
                                <div v-else class="mb-3 space-y-2">
                                    <div class="bg-muted/30 rounded-xl p-4 space-y-2 border border-border/40">
                                        <div v-if="result.user_answer" class="flex items-start gap-2">
                                            <span class="text-xs font-medium text-muted-foreground mt-0.5">Jawaban Anda:</span>
                                            <span :class="[
                                                'text-sm font-semibold px-2.5 py-1 rounded-lg',
                                                result.is_correct ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200 line-through'
                                            ]">{{ result.user_answer }}</span>
                                        </div>
                                        <div v-else class="flex items-center gap-2 text-xs text-muted-foreground italic">
                                            <Icon icon="mdi:checkbox-blank-circle-outline" class="w-4 h-4" /> Tidak dijawab
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <span class="text-xs font-medium text-muted-foreground mt-0.5">Kunci Jawaban:</span>
                                            <span class="text-sm font-bold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">{{ result.correct_answer }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- User answer vs correct (pilihan_ganda) -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="text-xs space-y-1">
                                    <div v-if="!result.user_answer" class="text-muted-foreground italic inline-flex items-center gap-1.5"><Icon icon="mdi:checkbox-blank-circle-outline" class="w-4 h-4" /> Tidak dijawab</div>
                                    <div v-if="!result.is_correct && result.user_answer" class="flex items-center gap-1.5">
                                        <span class="text-muted-foreground">Jawaban kamu:</span>
                                        <span class="text-red-500 font-semibold bg-red-50 px-1.5 py-0.5 rounded">{{ result.user_answer }}</span>
                                    </div>
                                    <div v-if="!result.is_correct" class="flex items-center gap-1.5">
                                        <span class="text-muted-foreground">Jawaban benar:</span>
                                        <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">{{ result.correct_answer }}</span>
                                    </div>
                                </div>

                                <!-- Explanation -->
                                <div v-if="showExplanation && result.explanation" class="mt-3 flex items-start gap-2.5 bg-gradient-to-r from-blue-50 to-blue-100/30 border border-blue-100 rounded-xl p-3.5 shadow-sm">
                                    <span class="text-blue-500 flex-shrink-0 mt-0.5"><Icon icon="mdi:lightbulb-outline" class="w-5 h-5 text-blue-500" /></span>
                                    <div>
                                        <p class="text-[10px] font-bold text-blue-700 mb-0.5 uppercase tracking-wider">Pembahasan</p>
                                        <p class="text-xs text-blue-800 leading-relaxed">{{ result.explanation }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Answer key hidden -->
            <div v-else class="anim-result-section bg-card border border-border/60 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-muted/80 to-muted/40 flex items-center justify-center text-2xl mx-auto mb-5 shadow-inner"><Icon icon="mdi:lock-outline" class="w-8 h-8 text-muted-foreground/70" /></div>
                <p class="font-semibold text-foreground text-lg">Kunci Jawaban Disembunyikan</p>
                <p class="text-sm text-muted-foreground mt-1.5">Pengatur soal belum mengaktifkan tampilan kunci jawaban</p>
            </div>

            <!-- Action Buttons - NO RETRY -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-4 anim-result-section">
                <Link :href="route('practice.history')"
                      class="flex items-center justify-center gap-2.5 bg-gradient-to-r from-primary to-primary/80 text-primary-foreground px-5 py-3.5 rounded-2xl text-sm font-semibold hover:from-primary/90 hover:to-primary/70 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 min-h-[48px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                    Riwayat Tugas
                </Link>
                <Link :href="route('practice.statistics')"
                      class="flex items-center justify-center gap-2.5 bg-card border border-border/60 text-foreground px-5 py-3.5 rounded-2xl text-sm font-semibold hover:bg-muted/50 transition-all duration-300 shadow-sm hover:shadow-md hover:-translate-y-0.5 min-h-[48px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Statistik Saya
                </Link>
                <a :href="route('practice.certificate', session.id)"
                   class="sm:col-span-2 flex items-center justify-center gap-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-3.5 rounded-2xl text-sm font-semibold hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 min-h-[48px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    Unduh Sertifikat
                </a>
                <Link :href="route('packages.index')"
                      class="sm:col-span-2 flex items-center justify-center gap-2.5 bg-muted/50 text-muted-foreground px-5 py-3.5 rounded-2xl text-sm font-medium hover:bg-muted/80 transition-all duration-300 min-h-[48px]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    Lihat Soal Lain
                </Link>
            </div>

        </div>
    </UserLayout>
</template>

<style scoped>
@keyframes resultFadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.anim-result-section {
    animation: resultFadeInUp 0.5s ease-out both;
}
.anim-result-section:nth-child(1) { animation-delay: 0s; }
.anim-result-section:nth-child(2) { animation-delay: 0.08s; }
.anim-result-section:nth-child(3) { animation-delay: 0.16s; }
.anim-result-section:nth-child(4) { animation-delay: 0.24s; }
.anim-result-section:nth-child(5) { animation-delay: 0.32s; }
.anim-result-section:nth-child(6) { animation-delay: 0.4s; }
@keyframes resultFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.anim-result-fade { animation: resultFadeIn 0.4s ease-out both; }
@keyframes pulseRing {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}
.anim-pulse-ring { animation: pulseRing 2s ease-in-out infinite; }
</style>
