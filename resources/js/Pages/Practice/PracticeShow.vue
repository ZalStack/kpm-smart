<script setup>
import { inject, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
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
        alert('Detail riwayat tidak tersedia. Pengatur paket telah menonaktifkan semua tampilan hasil.');
        router.visit(route('practice.history'));
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

        <Link :href="route('practice.history')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Riwayat
        </Link>

        <template v-if="!allHidden">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Summary -->
                <Card class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                        <div><p class="text-xs text-muted-foreground">Paket</p><p class="text-sm font-semibold truncate">{{ session.package?.title }}</p></div>
                        <div v-if="showScore"><p class="text-xs text-muted-foreground">Skor</p><p class="text-2xl font-bold text-primary">{{ Number(session.total_score || 0).toFixed(1) }}</p></div>
                        <div v-if="showAnswerKey"><p class="text-xs text-muted-foreground">Benar</p><p class="text-2xl font-bold text-green-600">{{ session.correct_answer }}</p></div>
                        <div v-if="showAnswerKey"><p class="text-xs text-muted-foreground">Salah</p><p class="text-2xl font-bold text-red-500">{{ session.wrong_answer }}</p></div>
                        <div><p class="text-xs text-muted-foreground">Tanggal</p><p class="text-sm font-medium">{{ formatDate(session.created_at) }}</p></div>
                    </div>
                    <div v-if="!showScore || !showAnswerKey" class="mt-3 text-center">
                        <span class="text-xs text-muted-foreground bg-muted px-3 py-1 rounded-full">🔒 Beberapa informasi disembunyikan oleh pengatur paket</span>
                    </div>
                </Card>

                <!-- Answer Details -->
                <div v-if="showAnswerKey" class="space-y-3">
                    <h3 class="text-lg font-semibold">📋 Detail Jawaban</h3>
                    <div v-for="(result, idx) in results" :key="idx"
                         :class="['bg-card rounded-xl border p-4 border-l-4', result.is_correct ? 'border-l-green-500' : 'border-l-red-500']">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-muted flex items-center justify-center text-xs font-bold">{{ idx + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <Badge :variant="result.is_correct ? 'success' : 'destructive'" class="text-[10px] mb-2">{{ result.is_correct ? '✅ Benar' : '❌ Salah' }}</Badge>
                                <div v-if="result.image" class="mb-2"><img :src="result.image" alt="Gambar" class="max-w-full h-auto rounded-lg border max-h-48 object-contain" @error="$event.target.style.display='none'" /></div>
                                <p class="text-sm leading-relaxed mb-2" v-html="result.question"></p>
                                <div class="space-y-1 text-sm">
                                    <p v-if="result.user_answer"><span class="text-muted-foreground">Jawaban Anda:</span> <span :class="result.is_correct ? 'text-green-600 font-medium' : 'text-red-500'">{{ result.user_answer }}</span></p>
                                    <p v-if="!result.is_correct"><span class="text-muted-foreground">Jawaban Benar:</span> <span class="text-green-600 font-medium">{{ result.correct_answer }}</span></p>
                                </div>
                                <div v-if="showExplanation && result.explanation" class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-800">💡 <strong>Pembahasan:</strong> {{ result.explanation }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-card rounded-xl border p-8 text-center">
                    <div class="text-4xl mb-3">🔒</div>
                    <p class="text-muted-foreground">Kunci Jawaban Disembunyikan oleh Pengatur Paket</p>
                </div>
            </div>
        </template>
    </UserLayout>
</template>
