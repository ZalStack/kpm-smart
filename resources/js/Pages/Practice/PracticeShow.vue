<script setup>
import { inject, onMounted, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import MathRenderer from '@/Components/shared/MathRenderer.vue';

const route = inject('route');

const props = defineProps({
    session: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    showAnswerKey: { type: Boolean, default: true },
    showExplanation: { type: Boolean, default: true },
    showScore: { type: Boolean, default: true },
});

const allHidden = computed(() => !props.showScore && !props.showAnswerKey && !props.showExplanation);

onMounted(() => {
    if (allHidden.value) {
        alert('Detail riwayat tidak tersedia. Pengatur soal telah menonaktifkan semua tampilan hasil.');
        router.visit(route('user.practice.history'));
    }

    if (props.session?.total_score >= 80) {
        const duration = 2000;
        const end = Date.now() + duration;
        const colors = ['#10b981', '#059669', '#f59e0b', '#3b82f6', '#14b8a6'];
        (function frame() {
            confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors });
            confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors });
            if (Date.now() < end) requestAnimationFrame(frame);
        })();
    }
});

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <UserLayout>
        <Head title="Detail Riwayat Tugas - KPM SMART" />

        <!-- Back Navigation -->
        <Link
            :href="route('user.practice.history')"
            class="inline-flex items-center gap-2 text-xs font-semibold text-muted-foreground hover:text-foreground transition-all duration-200 mb-6 group select-none"
        >
            <Icon icon="mdi:arrow-left" class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" />
            Kembali ke Riwayat Pengerjaan
        </Link>

        <template v-if="!allHidden">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Summary Card -->
                <div class="bg-card rounded-3xl border border-border/80 p-6 shadow-xs anim-fade-in">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3.5 rounded-2xl bg-muted/40 border border-border/60 col-span-2 sm:col-span-1">
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Judul Soal</p>
                            <p class="text-xs font-bold text-foreground truncate mt-1">{{ session.package?.title }}</p>
                        </div>
                        <div v-if="showScore" class="p-3.5 rounded-2xl bg-emerald-50 text-emerald-900 border border-emerald-200">
                            <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Nilai Akhir</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-0.5">{{ Number(session.total_score || 0).toFixed(1) }}</p>
                        </div>
                        <div v-if="showAnswerKey" class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                            <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Benar</p>
                            <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-0.5">{{ session.correct_answer }}</p>
                        </div>
                        <div v-if="showAnswerKey" class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-100">
                            <p class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Salah</p>
                            <p class="text-2xl sm:text-3xl font-black text-rose-500 mt-0.5">{{ session.wrong_answer }}</p>
                        </div>
                    </div>

                    <div v-if="!showScore || !showAnswerKey" class="mt-4 text-center">
                        <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground bg-muted/80 px-3.5 py-1.5 rounded-full font-medium">
                            <Icon icon="mdi:lock-outline" class="w-3.5 h-3.5" />
                            Beberapa informasi disembunyikan oleh pengatur soal
                        </span>
                    </div>
                </div>

                <!-- Answer Details -->
                <div v-if="showAnswerKey" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-foreground flex items-center gap-2">
                            <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 text-primary" />
                            Detail & Pembahasan Soal
                        </h3>
                        <span class="text-xs text-muted-foreground font-semibold">{{ results.length }} Soal</span>
                    </div>

                    <div
                        v-for="(result, idx) in results"
                        :key="idx"
                        :class="[
                            'bg-card rounded-2xl border border-border/80 p-5 shadow-xs transition-all border-l-4 anim-fade-in',
                            result.is_correct ? 'border-l-emerald-500' : 'border-l-rose-500'
                        ]"
                    >
                        <div class="flex items-start gap-3.5">
                            <span class="shrink-0 w-8 h-8 rounded-xl bg-muted font-bold text-xs flex items-center justify-center text-foreground shadow-2xs">
                                {{ idx + 1 }}
                            </span>

                            <div class="flex-1 min-w-0">
                                <div class="mb-2">
                                    <Badge :variant="result.is_correct ? 'success' : 'destructive'" class="text-[10px] font-bold">
                                        <Icon :icon="result.is_correct ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                                        {{ result.is_correct ? 'Jawaban Benar' : 'Jawaban Salah' }}
                                    </Badge>
                                </div>

                                <div v-if="result.image" class="mb-3">
                                    <img
                                        :src="result.image.startsWith('/') || result.image.startsWith('http') ? result.image : '/storage/' + result.image"
                                        alt="Gambar Soal"
                                        class="max-w-full h-auto rounded-xl border border-border/60 max-h-48 object-contain shadow-2xs"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>

                                <p class="text-sm leading-relaxed mb-3 text-foreground font-medium">
                                    <MathRenderer :text="result.question" />
                                </p>

                                <!-- Pilihan Ganda -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="space-y-1 text-xs">
                                    <p v-if="result.user_answer" class="flex items-center gap-2">
                                        <span class="text-muted-foreground">Jawabanmu:</span>
                                        <span :class="result.is_correct ? 'text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md' : 'text-rose-700 font-bold bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md'">
                                            {{ result.user_answer }}
                                        </span>
                                    </p>
                                    <p v-if="!result.is_correct" class="flex items-center gap-2 mt-1">
                                        <span class="text-muted-foreground">Kunci Jawaban:</span>
                                        <span class="text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                            {{ result.correct_answer }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Isian Singkat -->
                                <div v-else class="mb-3 space-y-2 text-xs">
                                    <div class="bg-muted/40 rounded-xl p-3.5 space-y-1.5 border border-border/60">
                                        <div v-if="result.user_answer" class="flex items-center gap-2">
                                            <span class="font-medium text-muted-foreground">Jawabanmu:</span>
                                            <span :class="result.is_correct ? 'font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200' : 'font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 line-through'">
                                                {{ result.user_answer }}
                                            </span>
                                        </div>
                                        <div v-else class="text-muted-foreground italic flex items-center gap-1">
                                            <Icon icon="mdi:checkbox-blank-circle-outline" class="w-3.5 h-3.5" /> Tidak dijawab
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-muted-foreground">Kunci Jawaban:</span>
                                            <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                {{ result.correct_answer }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Explanation Box -->
                                <div v-if="showExplanation && result.explanation" class="mt-3.5 p-4 bg-sky-50/70 border border-sky-200/80 rounded-2xl text-xs text-sky-950 leading-relaxed space-y-1">
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

                <!-- Certificate Download -->
                <div v-if="session.status === 'completed'" class="flex justify-center pt-2">
                    <a
                        :href="route('user.practice.certificate', session.id)"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3.5 rounded-2xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200 active:scale-95"
                    >
                        <Icon icon="mdi:certificate-outline" class="w-5 h-5" />
                        Unduh Sertifikat Pengerjaan
                    </a>
                </div>
            </div>
        </template>
    </UserLayout>
</template>
