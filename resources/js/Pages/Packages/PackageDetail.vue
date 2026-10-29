<script setup>
import { inject, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';

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

/** Progres penyelesaian seluruh kartu dalam paket ini. */
const cardProgress = computed(() => {
    const total = props.package.cards?.length || 0;
    if (!total) return 0;
    const done = props.package.cards.filter(c => isCardCompleted(c.id)).length;
    return Math.round((done / total) * 100);
});
</script>

<template>
    <UserLayout>
        <Head :title="package.title + ' - PR KK'" />

        <!-- Navigasi kembali -->
        <Link :href="route('user.packages.index')" class="back-link mb-5 group select-none">
            <Icon icon="mdi:arrow-left" class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" />
            Kembali ke PR Pekanan
        </Link>

        <!-- Peringatan jadwal -->
        <div
            v-if="package.schedule_status === 'expired'"
            class="anim-fade-in-up mb-6 flex items-start gap-3.5 rounded-2xl border border-rose-200 bg-rose-50 p-4 sm:p-5"
        >
            <span class="icon-box shrink-0 bg-rose-500 text-white">
                <Icon icon="mdi:close-octagon" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-sm font-bold text-rose-900">Jadwal Telah Berakhir</p>
                <p class="mt-0.5 text-sm leading-relaxed text-rose-700">Tugas PR ini sudah melewati batas waktu pengerjaan.</p>
            </div>
        </div>

        <div
            v-else-if="package.schedule_status === 'upcoming'"
            class="anim-fade-in-up mb-6 flex items-start gap-3.5 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5"
        >
            <span class="icon-box shrink-0 bg-amber-500 text-white">
                <Icon icon="mdi:clock-outline" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-sm font-bold text-amber-900">Belum Tersedia</p>
                <p class="mt-0.5 text-sm leading-relaxed text-amber-700">Paket soal ini akan dibuka sesuai jadwal yang telah ditentukan.</p>
            </div>
        </div>

        <!-- Sesi berjalan (paket tanpa kartu) -->
        <div
            v-if="inProgressSession && !package.cards?.length"
            class="anim-fade-in-up mb-6 flex flex-col gap-4 rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5"
        >
            <div class="flex items-center gap-3.5">
                <span class="icon-box shrink-0 bg-sky-500 text-white">
                    <Icon icon="mdi:pencil-outline" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-bold text-sky-900">Tugas Sedang Berjalan</p>
                    <p class="mt-0.5 text-sm text-sky-700">Kamu memiliki sesi pengerjaan aktif yang belum diselesaikan.</p>
                </div>
            </div>
            <Link :href="route('user.practice.show', inProgressSession.id)" class="btn-primary shrink-0 px-5 py-2.5 text-xs">
                Lanjutkan Sekarang
                <Icon icon="mdi:arrow-right" class="h-4 w-4" />
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Konten Utama -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Hero Paket -->
                <article class="ui-card anim-fade-in-up overflow-hidden">
                    <div v-if="package.thumbnail" class="h-48 overflow-hidden sm:h-60">
                        <img :src="'/storage/' + package.thumbnail" :alt="package.title" class="h-full w-full object-cover" />
                    </div>
                    <div v-else class="relative h-40 overflow-hidden bg-gradient-to-br from-primary via-indigo-600 to-violet-700 sm:h-48">
                        <div class="grid-dots absolute inset-0 opacity-30"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/25 bg-white/15 text-white backdrop-blur">
                                <Icon icon="mdi:book-open-page-variant" class="h-8 w-8" />
                            </span>
                        </div>
                    </div>

                    <div class="p-5 sm:p-7">
                        <div class="mb-3 flex flex-wrap gap-2">
                            <span v-if="package.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">
                                <Icon icon="mdi:folder-outline" class="h-3.5 w-3.5" />
                                {{ package.bidang }}
                            </span>
                            <span v-if="package.level" class="chip border border-indigo-200 bg-indigo-50 text-indigo-700">
                                <Icon icon="mdi:target" class="h-3.5 w-3.5" />
                                Level {{ package.level }}
                            </span>
                            <span v-if="package.kelas" class="chip border border-amber-200 bg-amber-50 text-amber-700">
                                <Icon icon="mdi:school-outline" class="h-3.5 w-3.5" />
                                Kelas {{ package.kelas }}
                            </span>
                        </div>

                        <h1 class="text-xl font-extrabold leading-tight tracking-tight text-foreground sm:text-2xl">
                            {{ package.title }}
                        </h1>

                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                            {{ package.description || 'Tidak ada deskripsi tambahan untuk paket soal ini.' }}
                        </p>

                        <!-- Progres kartu -->
                        <div v-if="package.cards?.length" class="mt-5">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-muted-foreground">Progres Pengerjaan Kartu</span>
                                <span class="text-primary">{{ cardProgress }}%</span>
                            </div>
                            <div class="progress-track mt-2">
                                <div class="progress-fill" :style="{ width: cardProgress + '%' }"></div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-border/60 pt-4 text-sm text-muted-foreground">
                            <span class="flex items-center gap-1.5 font-medium">
                                <Icon icon="mdi:cards-outline" class="h-4 w-4 text-primary" />
                                {{ package.cards?.length || 0 }} Kartu Tugas
                            </span>
                            <span class="flex items-center gap-1.5 font-medium">
                                <Icon icon="mdi:help-circle-outline" class="h-4 w-4 text-indigo-500" />
                                {{ totalQuestions }} Total Butir Soal
                            </span>
                        </div>
                    </div>
                </article>

                <!-- Daftar Kartu -->
                <div v-if="package.cards && package.cards.length > 0">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="section-title flex items-center gap-2">
                            <Icon icon="mdi:clipboard-text-outline" class="h-5 w-5 text-primary" />
                            Daftar Kartu Tugas
                        </h2>
                        <span class="text-xs text-muted-foreground">Pilih kartu untuk mulai mengerjakan</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            v-for="(card, index) in package.cards"
                            :key="card.id"
                            :style="{ animationDelay: (index * 60) + 'ms' }"
                            class="ui-card anim-fade-in-up flex flex-col justify-between p-5 transition-all duration-300"
                            :class="isCardCompleted(card.id)
                                ? 'border-emerald-200 bg-gradient-to-br from-emerald-50/60 to-card'
                                : isCardInProgress(card.id)
                                    ? 'border-sky-200 bg-gradient-to-br from-sky-50/60 to-card'
                                    : 'hover:border-primary/40 hover:shadow-card-hover'"
                        >
                            <div>
                                <div class="mb-2 flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-bold leading-snug text-foreground">{{ card.title }}</h3>
                                    <span
                                        v-if="isCardCompleted(card.id)"
                                        class="chip shrink-0 border border-emerald-200 bg-emerald-50 text-emerald-700"
                                    >
                                        <Icon icon="mdi:check-circle" class="h-3.5 w-3.5" /> Selesai
                                    </span>
                                    <span
                                        v-else-if="isCardInProgress(card.id)"
                                        class="chip shrink-0 border border-sky-200 bg-sky-50 text-sky-700"
                                    >
                                        <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" /> Berjalan
                                    </span>
                                </div>

                                <p class="mb-4 line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                                    {{ card.description || 'Kerjakan kartu soal ini dengan teliti.' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-border/60 pt-3">
                                <span class="flex items-center gap-1 text-xs font-semibold text-muted-foreground">
                                    <Icon icon="mdi:help-circle-outline" class="h-3.5 w-3.5 text-primary" />
                                    {{ card.question_count || 0 }} Soal
                                </span>

                                <div>
                                    <template v-if="isCardCompleted(card.id)">
                                        <Link
                                            :href="route('user.practice.show', cardSessionId(card.id))"
                                            class="inline-flex items-center gap-1 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            <Icon icon="mdi:chart-bar" class="h-4 w-4" />
                                            Hasil
                                        </Link>
                                    </template>
                                    <template v-else-if="isCardInProgress(card.id)">
                                        <Link
                                            :href="route('user.practice.show', cardSessionId(card.id))"
                                            class="inline-flex items-center gap-1 rounded-xl bg-sky-600 px-3 py-1.5 text-xs font-bold text-white shadow-2xs transition hover:bg-sky-700"
                                        >
                                            <Icon icon="mdi:play" class="h-4 w-4" />
                                            Lanjutkan
                                        </Link>
                                    </template>
                                    <template v-else-if="canStart">
                                        <button
                                            @click="startPractice(card.id)"
                                            class="btn-primary px-3.5 py-1.5 text-xs"
                                        >
                                            <Icon icon="mdi:pencil" class="h-3.5 w-3.5" />
                                            Kerjakan
                                        </button>
                                    </template>
                                    <template v-else>
                                        <span class="chip bg-muted text-muted-foreground">
                                            <Icon icon="mdi:lock-outline" class="h-3.5 w-3.5" /> Terkunci
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mulai tunggal (paket tanpa kartu) -->
                <div v-else-if="canStart" class="empty-state">
                    <div class="empty-state-icon !bg-primary/10 !text-primary !shadow-none">
                        <Icon icon="mdi:book-open-page-variant" class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-bold text-foreground">Siap untuk Memulai?</h3>
                    <p class="mt-1.5 max-w-md text-sm leading-relaxed text-muted-foreground">
                        Paket ini berisi seluruh butir soal langsung. Pastikan koneksi stabil dan kamu memiliki waktu luang sebelum memulai.
                    </p>
                    <button @click="startPractice(null)" class="btn-primary mt-5 px-8 py-3">
                        <Icon icon="mdi:play-circle-outline" class="h-5 w-5" />
                        Mulai Mengerjakan Sekarang
                    </button>
                </div>
            </div>

            <!-- Panel Samping -->
            <div class="space-y-4">
                <div class="rounded-2xl border border-amber-200/90 bg-amber-50/80 p-5">
                    <div class="flex items-start gap-3">
                        <span class="icon-box-sm shrink-0 bg-amber-500 text-white">
                            <Icon icon="mdi:alert-outline" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-950">Peraturan Pengerjaan</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-amber-800">
                                Setiap kartu tugas hanya dapat dikerjakan <strong>1 kali</strong>. Pastikan kamu memiliki waktu luang dan konsentrasi penuh sebelum menekan tombol mulai.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-card p-5">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Petunjuk Pengerjaan</p>
                    <ul class="space-y-3 text-sm leading-relaxed text-muted-foreground">
                        <li class="flex items-start gap-2.5">
                            <Icon icon="mdi:check-circle-outline" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Jawaban akan disimpan otomatis secara berkala.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <Icon icon="mdi:check-circle-outline" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Jangan membuka tab atau aplikasi lain selama tes berlangsung untuk menjaga integritas nilai.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <Icon icon="mdi:check-circle-outline" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Setelah selesai, kamu dapat langsung melihat hasil dan pembahasan.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
