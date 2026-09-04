<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
    package: { type: Object, required: true },
});

function getScheduleStatus(pkg) {
    if (!pkg.start_date && !pkg.end_date) {
        return { label: 'Tanpa Batas', variant: 'outline' };
    }

    const now = new Date();

    if (pkg.start_date) {
        const startTime = (pkg.start_time || '00:00').substring(0, 5);
        const start = new Date(pkg.start_date + 'T' + startTime);
        if (now < start) return { label: 'Akan Datang', variant: 'secondary' };
    }

    if (pkg.end_date) {
        const endTime = (pkg.end_time || '23:59').substring(0, 5);
        const end = new Date(pkg.end_date + 'T' + endTime + ':59');
        if (now > end) return { label: 'Berakhir', variant: 'destructive' };
    }

    return { label: 'Berlangsung', variant: 'success' };
}
</script>

<template>
    <AdminLayout>
        <Head :title="package.title + ' - Detail Soal'" />

        <template #header-title>Detail Soal</template>
        <template #header-sub>Informasi lengkap mengenai soal tugas</template>

        <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-200 mb-6 hover:translate-x-[-2px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Soal
        </Link>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Package Info Card -->
                <div class="bg-card rounded-2xl border shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0ms">
                    <div v-if="package.thumbnail" class="h-48 overflow-hidden">
                        <img :src="'/storage/' + package.thumbnail" :alt="package.title" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105" />
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h1 class="text-2xl font-bold mb-1">{{ package.title }}</h1>
                                <p class="text-muted-foreground text-sm">{{ package.description }}</p>
                            </div>
                            <Badge :variant="package.is_active ? 'success' : 'destructive'">{{ package.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <Badge v-if="package.bidang" variant="outline"><Icon icon="mdi:folder-outline" class="w-4 h-4 inline-block align-middle mr-1" /> {{ package.bidang }}</Badge>
                            <Badge v-if="package.level" variant="outline"><Icon icon="mdi:target" class="w-4 h-4 inline-block align-middle mr-1" /> {{ package.level }}</Badge>
                            <Badge v-if="package.kelas" variant="outline"><Icon icon="mdi:school-outline" class="w-4 h-4 inline-block align-middle mr-1" /> {{ package.kelas }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Cards List -->
                <div class="bg-card rounded-2xl border shadow-sm p-6 animate-fade-in-up" style="animation-delay: 100ms">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold"><Icon icon="mdi:clipboard-text-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Card Tugas ({{ package.cards?.length || 0 }})</h2>
                    </div>
                    <div v-if="package.cards && package.cards.length > 0" class="space-y-3">
                        <div v-for="(card, cardIdx) in package.cards" :key="card.id" class="bg-muted/50 rounded-xl p-4 hover:bg-muted transition-all duration-200 hover:shadow-sm border border-transparent hover:border-border/50" :style="{ animationDelay: cardIdx * 50 + 'ms' }">
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-sm">{{ card.title }}</p>
                                    <p v-if="card.description" class="text-xs text-muted-foreground mt-1 line-clamp-2">{{ card.description }}</p>
                                    <div class="flex items-center gap-3 mt-2 text-xs text-muted-foreground">
                                        <span><Icon icon="mdi:pencil-outline" class="w-4 h-4 inline-block align-middle mr-1" /> {{ card.question_count || 0 }} soal</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 ml-2">
                                    <Link :href="route('admin.packages.edit.cards', package.id)" class="p-1.5 rounded-lg hover:bg-background transition-all duration-200 text-muted-foreground hover:text-foreground hover:scale-110" title="Edit Card">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-muted-foreground text-sm">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-muted/50 flex items-center justify-center">
                                <Icon icon="mdi:clipboard-text-outline" class="w-7 h-7 text-muted-foreground/50" />
                            </div>
                            <p class="font-medium">Belum ada card</p>
                        </div>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="bg-card rounded-2xl border shadow-sm p-6 animate-fade-in-up" style="animation-delay: 200ms">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold"><Icon icon="mdi:help-circle-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Soal ({{ package.questions?.length || 0 }})</h2>
                    </div>
                    <div v-if="package.questions && package.questions.length > 0" class="space-y-3">
                        <div v-for="(question, idx) in package.questions" :key="question.id" class="bg-muted/50 rounded-xl p-4 hover:bg-muted/70 transition-all duration-200">
                            <div class="flex items-start gap-3">
                                <span class="text-xs font-bold text-muted-foreground bg-background rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0 mt-0.5 border">{{ idx + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm leading-relaxed" v-html="question.question"></p>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <Badge v-if="question.correct_answer" variant="outline" class="text-[10px]">Jawaban: {{ question.correct_answer }}</Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-muted-foreground text-sm">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-muted/50 flex items-center justify-center">
                                <Icon icon="mdi:help-circle-outline" class="w-7 h-7 text-muted-foreground/50" />
                            </div>
                            <p class="font-medium">Belum ada soal</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Quick Stats -->
                <div class="bg-card rounded-2xl border shadow-sm p-5 animate-fade-in-up" style="animation-delay: 100ms">
                    <h3 class="font-semibold text-sm mb-3"><Icon icon="mdi:chart-bar" class="w-5 h-5 inline-block align-middle mr-1" /> Statistik Soal</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-muted-foreground">Total Card</span><span class="font-medium">{{ package.cards?.length || 0 }}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Total Soal</span><span class="font-medium">{{ package.questions?.length || 0 }}</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Status Jadwal</span><Badge :variant="getScheduleStatus(package).variant" class="text-[10px]">{{ getScheduleStatus(package).label }}</Badge></div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="bg-card rounded-2xl border shadow-sm p-5 animate-fade-in-up" style="animation-delay: 200ms">
                    <h3 class="font-semibold text-sm mb-3"><Icon icon="mdi:calendar-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Jadwal</h3>
                    <div class="space-y-2 text-sm">
                        <div v-if="package.start_date" class="flex justify-between"><span class="text-muted-foreground">Mulai</span><span class="font-medium">{{ package.start_date }}</span></div>
                        <div v-if="package.end_date" class="flex justify-between"><span class="text-muted-foreground">Selesai</span><span class="font-medium">{{ package.end_date }}</span></div>
                        <div v-if="package.start_time" class="flex justify-between"><span class="text-muted-foreground">Jam Mulai</span><span class="font-medium">{{ package.start_time }}</span></div>
                        <div v-if="package.end_time" class="flex justify-between"><span class="text-muted-foreground">Jam Selesai</span><span class="font-medium">{{ package.end_time }}</span></div>
                        <div v-if="!package.start_date && !package.end_date" class="text-muted-foreground text-xs">Tanpa batas waktu</div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-card rounded-2xl border shadow-sm p-5 animate-fade-in-up" style="animation-delay: 300ms">
                    <h3 class="font-semibold text-sm mb-3"><Icon icon="mdi:cog-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Pengaturan Soal</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Kunci Jawaban</span>
                            <Badge :variant="package.show_answer_key ? 'success' : 'destructive'" class="text-[10px]">{{ package.show_answer_key ? 'Ditampilkan' : 'Disembunyikan' }}</Badge>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Pembahasan</span>
                            <Badge :variant="package.show_explanation ? 'success' : 'destructive'" class="text-[10px]">{{ package.show_explanation ? 'Ditampilkan' : 'Disembunyikan' }}</Badge>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Skor</span>
                            <Badge :variant="package.show_score ? 'success' : 'destructive'" class="text-[10px]">{{ package.show_score ? 'Ditampilkan' : 'Disembunyikan' }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-card rounded-2xl border shadow-sm p-5 space-y-2 animate-fade-in-up" style="animation-delay: 400ms">
                    <Link :href="route('admin.packages.edit.informasi', package.id)" class="block w-full text-center bg-primary text-primary-foreground py-2.5 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all duration-300 hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]"><Icon icon="mdi:pencil" class="w-4 h-4 inline-block align-middle mr-1" /> Edit Soal</Link>
                    <Link :href="route('admin.packages.edit.cards', package.id)" class="block w-full text-center bg-muted text-foreground py-2.5 rounded-lg text-sm font-medium hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-[0.98]"><Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Kelola Card</Link>
                    <Link :href="route('admin.packages.edit.questions', package.id)" class="block w-full text-center bg-muted text-foreground py-2.5 rounded-lg text-sm font-medium hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-[0.98]"><Icon icon="mdi:help-circle-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Kelola Soal</Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out both;
}
</style>
