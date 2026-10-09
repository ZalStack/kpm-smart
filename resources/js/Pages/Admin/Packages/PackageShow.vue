<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Button from '@/Components/ui/button/Button.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
});

function getScheduleStatus(pkg) {
    if (!pkg.start_date && !pkg.end_date) {
        return { label: 'Tanpa Batas Waktu', color: 'bg-muted text-muted-foreground border-border' };
    }

    const now = new Date();
    if (pkg.start_date) {
        const startTime = (pkg.start_time || '00:00').substring(0, 5);
        const start = new Date(pkg.start_date + 'T' + startTime);
        if (now < start) return { label: 'Akan Datang', color: 'bg-blue-50 text-blue-700 border-blue-200' };
    }

    if (pkg.end_date) {
        const endTime = (pkg.end_time || '23:59').substring(0, 5);
        const end = new Date(pkg.end_date + 'T' + endTime + ':59');
        if (now > end) return { label: 'Telah Berakhir', color: 'bg-rose-50 text-rose-700 border-rose-200' };
    }

    return { label: 'Sedang Berlangsung', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
}
</script>

<template>
    <AdminLayout>
        <Head :title="package.title + ' - Detail Soal'" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:package-variant" class="w-5 h-5" />
                </span>
                <span>Detail Paket Soal</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="space-y-6">
            <!-- Navigation Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <Link :href="route('admin.packages.index')"
                      class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                    <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                    Kembali ke Bank Soal
                </Link>

                <div class="flex items-center gap-1.5 p-1 bg-muted/60 rounded-xl w-fit">
                    <Link :href="route('admin.packages.edit.informasi', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:pencil" class="w-3.5 h-3.5" /> Informasi
                    </Link>
                    <Link :href="route('admin.packages.edit.cards', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:clipboard-text-outline" class="w-3.5 h-3.5" /> Card ({{ package.cards?.length || 0 }})
                    </Link>
                    <Link :href="route('admin.packages.edit.questions', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5" /> Soal ({{ package.questions?.length || 0 }})
                    </Link>
                    <Link :href="route('admin.packages.detail', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-card text-foreground shadow-sm flex items-center gap-1.5">
                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5 text-primary" /> Detail
                    </Link>
                </div>
            </div>

            <!-- Overview Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info Card -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-card rounded-3xl border border-border/60 overflow-hidden shadow-sm">
                        <div v-if="package.thumbnail" class="h-48 sm:h-56 overflow-hidden relative bg-muted">
                            <img :src="'/storage/' + package.thumbnail" :alt="package.title" class="w-full h-full object-cover" />
                        </div>

                        <div class="p-6 sm:p-7 space-y-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h1 class="text-xl sm:text-2xl font-black text-foreground">{{ package.title }}</h1>
                                    <p class="text-xs sm:text-sm text-muted-foreground mt-1 leading-relaxed">{{ package.description }}</p>
                                </div>
                                <span :class="['inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border shrink-0', package.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200']">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="package.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    {{ package.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2 pt-2 border-t border-border/50">
                                <span v-if="package.bidang" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                    <Icon icon="mdi:folder-outline" class="w-3.5 h-3.5" /> Bidang: {{ package.bidang }}
                                </span>
                                <span v-if="package.level" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">
                                    <Icon icon="mdi:star-outline" class="w-3.5 h-3.5" /> Level: {{ package.level }}
                                </span>
                                <span v-if="package.kelas" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                    <Icon icon="mdi:school-outline" class="w-3.5 h-3.5" /> Kelas: {{ package.kelas }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cards Summary -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-border/50">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 text-primary" />
                                <h3 class="font-bold text-base text-foreground">Daftar Card Materi ({{ package.cards?.length || 0 }})</h3>
                            </div>
                            <Link :href="route('admin.packages.edit.cards', package.id)" class="text-xs font-semibold text-primary hover:underline">
                                Kelola Card →
                            </Link>
                        </div>

                        <div v-if="package.cards && package.cards.length > 0" class="space-y-3">
                            <div v-for="(card, cardIdx) in package.cards" :key="card.id"
                                 class="p-4 rounded-2xl bg-muted/30 border border-border/40 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-bold text-sm text-foreground">{{ card.title }}</h4>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ card.description || 'Tanpa deskripsi' }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold shrink-0">
                                    {{ card.question_count || 0 }} butir soal
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-muted-foreground text-center py-6">Belum ada card materi dibuat pada paket ini.</p>
                    </div>
                </div>

                <!-- Sidebar Info & Actions -->
                <div class="space-y-5">
                    <!-- Schedule Box -->
                    <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm space-y-4">
                        <h4 class="font-bold text-sm text-foreground flex items-center gap-2">
                            <Icon icon="mdi:calendar-clock" class="w-4 h-4 text-primary" /> Jadwal & Status
                        </h4>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Status Pelaksanaan</span>
                                <span :class="['px-2.5 py-0.5 rounded-full font-bold border', getScheduleStatus(package).color]">
                                    {{ getScheduleStatus(package).label }}
                                </span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Tanggal Mulai</span>
                                <span class="font-semibold text-foreground">{{ package.start_date || 'Bebas' }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Tanggal Berakhir</span>
                                <span class="font-semibold text-foreground">{{ package.end_date || 'Bebas' }}</span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Jam Pelaksanaan</span>
                                <span class="font-semibold text-foreground">{{ package.start_time ? `${package.start_time} - ${package.end_time}` : '24 Jam' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Rules Box -->
                    <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm space-y-4">
                        <h4 class="font-bold text-sm text-foreground flex items-center gap-2">
                            <Icon icon="mdi:tune-variant" class="w-4 h-4 text-primary" /> Aturan Evaluasi
                        </h4>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Kunci Jawaban</span>
                                <span class="font-bold" :class="package.show_answer_key ? 'text-emerald-600' : 'text-muted-foreground'">
                                    {{ package.show_answer_key ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-border/40">
                                <span class="text-muted-foreground">Pembahasan</span>
                                <span class="font-bold" :class="package.show_explanation ? 'text-emerald-600' : 'text-muted-foreground'">
                                    {{ package.show_explanation ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-muted-foreground">Skor Akhir</span>
                                <span class="font-bold" :class="package.show_score ? 'text-emerald-600' : 'text-muted-foreground'">
                                    {{ package.show_score ? 'Ditampilkan' : 'Disembunyikan' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Links -->
                    <div class="bg-card rounded-3xl border border-border/60 p-5 shadow-sm space-y-2.5">
                        <Link :href="route('admin.packages.edit.informasi', package.id)" class="block">
                            <Button class="w-full gap-2 font-semibold shadow-sm">
                                <Icon icon="mdi:pencil" class="w-4 h-4" /> Edit Informasi Paket
                            </Button>
                        </Link>
                        <Link :href="route('admin.packages.edit.questions', package.id)" class="block">
                            <Button variant="outline" class="w-full gap-2 font-semibold">
                                <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-primary" /> Kelola Bank Soal
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
