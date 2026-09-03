<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';

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
            <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali ke Daftar Soal
            </Link>

            <div class="flex flex-wrap gap-2 pt-2">
                <Link :href="route('admin.packages.edit.informasi', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
                    📝 Informasi
                </Link>
                <Link :href="route('admin.packages.edit.cards', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
                    📋 Card
                </Link>
                <Link :href="route('admin.packages.edit.questions', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground shadow-sm">
                    ❓ Soal
                </Link>
                <Link :href="route('admin.packages.detail', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
                    👁️ Detail
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Soal</p>
                <p class="text-2xl font-bold mt-1 text-primary">{{ totalQuestions }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Card</p>
                <p class="text-2xl font-bold mt-1">{{ totalCards }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Rata-rata/Card</p>
                <p class="text-2xl font-bold mt-1 text-green-600">
                    {{ totalCards > 0 ? (totalQuestions / totalCards).toFixed(1) : '0' }}
                </p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Status Soal</p>
                <p class="text-2xl font-bold mt-1" :class="package.is_active ? 'text-green-600' : 'text-red-500'">
                    {{ package.is_active ? 'Aktif' : 'Nonaktif' }}
                </p>
            </div>
        </div>

        <!-- Toolbar & Filter -->
        <div class="bg-card rounded-xl border shadow-sm p-4 mb-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="font-semibold text-sm flex items-center gap-2">
                    📝 Daftar Bank Soal ({{ filteredQuestions.length }} dari {{ totalQuestions }})
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="totalCards > 0">
                        <Link :href="route('admin.packages.show-import', package.id)" class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-md text-sm font-medium hover:bg-green-100 transition">
                            📄 Import PDF
                        </Link>
                        <Link :href="route('admin.packages.create-question', package.id)" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90 transition shadow-sm">
                            ➕ Tambah Soal
                        </Link>
                    </template>
                    <div v-else class="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-md px-3 py-2">
                        ⚠️ Buat card terlebih dahulu sebelum menambahkan soal
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t">
                <div class="sm:col-span-2 relative">
                    <Input v-model="searchQuery" placeholder="Cari isi pertanyaan atau opsi..." class="w-full" />
                </div>
                <div>
                    <Select v-model="selectedCard" class="w-full">
                        <option value="">Semua Card</option>
                        <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                    </Select>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="space-y-4">
            <div v-if="filteredQuestions.length === 0" class="bg-card rounded-xl border shadow-sm p-12 text-center text-muted-foreground text-sm">
                Tidak ada soal yang ditemukan.
                <span v-if="totalQuestions === 0 && totalCards > 0">
                    Klik <strong>"Tambah Soal"</strong> atau <strong>"Import PDF"</strong> untuk menambahkan soal.
                </span>
            </div>

            <div v-for="(q, idx) in filteredQuestions" :key="q.id || idx" class="bg-card rounded-xl border shadow-sm p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ idx + 1 }}
                        </span>
                        <Badge variant="outline" class="text-xs">
                            📂 {{ getCardTitle(q.card_id) }}
                        </Badge>
                    </div>

                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" @click="previewQuestion = q" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Preview">
                            👁️
                        </button>
                        <Link :href="route('admin.packages.edit-question', [package.id, q.id])" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Edit Soal">
                            ✏️
                        </Link>
                        <button type="button" @click="confirmDelete(q.id)" class="p-1.5 rounded-md hover:bg-destructive/10 transition text-muted-foreground hover:text-destructive" title="Hapus Soal">
                            🗑️
                        </button>
                    </div>
                </div>

                <!-- Question Text -->
                <div class="text-sm leading-relaxed mb-3" v-html="q.question"></div>

                <!-- Image if any -->
                <div v-if="q.image" class="mb-3">
                    <img :src="getImageUrl(q.image)" alt="Gambar soal" class="max-h-48 rounded-md border object-contain" @error="$event.target.style.display='none'" />
                </div>

                <!-- Options -->
                <div v-if="q.options && q.options.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                    <div v-for="(opt, optIdx) in q.options" :key="optIdx"
                         :class="[
                             'p-2.5 rounded-md border text-xs flex items-start gap-2',
                             opt === q.correct_answer ? 'bg-green-50 border-green-300 text-green-800 font-medium' : 'bg-muted/30 text-muted-foreground'
                         ]">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                              :class="opt === q.correct_answer ? 'bg-green-600 text-white' : 'bg-muted text-muted-foreground'">
                            {{ String.fromCharCode(65 + optIdx) }}
                        </span>
                        <span class="flex-1 min-w-0 break-words">{{ opt }}</span>
                        <span v-if="opt === q.correct_answer" class="text-green-600 text-xs font-bold">✓</span>
                    </div>
                </div>

                <!-- Explanation -->
                <div v-if="q.explanation" class="mt-3 p-3 bg-blue-50/70 border border-blue-100 rounded-lg text-xs text-blue-900 leading-relaxed">
                    💡 <strong>Pembahasan:</strong> {{ q.explanation }}
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="previewQuestion" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="previewQuestion = null">
            <div class="bg-card rounded-xl shadow-2xl max-w-2xl w-full border max-h-[90vh] overflow-y-auto p-6 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-bold text-base">👁️ Preview Soal</h3>
                    <button type="button" @click="previewQuestion = null" class="w-8 h-8 rounded-md bg-muted hover:bg-muted/80 flex items-center justify-center">✕</button>
                </div>

                <div class="text-sm leading-relaxed" v-html="previewQuestion.question"></div>

                <div v-if="previewQuestion.image">
                    <img :src="getImageUrl(previewQuestion.image)" alt="Preview Gambar" class="max-h-64 rounded-md border object-contain mx-auto" />
                </div>

                <div class="space-y-2">
                    <div v-for="(opt, oIdx) in previewQuestion.options" :key="oIdx"
                         :class="[
                             'p-3 rounded-lg border text-sm flex items-center gap-2',
                             opt === previewQuestion.correct_answer ? 'bg-green-50 border-green-300 font-medium text-green-900' : 'bg-muted/20'
                         ]">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                              :class="opt === previewQuestion.correct_answer ? 'bg-green-600 text-white' : 'bg-muted text-muted-foreground'">
                            {{ String.fromCharCode(65 + oIdx) }}
                        </span>
                        <span>{{ opt }}</span>
                        <span v-if="opt === previewQuestion.correct_answer" class="ml-auto text-green-700 font-bold text-xs">Kunci Jawaban ✓</span>
                    </div>
                </div>

                <div v-if="previewQuestion.explanation" class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                    💡 <strong>Pembahasan:</strong> {{ previewQuestion.explanation }}
                </div>

                <div class="flex justify-end pt-2 border-t">
                    <Button type="button" variant="outline" size="sm" @click="previewQuestion = null">Tutup</Button>
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
