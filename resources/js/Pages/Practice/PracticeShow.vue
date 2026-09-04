<script setup>
import { inject, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';
import { Icon } from '@iconify/vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Card from '@/Components/ui/card/Card.vue';

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
        router.visit(route('practice.history'));
    }

    if (props.session?.total_score >= 80) {
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

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <UserLayout>
        <Head title="Detail Riwayat - KPM SMART" />

        <template #header-title>Detail Riwayat</template>
        <template #header-sub>{{ session.package?.title }}</template>


        <Link :href="route('practice.history')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-300 mb-4 hover:gap-2.5 group">
            <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Riwayat
        </Link>

        <template v-if="!allHidden">
            <div class="max-w-3xl mx-auto space-y-6">

                <!-- Summary -->
                <Card class="p-5 shadow-card border border-border/60 rounded-2xl anim-show-fade">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-center">
                        <div class="p-3 rounded-xl bg-muted/30"><p class="text-xs text-muted-foreground font-medium">Soal</p><p class="text-sm font-semibold truncate mt-1">{{ session.package?.title }}</p></div>
                        <div v-if="showScore" class="p-3 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5"><p class="text-xs text-muted-foreground font-medium">Skor</p><p class="text-3xl font-extrabold bg-gradient-to-br from-primary to-primary/70 bg-clip-text text-transparent mt-1">{{ Number(session.total_score || 0).toFixed(1) }}</p></div>
                        <div v-if="showAnswerKey" class="p-3 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100/50"><p class="text-xs text-emerald-700/60 font-medium">Benar</p><p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ session.correct_answer }}</p></div>
                        <div v-if="showAnswerKey" class="p-3 rounded-xl bg-gradient-to-br from-red-50 to-red-100/50"><p class="text-xs text-red-700/60 font-medium">Salah</p><p class="text-3xl font-extrabold text-red-500 mt-1">{{ session.wrong_answer }}</p></div>
                        <div class="p-3 rounded-xl bg-muted/30"><p class="text-xs text-muted-foreground font-medium">Tanggal</p><p class="text-sm font-medium mt-1">{{ formatDate(session.created_at) }}</p></div>
                    </div>
                    <div v-if="!showScore || !showAnswerKey" class="mt-3 text-center">
                        <span class="text-xs text-muted-foreground bg-muted/80 px-3 py-1.5 rounded-full font-medium"><Icon icon="mdi:lock-outline" class="w-3 h-3 inline-block mr-1 align-middle" /> Beberapa informasi disembunyikan oleh pengatur soal</span>
                    </div>
                </Card>

                <!-- Answer Details -->
                <div v-if="showAnswerKey" class="space-y-4">
                    <h3 class="text-lg font-semibold flex items-center gap-1.5"><Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 text-primary" /> Detail Jawaban</h3>
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['anim-show-item bg-card rounded-2xl border border-border/60 p-4 border-l-4 shadow-sm hover:shadow-md transition-shadow duration-300', result.is_correct ? 'border-l-emerald-500' : 'border-l-red-500']"
                         :style="{ animationDelay: (idx * 0.08) + 's' }">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-muted to-muted/60 flex items-center justify-center text-xs font-bold shadow-sm">{{ idx + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <Badge :variant="result.is_correct ? 'success' : 'destructive'" class="text-[10px] mb-2"><Icon :icon="result.is_correct ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3 h-3 inline-block mr-0.5 align-middle" /> {{ result.is_correct ? 'Benar' : 'Salah' }}</Badge>
                                <div v-if="result.image" class="mb-3"><img :src="result.image" alt="Gambar" class="max-w-full h-auto rounded-xl border border-border/40 max-h-48 object-contain shadow-sm" @error="$event.target.style.display='none'" /></div>
                                <p class="text-sm leading-relaxed mb-3" v-html="result.question"></p>
                                <div v-if="!result.type || result.type === 'pilihan_ganda'" class="space-y-1.5 text-sm">
                                    <p v-if="result.user_answer" class="flex items-center gap-1.5"><span class="text-muted-foreground">Jawaban Anda:</span> <span :class="result.is_correct ? 'text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded' : 'text-red-500 font-semibold bg-red-50 px-1.5 py-0.5 rounded'">{{ result.user_answer }}</span></p>
                                    <p v-if="!result.is_correct" class="flex items-center gap-1.5"><span class="text-muted-foreground">Jawaban Benar:</span> <span class="text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">{{ result.correct_answer }}</span></p>
                                </div>
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
                                <div v-if="showExplanation && result.explanation" class="mt-3 p-3.5 bg-gradient-to-r from-blue-50 to-blue-100/30 border border-blue-100 rounded-xl text-sm text-blue-800 shadow-sm"><Icon icon="mdi:lightbulb-on-outline" class="w-4 h-4 inline-block mr-1 align-middle text-blue-500" /> <strong class="text-blue-700">Pembahasan:</strong> {{ result.explanation }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-card rounded-2xl border border-border/60 p-10 text-center shadow-sm anim-show-fade">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-muted/80 to-muted/40 flex items-center justify-center mx-auto mb-5 shadow-inner"><Icon icon="mdi:lock-outline" class="w-8 h-8 text-muted-foreground/70" /></div>
                    <p class="text-muted-foreground font-medium">Kunci Jawaban Disembunyikan oleh Pengatur Soal</p>
                </div>

                <!-- Certificate Download -->
                <div v-if="session.status === 'completed'" class="flex justify-center anim-show-fade">
                    <a :href="route('practice.certificate', session.id)"
                       class="inline-flex items-center gap-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-6 py-3.5 rounded-2xl text-sm font-semibold hover:from-emerald-600 hover:to-emerald-700 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 min-h-[48px]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Unduh Sertifikat
                    </a>
                </div>
            </div>
        </template>
    </UserLayout>
</template>

<style scoped>
@keyframes showFadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
.anim-show-item {
    animation: showFadeInUp 0.5s ease-out both;
}
@keyframes showFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.anim-show-fade { animation: showFadeIn 0.4s ease-out both; }
</style>
