<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
    allQuestions: { type: Array, default: () => [] },
    cards: { type: Array, default: () => [] },
    questionsByCard: { type: Object, default: () => ({}) },
    totalCards: { type: Number, default: 0 },
    totalQuestions: { type: Number, default: 0 },
});

const searchQuery = ref('');
const selectedCard = ref('');
const deleteDialog = ref({ open: false, questionId: null });
const previewQuestion = ref(null);

const filteredQuestions = computed(() => {
    let result = props.allQuestions || [];

    if (selectedCard.value) {
        result = result.filter(q => q.card_id === selectedCard.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(q => {
            const inQuestion = (q.question || '').toLowerCase().includes(query);
            const inAnswer = (q.correct_answer || '').toLowerCase().includes(query);
            const inOptions = (q.options || []).some(opt => (opt || '').toLowerCase().includes(query));
            return inQuestion || inAnswer || inOptions;
        });
    }

    return result;
});

function getCardTitle(cardId) {
    const found = (props.cards || []).find(c => c.id === cardId);
    return found ? found.title : 'Umum';
}

function getImageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
        return path;
    }
    return '/storage/' + path;
}

function confirmDelete(questionId) {
    deleteDialog.value = { open: true, questionId };
}

function doDelete() {
    if (deleteDialog.value.questionId) {
        router.delete(route('admin.packages.remove-question', [props.package.id, deleteDialog.value.questionId]), {
            onSuccess: () => {
                deleteDialog.value = { open: false, questionId: null };
            },
            onError: () => {
                alert('Gagal menghapus soal. Silakan coba lagi.');
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Kelola Soal - ' + package.title" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:help-circle-outline" class="w-5 h-5" />
                </span>
                <span>Kelola Bank Soal</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Navigation Tab Pills -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <Link :href="route('admin.packages.index')"
                      class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                    <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                    Kembali ke Bank Soal
                </Link>

                <div class="flex items-center gap-1.5 p-1 bg-muted/60 rounded-xl w-fit">
                    <Link :href="route('admin.packages.edit.informasi', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5 transition">
                        <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" /> Informasi
                    </Link>
                    <Link :href="route('admin.packages.edit.cards', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5 transition">
                        <Icon icon="mdi:clipboard-text-outline" class="w-3.5 h-3.5" /> Card ({{ totalCards }})
                    </Link>
                    <Link :href="route('admin.packages.edit.questions', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-card text-foreground shadow-sm flex items-center gap-1.5">
                        <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5 text-primary" /> Soal ({{ totalQuestions }})
                    </Link>
                    <Link :href="route('admin.packages.detail', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5 transition">
                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" /> Detail
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground font-medium">Total Soal</span>
                        <Icon icon="mdi:help-circle" class="w-4 h-4 text-primary" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground mt-2">{{ totalQuestions }}</p>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground font-medium">Total Card</span>
                        <Icon icon="mdi:card-multiple-outline" class="w-4 h-4 text-blue-600" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground mt-2">{{ totalCards }}</p>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground font-medium">Rata-rata/Card</span>
                        <Icon icon="mdi:chart-timeline-variant-shimmer" class="w-4 h-4 text-emerald-600" />
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground mt-2">
                        {{ totalCards > 0 ? (totalQuestions / totalCards).toFixed(1) : '0' }}
                    </p>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground font-medium">Status Paket</span>
                        <span class="w-2 h-2 rounded-full" :class="package.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight mt-2" :class="package.is_active ? 'text-emerald-600' : 'text-red-500'">
                        {{ package.is_active ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-base tracking-tight text-foreground">
                            Bank Soal
                        </h3>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Menampilkan {{ filteredQuestions.length }} dari total {{ totalQuestions }} soal
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <template v-if="totalCards > 0">
                            <Link :href="route('admin.packages.show-import', package.id)"
                                  class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-emerald-600/30 bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 text-xs sm:text-sm font-semibold transition">
                                <Icon icon="mdi:file-pdf-box" class="w-4 h-4 text-emerald-600" />
                                <span>Import PDF</span>
                            </Link>
                            <Link :href="route('admin.packages.create-question', package.id)"
                                  class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 text-xs sm:text-sm font-semibold shadow-sm transition">
                                <Icon icon="mdi:plus" class="w-4 h-4" />
                                <span>Tambah Soal</span>
                            </Link>
                        </template>
                        <div v-else class="text-xs text-amber-700 bg-amber-500/10 border border-amber-500/20 rounded-xl px-3.5 py-2 flex items-center gap-1.5 font-medium">
                            <Icon icon="mdi:alert-circle-outline" class="w-4 h-4 text-amber-600 shrink-0" />
                            <span>Buat card terlebih dahulu sebelum menambahkan soal</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-border/50">
                    <div class="sm:col-span-2 relative">
                        <Icon icon="mdi:magnify" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                        <Input v-model="searchQuery" placeholder="Cari pertanyaan, opsi, atau kunci jawaban..." class="pl-10 h-11 rounded-xl bg-background" />
                    </div>
                    <div>
                        <Select v-model="selectedCard" class="w-full h-11 rounded-xl bg-background">
                            <option value="">Semua Card</option>
                            <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <div class="space-y-4">
                <div v-if="filteredQuestions.length === 0" class="bg-card rounded-3xl border border-border/60 p-12 text-center text-muted-foreground shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center mx-auto mb-3">
                        <Icon icon="mdi:help-circle-outline" class="w-8 h-8 text-muted-foreground/40" />
                    </div>
                    <p class="font-semibold text-foreground">Tidak ada soal yang sesuai</p>
                    <p class="text-xs text-muted-foreground mt-1">
                        {{ totalQuestions === 0 ? 'Mulai tambahkan soal manual atau import dari file PDF.' : 'Coba ubah kata kunci pencarian atau filter card.' }}
                    </p>
                </div>

                <div v-for="(q, idx) in filteredQuestions" :key="q.id || idx"
                     class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow duration-300 space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary font-black text-xs flex items-center justify-center shrink-0">
                                {{ idx + 1 }}
                            </span>
                            <span v-if="q.type === 'isian_singkat'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-700 text-xs font-bold">
                                <Icon icon="mdi:form-textbox" class="w-3.5 h-3.5" /> Isian Singkat
                            </span>
                            <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 text-xs font-bold">
                                <Icon icon="mdi:format-list-bulleted" class="w-3.5 h-3.5" /> Pilihan Ganda
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-muted-foreground bg-muted/60 px-2.5 py-1 rounded-full">
                                <Icon icon="mdi:clipboard-text-outline" class="w-3.5 h-3.5 text-primary" /> {{ getCardTitle(q.card_id) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" @click="previewQuestion = q" class="p-2 rounded-xl hover:bg-muted text-muted-foreground hover:text-foreground transition" title="Preview">
                                <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                            </button>
                            <Link :href="route('admin.packages.edit-question', [package.id, q.id])" class="p-2 rounded-xl hover:bg-muted text-muted-foreground hover:text-foreground transition" title="Edit Soal">
                                <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                            </Link>
                            <button type="button" @click="confirmDelete(q.id)" class="p-2 rounded-xl hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition" title="Hapus Soal">
                                <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Question Content -->
                    <div class="text-sm leading-relaxed text-foreground font-medium" v-html="q.question"></div>

                    <!-- Question Image -->
                    <div v-if="q.image" class="pt-1">
                        <img :src="getImageUrl(q.image)" alt="Gambar soal" class="max-h-56 rounded-2xl border border-border/60 object-contain bg-muted/20 p-1" @error="$event.target.style.display='none'" />
                    </div>

                    <!-- Options / Answer Key -->
                    <div v-if="q.type === 'isian_singkat'" class="p-3.5 bg-blue-500/10 border border-blue-500/20 rounded-2xl text-xs sm:text-sm">
                        <span class="font-bold text-blue-800">Kunci Jawaban:</span>
                        <span class="text-blue-950 font-semibold ml-1.5">{{ q.correct_answer }}</span>
                    </div>
                    <div v-else-if="q.options && q.options.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        <div v-for="(opt, optIdx) in q.options" :key="optIdx"
                             :class="[
                                 'p-3 rounded-2xl border text-xs sm:text-sm flex items-start gap-2.5 transition',
                                 opt === q.correct_answer ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-950 font-semibold' : 'bg-muted/30 border-border/50 text-foreground'
                             ]">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition"
                                  :class="opt === q.correct_answer ? 'bg-emerald-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                                {{ String.fromCharCode(65 + optIdx) }}
                            </span>
                            <span class="flex-1 min-w-0 break-words leading-relaxed" v-html="opt"></span>
                            <Icon v-if="opt === q.correct_answer" icon="mdi:check-circle" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div v-if="q.explanation" class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-xs text-amber-950 leading-relaxed flex items-start gap-2">
                        <Icon icon="mdi:lightbulb-on-outline" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                        <div>
                            <span class="font-bold">Pembahasan:</span>
                            <span class="ml-1" v-html="q.explanation"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="previewQuestion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="previewQuestion = null">
            <div class="bg-card rounded-3xl shadow-2xl max-w-2xl w-full border border-border/60 max-h-[90vh] overflow-y-auto p-6 sm:p-7 space-y-5">
                <div class="flex items-center justify-between border-b border-border/50 pb-4">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:eye-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Preview Soal</h3>
                    </div>
                    <button type="button" @click="previewQuestion = null" class="w-8 h-8 rounded-xl bg-muted hover:bg-muted/80 flex items-center justify-center transition">
                        <Icon icon="mdi:close" class="w-4 h-4" />
                    </button>
                </div>

                <div class="text-sm sm:text-base leading-relaxed text-foreground font-medium" v-html="previewQuestion.question"></div>

                <div v-if="previewQuestion.image">
                    <img :src="getImageUrl(previewQuestion.image)" alt="Preview Gambar" class="max-h-64 rounded-2xl border border-border/60 object-contain mx-auto bg-muted/20 p-1" />
                </div>

                <div v-if="previewQuestion.type === 'isian_singkat'" class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl text-sm">
                    <span class="font-bold text-blue-800">Kunci Jawaban:</span>
                    <span class="text-blue-950 ml-1.5 font-bold">{{ previewQuestion.correct_answer }}</span>
                </div>
                <div v-else class="space-y-2.5">
                    <div v-for="(opt, oIdx) in previewQuestion.options" :key="oIdx"
                         :class="[
                             'p-3.5 rounded-2xl border text-sm flex items-center gap-3 transition',
                             opt === previewQuestion.correct_answer ? 'bg-emerald-500/10 border-emerald-500/30 font-semibold text-emerald-950' : 'bg-muted/30 border-border/50 text-foreground'
                         ]">
                        <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black shrink-0"
                              :class="opt === previewQuestion.correct_answer ? 'bg-emerald-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                            {{ String.fromCharCode(65 + oIdx) }}
                        </span>
                        <span class="flex-1 break-words" v-html="opt"></span>
                        <span v-if="opt === previewQuestion.correct_answer" class="text-emerald-700 font-bold text-xs inline-flex items-center gap-1 shrink-0">
                            Kunci Jawaban <Icon icon="mdi:check-circle" class="w-4 h-4" />
                        </span>
                    </div>
                </div>

                <div v-if="previewQuestion.explanation" class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-xs text-amber-950 flex items-start gap-2">
                    <Icon icon="mdi:lightbulb-on-outline" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
                    <div>
                        <span class="font-bold">Pembahasan:</span>
                        <span class="ml-1" v-html="previewQuestion.explanation"></span>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-border/50">
                    <Button type="button" variant="outline" @click="previewQuestion = null" class="h-10 px-5 rounded-xl font-semibold">Tutup</Button>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="deleteDialog.open"
            title="Hapus Soal"
            message="Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan."
            confirm-text="Ya, Hapus"
            @update:open="deleteDialog.open = $event"
            @confirm="doDelete"
        />
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
