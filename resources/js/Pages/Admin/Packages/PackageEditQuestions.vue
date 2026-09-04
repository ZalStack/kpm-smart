<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
import { Icon } from '@iconify/vue';

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

        <template #header-title>Kelola Bank Soal</template>
        <template #header-sub>{{ package.title }}</template>

        <!-- Navigation / Tabs Header -->
        <div class="space-y-4 mb-6">
            <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-200 hover:translate-x-[-2px]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali ke Daftar Soal
            </Link>

            <div class="flex flex-wrap gap-2 pt-2">
                <Link :href="route('admin.packages.edit.informasi', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Informasi
                </Link>
                <Link :href="route('admin.packages.edit.cards', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Card
                </Link>
                <Link :href="route('admin.packages.edit.questions', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground shadow-sm transition-all duration-300 hover:shadow-md hover:shadow-primary/20 active:scale-95 relative">
                    <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-white/60 rounded-full"></span>
                    <Icon icon="mdi:help-circle-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Soal
                </Link>
                <Link :href="route('admin.packages.detail', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:eye-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Detail
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4 animate-fade-in-up" style="animation-delay: 0ms">
                <p class="text-xs text-muted-foreground">Total Soal</p>
                <p class="text-2xl font-bold mt-1 text-primary">{{ totalQuestions }}</p>
            </div>
            <div class="stat-tile p-4 animate-fade-in-up" style="animation-delay: 50ms">
                <p class="text-xs text-muted-foreground">Total Card</p>
                <p class="text-2xl font-bold mt-1">{{ totalCards }}</p>
            </div>
            <div class="stat-tile p-4 animate-fade-in-up" style="animation-delay: 100ms">
                <p class="text-xs text-muted-foreground">Rata-rata/Card</p>
                <p class="text-2xl font-bold mt-1 text-green-600">
                    {{ totalCards > 0 ? (totalQuestions / totalCards).toFixed(1) : '0' }}
                </p>
            </div>
            <div class="stat-tile p-4 animate-fade-in-up" style="animation-delay: 150ms">
                <p class="text-xs text-muted-foreground">Status Soal</p>
                <p class="text-2xl font-bold mt-1" :class="package.is_active ? 'text-green-600' : 'text-red-500'">
                    {{ package.is_active ? 'Aktif' : 'Nonaktif' }}
                </p>
            </div>
        </div>

        <!-- Toolbar & Filter -->
        <div class="bg-card rounded-2xl border shadow-sm p-4 mb-6 space-y-4 animate-fade-in-up" style="animation-delay: 200ms">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-semibold text-sm flex items-center gap-2">
                    <Icon icon="mdi:pencil-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Daftar Bank Soal ({{ filteredQuestions.length }} dari {{ totalQuestions }})
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="totalCards > 0">
                        <Link :href="route('admin.packages.show-import', package.id)" class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-medium hover:bg-green-100 transition-all duration-200 hover:shadow-sm active:scale-95">
                            <Icon icon="mdi:file-document-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Import PDF
                        </Link>
                        <Link :href="route('admin.packages.create-question', package.id)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-all duration-300 shadow-sm hover:shadow-lg hover:shadow-primary/20 active:scale-95">
                            <Icon icon="mdi:plus" class="w-4 h-4 inline-block align-middle mr-1" /> Tambah Soal
                        </Link>
                    </template>
                    <div v-else class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                        <Icon icon="mdi:alert-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Buat card terlebih dahulu sebelum menambahkan soal
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t">
                <div class="sm:col-span-2 relative">
                    <Input v-model="searchQuery" placeholder="Cari isi pertanyaan atau opsi..." class="w-full transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                </div>
                <div>
                    <Select v-model="selectedCard" class="w-full transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua Card</option>
                        <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </Select>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="space-y-4">
            <div v-if="filteredQuestions.length === 0" class="bg-card rounded-2xl border shadow-sm p-12 text-center text-muted-foreground text-sm">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center">
                        <Icon icon="mdi:help-circle-outline" class="w-8 h-8 text-muted-foreground/50" />
                    </div>
                    <p class="font-medium">Tidak ada soal yang ditemukan.</p>
                    <span v-if="totalQuestions === 0 && totalCards > 0" class="text-xs text-muted-foreground">
                        Klik <strong>"Tambah Soal"</strong> atau <strong>"Import PDF"</strong> untuk menambahkan soal.
                    </span>
                </div>
            </div>

            <div v-for="(q, idx) in filteredQuestions" :key="q.id || idx" class="bg-card rounded-2xl border shadow-sm p-5 hover:shadow-md transition-all duration-300 hover:border-primary/20" :style="{ animationDelay: idx * 40 + 'ms' }">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ idx + 1 }}
                        </span>
                        <span v-if="q.type === 'isian_singkat'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-semibold">
                            <Icon icon="mdi:form-textbox" class="w-3 h-3" /> Isian Singkat
                        </span>
                        <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-semibold">
                            <Icon icon="mdi:format-list-bulleted" class="w-3 h-3" /> Pilihan Ganda
                        </span>
                        <Badge variant="outline" class="text-xs">
                            <Icon icon="mdi:folder-outline" class="w-4 h-4 inline-block align-middle mr-1" /> {{ getCardTitle(q.card_id) }}
                        </Badge>
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" @click="previewQuestion = q" class="p-1.5 rounded-lg hover:bg-muted transition-all duration-200 text-muted-foreground hover:text-foreground hover:scale-110" title="Preview">
                            <Icon icon="mdi:eye-outline" class="w-5 h-5" />
                        </button>
                        <Link :href="route('admin.packages.edit-question', [package.id, q.id])" class="p-1.5 rounded-lg hover:bg-muted transition-all duration-200 text-muted-foreground hover:text-foreground hover:scale-110" title="Edit Soal">
                            <Icon icon="mdi:pencil" class="w-5 h-5" />
                        </Link>
                        <button type="button" @click="confirmDelete(q.id)" class="p-1.5 rounded-lg hover:bg-destructive/10 transition-all duration-200 text-muted-foreground hover:text-destructive hover:scale-110" title="Hapus Soal">
                            <Icon icon="mdi:delete-outline" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="text-sm leading-relaxed mb-3" v-html="q.question"></div>

                <!-- Image if any -->
                <div v-if="q.image" class="mb-3">
                    <img :src="getImageUrl(q.image)" alt="Gambar soal" class="max-h-48 rounded-xl border object-contain transition-transform duration-300 hover:scale-105" @error="$event.target.style.display='none'" />
                </div>

                <!-- Options -->
                <div v-if="q.type === 'isian_singkat'" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-xl text-sm">
                    <span class="font-medium text-blue-800">Kunci Jawaban:</span>
                    <span class="text-blue-900 ml-1">{{ q.correct_answer }}</span>
                </div>
                <div v-else-if="q.options && q.options.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                    <div v-for="(opt, optIdx) in q.options" :key="optIdx"
                         :class="[
                             'p-2.5 rounded-lg border text-xs flex items-start gap-2 transition-all duration-200',
                             opt === q.correct_answer ? 'bg-green-50 border-green-300 text-green-800 font-medium shadow-sm' : 'bg-muted/30 text-muted-foreground hover:bg-muted/50'
                         ]">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0 transition-all duration-200"
                              :class="opt === q.correct_answer ? 'bg-green-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                            {{ String.fromCharCode(65 + optIdx) }}
                        </span>
                        <span class="flex-1 min-w-0 break-words">{{ opt }}</span>
                        <Icon v-if="opt === q.correct_answer" icon="mdi:check" class="w-3 h-3 inline-block align-middle text-green-600" />
                    </div>
                </div>

                <!-- Explanation -->
                <div v-if="q.explanation" class="mt-3 p-3 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900 leading-relaxed">
                    <Icon icon="mdi:lightbulb-on-outline" class="w-4 h-4 inline-block align-middle mr-1" /> <strong>Pembahasan:</strong> {{ q.explanation }}
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="previewQuestion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="previewQuestion = null">
            <div class="bg-card rounded-2xl shadow-2xl max-w-2xl w-full border max-h-[90vh] overflow-y-auto p-6 space-y-4 animate-fade-in-up">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-bold text-base"><Icon icon="mdi:eye-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Preview Soal</h3>
                    <button type="button" @click="previewQuestion = null" class="w-8 h-8 rounded-lg bg-muted hover:bg-muted/80 flex items-center justify-center transition-all duration-200 hover:scale-110"><Icon icon="mdi:close" class="w-4 h-4" /></button>
                </div>

                <div class="text-sm leading-relaxed" v-html="previewQuestion.question"></div>

                <div v-if="previewQuestion.image">
                    <img :src="getImageUrl(previewQuestion.image)" alt="Preview Gambar" class="max-h-64 rounded-xl border object-contain mx-auto transition-transform duration-300 hover:scale-105" />
                </div>

                <div v-if="previewQuestion.type === 'isian_singkat'" class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm">
                    <span class="font-medium text-blue-800">Kunci Jawaban:</span>
                    <span class="text-blue-900 ml-1 font-semibold">{{ previewQuestion.correct_answer }}</span>
                </div>
                <div v-else class="space-y-2">
                    <div v-for="(opt, oIdx) in previewQuestion.options" :key="oIdx"
                         :class="[
                             'p-3 rounded-xl border text-sm flex items-center gap-2 transition-all duration-200',
                             opt === previewQuestion.correct_answer ? 'bg-green-50 border-green-300 font-medium text-green-900 shadow-sm' : 'bg-muted/20 hover:bg-muted/40'
                         ]">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                              :class="opt === previewQuestion.correct_answer ? 'bg-green-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                            {{ String.fromCharCode(65 + oIdx) }}
                        </span>
                        <span>{{ opt }}</span>
                        <span v-if="opt === previewQuestion.correct_answer" class="ml-auto text-green-700 font-bold text-xs inline-flex items-center gap-1">Kunci Jawaban <Icon icon="mdi:check" class="w-3 h-3 inline-block align-middle" /></span>
                    </div>
                </div>

                <div v-if="previewQuestion.explanation" class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800">
                    <Icon icon="mdi:lightbulb-on-outline" class="w-4 h-4 inline-block align-middle mr-1" /> <strong>Pembahasan:</strong> {{ previewQuestion.explanation }}
                </div>

                <div class="flex justify-end pt-2 border-t">
                    <Button type="button" variant="outline" size="sm" @click="previewQuestion = null" class="transition-all duration-200 active:scale-95">Tutup</Button>
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
