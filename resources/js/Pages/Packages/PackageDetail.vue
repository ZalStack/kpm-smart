<script setup>
import { inject, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');

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
    router.post(route('user.practice.start', props.package.id), { card_id: cardId });
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
</script>

<template>
    <UserLayout>
        <Head :title="package.title + ' - KPM SMART'" />

        <!-- Back Navigation -->
        <Link
            :href="route('user.packages.index')"
            class="inline-flex items-center gap-2 text-xs font-semibold text-muted-foreground hover:text-foreground transition-all duration-200 mb-6 group select-none"
        >
            <Icon icon="mdi:arrow-left" class="w-4 h-4 transition-transform duration-200 group-hover:-translate-x-1" />
            Kembali ke PR Pekanan
        </Link>

        <!-- Schedule Warning Banners -->
        <div
            v-if="package.schedule_status === 'expired'"
            class="bg-rose-50 border border-rose-200/80 rounded-2xl p-4 sm:p-5 mb-6 flex items-start gap-3.5 shadow-2xs"
        >
            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <Icon icon="mdi:close-octagon" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-sm font-bold text-rose-900">Jadwal Telah Berakhir</p>
                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">Tugas PR ini sudah melewati batas waktu pengerjaan.</p>
            </div>
        </div>

        <div
            v-else-if="package.schedule_status === 'upcoming'"
            class="bg-amber-50 border border-amber-200/80 rounded-2xl p-4 sm:p-5 mb-6 flex items-start gap-3.5 shadow-2xs"
        >
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <Icon icon="mdi:clock-outline" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">Belum Tersedia</p>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">Paket soal ini akan dibuka sesuai dengan jadwal yang telah ditentukan.</p>
            </div>
        </div>

        <!-- In Progress Global Session (Single Package) -->
        <div
            v-if="inProgressSession && !package.cards?.length"
            class="bg-sky-50 border border-sky-200/80 rounded-2xl p-4 sm:p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs"
        >
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <Icon icon="mdi:pencil-outline" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-sm font-bold text-sky-900">Tugas Sedang Berjalan</p>
                    <p class="text-xs text-sky-700 mt-0.5">Kamu memiliki sesi pengerjaan aktif yang belum diselesaikan.</p>
                </div>
            </div>
            <Link
                :href="route('user.practice.show', inProgressSession.id)"
                class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-xs"
            >
                Lanjutkan Sekarang
                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content: Package Info & Cards -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Package Hero Card -->
                <div class="bg-card border border-border/80 rounded-3xl overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                    <div v-if="package.thumbnail" class="h-48 sm:h-60 overflow-hidden">
                        <img :src="'/storage/' + package.thumbnail" :alt="package.title" class="w-full h-full object-cover" />
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <Badge v-if="package.bidang" variant="default" class="text-xs">
                                <Icon icon="mdi:folder-outline" class="w-3.5 h-3.5" />
                                {{ package.bidang }}
                            </Badge>
                            <Badge v-if="package.level" variant="info" class="text-xs">
                                <Icon icon="mdi:target" class="w-3.5 h-3.5" />
                                Level {{ package.level }}
                            </Badge>
                            <Badge v-if="package.kelas" variant="warning" class="text-xs">
                                <Icon icon="mdi:school-outline" class="w-3.5 h-3.5" />
                                Kelas {{ package.kelas }}
                            </Badge>
                        </div>

                        <h1 class="text-xl sm:text-2xl font-extrabold text-foreground mb-2 leading-tight">
                            {{ package.title }}
                        </h1>

                        <p class="text-muted-foreground text-xs sm:text-sm leading-relaxed mb-6">
                            {{ package.description || 'Tidak ada deskripsi tambahan untuk paket soal ini.' }}
                        </p>

                        <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground pt-4 border-t border-border/60">
                            <span class="flex items-center gap-1.5 font-medium">
                                <Icon icon="mdi:cards-outline" class="w-4 h-4 text-primary" />
                                {{ package.cards?.length || 0 }} Kartu Tugas
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1.5 font-medium">
                                <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-teal-600" />
                                {{ totalQuestions }} Total Butir Soal
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div v-if="package.cards && package.cards.length > 0">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                            <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 text-primary" />
                            Daftar Kartu Tugas
                        </h2>
                        <span class="text-xs text-muted-foreground font-semibold">
                            Pilih kartu untuk mulai mengerjakan
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="(card, index) in package.cards"
                            :key="card.id"
                            :style="{ animationDelay: (index * 60) + 'ms' }"
                            :class="[
                                'bg-card border rounded-2xl overflow-hidden p-5 transition-all duration-300 flex flex-col justify-between anim-fade-in-up',
                                isCardCompleted(card.id)
                                    ? 'border-emerald-200/90 bg-gradient-to-br from-emerald-50/20 to-card shadow-xs'
                                    : isCardInProgress(card.id)
                                        ? 'border-sky-200/90 bg-gradient-to-br from-sky-50/20 to-card shadow-xs'
                                        : 'border-border/80 hover:border-primary/40 hover:shadow-md hover:-translate-y-0.5'
                            ]"
                        >
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h3 class="font-bold text-sm text-foreground leading-snug">{{ card.title }}</h3>
                                    <span
                                        v-if="isCardCompleted(card.id)"
                                        class="shrink-0 inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-200"
                                    >
                                        <Icon icon="mdi:check-circle" class="w-3.5 h-3.5 text-emerald-600" /> Selesai
                                    </span>
                                    <span
                                        v-else-if="isCardInProgress(card.id)"
                                        class="shrink-0 inline-flex items-center gap-1 bg-sky-50 text-sky-700 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-sky-200"
                                    >
                                        <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5 text-sky-600" /> Berjalan
                                    </span>
                                </div>

                                <p class="text-xs text-muted-foreground line-clamp-2 mb-4 leading-relaxed">
                                    {{ card.description || 'Kerjakan kartu soal ini dengan teliti.' }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-border/60 flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold text-muted-foreground flex items-center gap-1">
                                    <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5 text-primary" />
                                    {{ card.question_count || 0 }} Soal
                                </span>

                                <!-- Action Button per Card -->
                                <div>
                                    <template v-if="isCardCompleted(card.id)">
                                        <Link
                                            :href="route('user.practice.show', cardSessionId(card.id))"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl hover:bg-emerald-100 transition"
                                        >
                                            <Icon icon="mdi:chart-bar" class="w-4 h-4" />
                                            Hasil
                                        </Link>
                                    </template>
                                    <template v-else-if="isCardInProgress(card.id)">
                                        <Link
                                            :href="route('user.practice.show', cardSessionId(card.id))"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 px-3 py-1.5 rounded-xl transition shadow-2xs"
                                        >
                                            <Icon icon="mdi:play" class="w-4 h-4" />
                                            Lanjutkan
                                        </Link>
                                    </template>
                                    <template v-else-if="canStart">
                                        <button
                                            @click="startPractice(card.id)"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-primary-foreground bg-primary hover:bg-primary/90 px-3.5 py-1.5 rounded-xl transition shadow-2xs active:scale-95"
                                        >
                                            <Icon icon="mdi:pencil" class="w-3.5 h-3.5" />
                                            Kerjakan
                                        </button>
                                    </template>
                                    <template v-else>
                                        <span class="text-xs text-muted-foreground">Terkunci</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Start for Packages without Cards -->
                <div v-else-if="canStart" class="bg-card border border-border/80 rounded-3xl p-8 text-center shadow-xs">
                    <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3 shadow-2xs">
                        <Icon icon="mdi:book-open-page-variant" class="w-7 h-7" />
                    </div>
                    <h3 class="text-base font-bold text-foreground mb-1">Siap untuk Memulai?</h3>
                    <p class="text-xs text-muted-foreground max-w-md mx-auto mb-5 leading-relaxed">
                        Paket ini berisi seluruh butir soal langsung. Pastikan koneksi stabil sebelum memulai.
                    </p>
                    <Button @click="startPractice(null)" size="lg" class="px-8 font-bold">
                        <Icon icon="mdi:play-circle-outline" class="w-5 h-5" />
                        Mulai Mengerjakan Sekarang
                    </Button>
                </div>
            </div>

            <!-- Right Sidebar Notice -->
            <div class="space-y-4">
                <div class="bg-amber-50/80 border border-amber-200/90 rounded-3xl p-5 shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <Icon icon="mdi:alert-outline" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-950 uppercase tracking-wider">Peraturan Pengerjaan</p>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Setiap kartu tugas hanya dapat dikerjakan <strong>1 kali</strong>. Pastikan kamu memiliki waktu luang dan konsentrasi penuh sebelum menekan tombol mulai.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border/80 rounded-3xl p-5 shadow-xs space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Petunjuk Pengerjaan</p>
                    <ul class="text-xs text-muted-foreground space-y-2 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">•</span>
                            <span>Jawaban akan disimpan otomatis secara berkala.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">•</span>
                            <span>Jangan membuka tab atau aplikasi lain selama tes berlangsung untuk menjaga integritas nilai.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold">•</span>
                            <span>Setelah selesai, kamu dapat langsung melihat hasil dan pembahasan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
