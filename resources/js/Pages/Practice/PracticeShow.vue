<script setup>
import { inject, onMounted, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import MathRenderer from '@/Components/shared/MathRenderer.vue';

const route = inject('route');

const props = defineProps({
    session: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    showAnswerKey: { type: Boolean, default: true },
    showExplanation: { type: Boolean, default: true },
    showScore: { type: Boolean, default: true },
    isDeadlinePassed: { type: Boolean, default: false },
});

const allHidden = computed(() => !props.showScore && !props.showAnswerKey && !props.showExplanation);

onMounted(() => {
    if (allHidden.value) {
        alert('Detail hasil tugas tidak tersedia.');
        router.visit(route('user.packages.index'));
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
        <Head title="Pembahasan Soal - KPM SMART" />

        <Link :href="route('user.packages.index')" class="back-link mb-5 group select-none">
            <Icon icon="mdi:arrow-left" class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" />
            Kembali ke PR Siswa Kelas Khusus
        </Link>

        <template v-if="!allHidden">
            <div class="mx-auto max-w-4xl space-y-6">
                <!-- Ringkasan -->
                <section class="ui-card anim-fade-in p-5 sm:p-6">
                    <div class="mb-4 flex flex-col gap-1 border-b border-border/60 pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-primary">Detail Sesi Tugas</p>
                            <h2 class="mt-1 truncate text-lg font-extrabold tracking-tight text-foreground">{{ session.package?.title }}</h2>
                            <p class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                                <Icon icon="mdi:calendar-clock" class="h-3.5 w-3.5" />
                                {{ formatDate(session.finished_at || session.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div v-if="showScore" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Nilai Akhir</p>
                            <p class="mt-1 text-3xl font-black text-emerald-600">{{ Number(session.total_score || 0).toFixed(1) }}</p>
                        </div>
                        <div v-if="showAnswerKey" class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Benar</p>
                            <p class="mt-1 text-3xl font-black text-emerald-600">{{ session.correct_answer }}</p>
                        </div>
                        <div v-if="showAnswerKey" class="rounded-2xl border border-rose-100 bg-rose-50/60 p-4 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Salah</p>
                            <p class="mt-1 text-3xl font-black text-rose-500">{{ session.wrong_answer }}</p>
                        </div>
                        <div class="rounded-2xl border border-border/60 bg-muted/40 p-4 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Durasi</p>
                            <p class="mt-1 text-lg font-bold text-foreground">{{ session.duration_seconds ? Math.floor(session.duration_seconds / 60) + ' mnt' : '-' }}</p>
                        </div>
                    </div>

                    <div v-if="!showScore || !showAnswerKey" class="mt-4 text-center">
                        <span class="chip bg-muted/80 text-muted-foreground">
                            <Icon icon="mdi:lock-outline" class="h-3.5 w-3.5" />
                            Beberapa informasi disembunyikan oleh pengatur soal
                        </span>
                    </div>
                </section>

                <!-- Detail Soal -->
                <div v-if="showAnswerKey" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="section-title flex items-center gap-2">
                            <Icon icon="mdi:lightbulb-outline" class="h-5 w-5 text-primary" />
                            Pembahasan Soal
                        </h3>
                        <span class="text-xs font-semibold text-muted-foreground">{{ results.length }} Soal</span>
                    </div>

                    <div v-if="isDeadlinePassed" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-semibold text-amber-800 flex items-center gap-2">
                        <Icon icon="mdi:clock-alert-outline" class="h-4 w-4 shrink-0 text-amber-600" />
                        <span>Pembahasan soal telah otomatis ditutup karena jadwal tugas telah melewati batas waktu (deadline).</span>
                    </div>

                    <article
                        v-for="(result, idx) in results"
                        :key="idx"
                        class="ui-card anim-fade-in-up border-l-4 p-5"
                        :class="result.is_correct ? 'border-l-emerald-500' : 'border-l-rose-500'"
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
                                        {{ result.is_correct ? 'Jawaban Benar' : 'Jawaban Salah' }}
                                    </span>
                                </div>

                                <div v-if="result.image" class="mb-3">
                                    <img
                                        :src="result.image.startsWith('/') || result.image.startsWith('http') ? result.image : '/storage/' + result.image"
                                        alt="Gambar Soal"
                                        class="max-h-48 w-auto max-w-full rounded-xl border border-border/60 object-contain shadow-2xs"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>

                                <p class="mb-3 text-sm font-medium leading-relaxed text-foreground">
                                    <MathRenderer :text="result.question" />
                                </p>

                                <!-- Pilihan Ganda -->
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="space-y-1.5 text-sm">
                                    <p v-if="result.user_answer" class="flex flex-wrap items-center gap-2">
                                        <span class="text-muted-foreground">Jawabanmu:</span>
                                        <span
                                            class="rounded-md border px-2 py-0.5 font-bold"
                                            :class="result.is_correct
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                : 'border-rose-200 bg-rose-50 text-rose-700'"
                                        >
                                            {{ result.user_answer }}
                                        </span>
                                    </p>
                                    <p v-if="!result.is_correct" class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-muted-foreground">Kunci Jawaban:</span>
                                        <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">
                                            {{ result.correct_answer }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Isian Singkat -->
                                <div v-else class="mb-3 space-y-2 text-sm">
                                    <div class="space-y-1.5 rounded-xl border border-border/60 bg-muted/40 p-3.5">
                                        <div v-if="result.user_answer" class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-muted-foreground">Jawabanmu:</span>
                                            <span
                                                class="rounded border px-2 py-0.5 font-bold"
                                                :class="result.is_correct
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                    : 'border-rose-200 bg-rose-50 text-rose-700 line-through'"
                                            >
                                                {{ result.user_answer }}
                                            </span>
                                        </div>
                                        <div v-else class="flex items-center gap-1 italic text-muted-foreground">
                                            <Icon icon="mdi:checkbox-blank-circle-outline" class="h-3.5 w-3.5" /> Tidak dijawab
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-medium text-muted-foreground">Kunci Jawaban:</span>
                                            <span class="rounded border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">
                                                {{ result.correct_answer }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pembahasan -->
                                <div v-if="showExplanation && result.explanation" class="mt-3.5 space-y-1 rounded-2xl border border-sky-200/80 bg-sky-50/70 p-4 text-sm leading-relaxed text-sky-950">
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

                <!-- Aksi Bawah -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pb-6 pt-2">
                    <Link :href="route('user.packages.index')" class="btn-outline px-6 py-3.5 w-full sm:w-auto text-xs">
                        <Icon icon="mdi:book-open-page-variant" class="h-4 w-4" />
                        Kembali ke PR Siswa Kelas Khusus
                    </Link>

                    <a
                        v-if="session.status === 'completed'"
                        :href="route('user.practice.certificate', session.id)"
                        class="btn-primary px-6 py-3.5 w-full sm:w-auto text-xs"
                    >
                        <Icon icon="mdi:certificate-outline" class="h-5 w-5" />
                        Unduh Sertifikat Pengerjaan
                    </a>
                </div>
            </div>
        </template>
    </UserLayout>
</template>
