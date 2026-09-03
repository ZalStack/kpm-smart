<script setup>
import { inject, computed } from 'vue';
const route = inject('route');

import { Head, Link, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';

const props = defineProps({
    package: { type: Object, required: true },
    totalCards: { type: Number, default: 0 },
    totalQuestions: { type: Number, default: 0 },
    completedSession: { type: Object, default: null },
    inProgressSession: { type: Object, default: null },
    completedCardIds: { type: Object, default: () => ({}) },
    inProgressCardIds: { type: Object, default: () => ({}) },
});

const canStart = computed(() => {
    return props.package.schedule_status !== 'expired' && props.package.schedule_status !== 'upcoming';
});

function startPractice(cardId) {
    router.post(route('practice.start', props.package.id), { card_id: cardId });
}

function isCardCompleted(cardId) {
    return Object.prototype.hasOwnProperty.call(props.completedCardIds, cardId);
}

function isCardInProgress(cardId) {
    return Object.prototype.hasOwnProperty.call(props.inProgressCardIds, cardId);
}

function cardSessionId(cardId) {
    return props.completedCardIds[cardId] || props.inProgressCardIds[cardId] || null;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <UserLayout>
        <Head :title="package.title + ' - KPM SMART'" />

        <template #header-title>{{ package.title }}</template>
        <template #header-sub>Detail paket tugas</template>

        <!-- Back Link -->
        <Link :href="route('packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Paket
        </Link>

        <!-- Schedule Warnings -->
        <div v-if="package.schedule_status === 'expired'" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 flex items-start gap-3">
            <span class="text-2xl flex-shrink-0">⛔</span>
            <div>
                <p class="text-sm font-semibold text-red-800">Jadwal Telah Berakhir</p>
                <p class="text-xs text-red-600 mt-0.5">Paket ini sudah tidak tersedia untuk dikerjakan.</p>
            </div>
        </div>
        <div v-else-if="package.schedule_status === 'upcoming'" class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
            <span class="text-2xl flex-shrink-0">⏳</span>
            <div>
                <p class="text-sm font-semibold text-amber-800">Belum Tersedia</p>
                <p class="text-xs text-amber-600 mt-0.5">Paket ini akan tersedia sesuai jadwal yang ditentukan.</p>
            </div>
        </div>

        <!-- In Progress Global Session -->
        <div v-if="inProgressSession && !package.cards?.length" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📝</span>
                <div>
                    <p class="text-sm font-semibold text-blue-800">Tugas Sedang Berlangsung</p>
                    <p class="text-xs text-blue-600">Lanjutkan sesi yang belum selesai.</p>
                </div>
            </div>
            <Link :href="route('practice.show', inProgressSession.id)" class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition">
                Lanjutkan →
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Hero Card -->
                <div class="bg-card border rounded-2xl overflow-hidden shadow-card">
                    <div v-if="package.thumbnail" class="h-48 overflow-hidden">
                        <img :src="'/storage/' + package.thumbnail" :alt="package.title" class="w-full h-full object-cover" />
                    </div>
                    <div class="p-6">
                        <h1 class="text-xl font-bold mb-2">{{ package.title }}</h1>
                        <p class="text-muted-foreground text-sm mb-4 leading-relaxed">{{ package.description }}</p>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-if="package.bidang" variant="outline" class="text-xs">📂 {{ package.bidang }}</Badge>
                            <Badge v-if="package.level" variant="outline" class="text-xs">🎯 {{ package.level }}</Badge>
                            <Badge v-if="package.kelas" variant="outline" class="text-xs">🏫 {{ package.kelas }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div v-if="package.cards && package.cards.length > 0">
                    <h2 class="text-base font-semibold mb-3 flex items-center gap-2">
                        📋 Card Tugas
                        <span class="text-xs text-muted-foreground font-normal">({{ package.cards.length }} card)</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="card in package.cards" :key="card.id"
                             :class="[
                                 'bg-card border rounded-2xl overflow-hidden transition-all duration-200',
                                 isCardCompleted(card.id) ? 'border-emerald-200 shadow-sm' :
                                 isCardInProgress(card.id) ? 'border-blue-200 shadow-sm' :
                                 'border-border hover:shadow-card-hover hover:-translate-y-0.5'
                             ]">

                            <!-- Status Bar -->
                            <div :class="[
                                'h-1 w-full',
                                isCardCompleted(card.id) ? 'bg-emerald-500' :
                                isCardInProgress(card.id) ? 'bg-blue-500' : 'bg-muted'
                            ]"></div>

                            <div class="p-5">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="font-semibold text-sm leading-tight">{{ card.title }}</h3>
                                    <!-- Status Badge -->
                                    <span v-if="isCardCompleted(card.id)"
                                          class="flex-shrink-0 ml-2 inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-semibold px-2 py-0.5 rounded-full ring-1 ring-emerald-200">
                                        ✅ Selesai
                                    </span>
                                    <span v-else-if="isCardInProgress(card.id)"
                                          class="flex-shrink-0 ml-2 inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded-full ring-1 ring-blue-200">
                                        ⏳ Berlangsung
                                    </span>
                                </div>

                                <p class="text-xs text-muted-foreground mb-4 line-clamp-2">{{ card.description }}</p>

                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-muted-foreground flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                                        {{ card.question_count || 0 }} soal
                                    </span>

                                    <!-- Card Action Buttons -->
                                    <div class="flex gap-2">
                                        <!-- Completed: view result only, no retry -->
                                        <template v-if="isCardCompleted(card.id)">
                                            <Link :href="route('practice.show', cardSessionId(card.id))"
                                                  class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                                                📊 Lihat Hasil
                                            </Link>
                                        </template>
                                        <!-- In Progress: continue -->
                                        <template v-else-if="isCardInProgress(card.id)">
                                            <Link :href="route('practice.show', cardSessionId(card.id))"
                                                  class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">
                                                ▶️ Lanjutkan
                                            </Link>
                                        </template>
                                        <!-- Available to start -->
                                        <template v-else-if="canStart">
                                            <button @click="startPractice(card.id)"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary-foreground bg-primary px-3 py-1.5 rounded-lg hover:bg-primary/90 transition">
                                                📖 Kerjakan
                                            </button>
                                        </template>
                                        <!-- Not available -->
                                        <template v-else>
                                            <span class="text-xs text-muted-foreground">Tidak tersedia</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Completed notice -->
                                <div v-if="isCardCompleted(card.id)" class="mt-3 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                                    <p class="text-[10px] text-amber-700 flex items-center gap-1.5">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                        Soal hanya bisa dikerjakan 1 kali
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Cards: Single Start Button -->
                <div v-else-if="canStart" class="bg-card border rounded-2xl p-6 text-center">
                    <p class="text-sm text-muted-foreground mb-4">Paket ini tidak memiliki card. Klik mulai untuk mengerjakan semua soal.</p>
                    <button @click="startPractice(null)"
                            class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-primary/90 transition">
                        📖 Mulai Mengerjakan
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Stats -->
                <div class="bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="font-semibold text-sm mb-4">📦 Informasi Paket</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Total Card</span>
                            <span class="font-semibold">{{ totalCards }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Total Soal</span>
                            <span class="font-semibold">{{ totalQuestions }}</span>
                        </div>
                        <div class="border-t border-border/50 pt-3 flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Percobaan</span>
                            <span class="font-semibold text-amber-600">1x per card</span>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div v-if="package.start_date || package.end_date" class="bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="font-semibold text-sm mb-4">📅 Jadwal</h3>
                    <div class="space-y-2.5 text-sm">
                        <div v-if="package.start_date" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Mulai</span>
                            <span class="font-medium">{{ package.start_date }}</span>
                        </div>
                        <div v-if="package.end_date" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Selesai</span>
                            <span class="font-medium">{{ package.end_date }}</span>
                        </div>
                        <div v-if="package.start_time" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Jam Mulai</span>
                            <span class="font-medium">{{ package.start_time }}</span>
                        </div>
                        <div v-if="package.end_time" class="flex items-center justify-between">
                            <span class="text-muted-foreground">Jam Selesai</span>
                            <span class="font-medium">{{ package.end_time }}</span>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="font-semibold text-sm mb-4">⚙️ Pengaturan</h3>
                    <div class="space-y-2.5 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Kunci Jawaban</span>
                            <span :class="package.show_answer_key ? 'text-emerald-600 font-medium' : 'text-muted-foreground'">
                                {{ package.show_answer_key ? '✅ Ya' : '❌ Tidak' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Pembahasan</span>
                            <span :class="package.show_explanation ? 'text-emerald-600 font-medium' : 'text-muted-foreground'">
                                {{ package.show_explanation ? '✅ Ya' : '❌ Tidak' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Tampil Skor</span>
                            <span :class="package.show_score ? 'text-emerald-600 font-medium' : 'text-muted-foreground'">
                                {{ package.show_score ? '✅ Ya' : '❌ Tidak' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 1-Attempt Notice -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <div class="flex items-start gap-2.5">
                        <span class="text-xl flex-shrink-0">⚠️</span>
                        <div>
                            <p class="text-xs font-semibold text-amber-800">Peraturan Penting</p>
                            <p class="text-xs text-amber-700 mt-1">Setiap card soal hanya bisa dikerjakan <strong>1 kali</strong>. Pastikan kamu siap sebelum memulai!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
