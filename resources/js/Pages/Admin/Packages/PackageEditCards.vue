<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
    questionsByCard: { type: Object, default: () => ({}) },
});

const form = useForm({
    card_title: '',
    card_description: '',
});

const deleteDialog = ref({ open: false, card: null });

const deleteDialogMessage = computed(() =>
    `Apakah Anda yakin ingin menghapus card '${deleteDialog.value.card?.title || ''}' beserta semua soal di dalamnya? Tindakan ini tidak dapat dibatalkan.`
);

function submitAddCard() {
    form.post(route('admin.packages.add-card', props.package.id), {
        onSuccess: () => {
            form.reset();
        },
    });
}

function confirmDelete(card) {
    deleteDialog.value = { open: true, card };
}

function doDelete() {
    if (deleteDialog.value.card) {
        router.delete(route('admin.packages.remove-card', [props.package.id, deleteDialog.value.card.id]), {
            onSuccess: () => {
                deleteDialog.value = { open: false, card: null };
            },
            onError: () => {
                alert('Gagal menghapus card. Silakan coba lagi.');
            },
        });
    }
}

const totalCards = computed(() => props.package.cards?.length || 0);
const totalQuestions = computed(() => props.package.questions?.length || 0);
const avgQuestions = computed(() => totalCards.value > 0 ? (totalQuestions.value / totalCards.value).toFixed(1) : '0');

function getCardQuestionCount(cardId) {
    if (props.questionsByCard && props.questionsByCard[cardId]) {
        return props.questionsByCard[cardId].length;
    }
    return (props.package.questions || []).filter(q => q.card_id === cardId).length;
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Kelola Card - ' + package.title" />

        <template #header-title>Kelola Card</template>
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
                <Link :href="route('admin.packages.edit.cards', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground shadow-sm">
                    📋 Card
                </Link>
                <Link :href="route('admin.packages.edit.questions', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
                    ❓ Soal
                </Link>
                <Link :href="route('admin.packages.detail', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
                    👁️ Detail
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Card</p>
                <p class="text-2xl font-bold mt-1">{{ totalCards }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Soal</p>
                <p class="text-2xl font-bold mt-1 text-primary">{{ totalQuestions }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Rata-rata Soal / Card</p>
                <p class="text-2xl font-bold mt-1 text-green-600">{{ avgQuestions }}</p>
            </div>
        </div>

        <!-- Add Card Form -->
        <div class="bg-card rounded-xl border shadow-sm p-5 mb-6">
            <h3 class="font-semibold text-base mb-3 flex items-center gap-2">
                ➕ Tambah Card Baru
            </h3>
            <form @submit.prevent="submitAddCard" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="card_title">Judul Card <span class="text-destructive">*</span></Label>
                        <Input id="card_title" v-model="form.card_title" required placeholder="Contoh: Bab 1: Aljabar Dasar" />
                        <p v-if="form.errors.card_title" class="text-xs text-destructive">{{ form.errors.card_title }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="card_description">Deskripsi <span class="text-destructive">*</span></Label>
                        <Input id="card_description" v-model="form.card_description" required placeholder="Deskripsi singkat topik materi card" />
                        <p v-if="form.errors.card_description" class="text-xs text-destructive">{{ form.errors.card_description }}</p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menambahkan...' : '➕ Tambah Card' }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Cards List -->
        <div class="bg-card rounded-xl border shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-muted/30 flex items-center justify-between">
                <h3 class="font-semibold text-sm">📋 Daftar Card ({{ totalCards }})</h3>
            </div>

            <div v-if="!package.cards || package.cards.length === 0" class="p-8 text-center text-muted-foreground text-sm">
                Belum ada card pada soal ini. Gunakan form di atas untuk membuat card pertama.
            </div>

            <div v-else class="divide-y">
                <div v-for="card in package.cards" :key="card.id" class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-muted/40 transition">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-base truncate">{{ card.title }}</h4>
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                                {{ getCardQuestionCount(card.id) }} soal
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground line-clamp-2">{{ card.description }}</p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-muted hover:bg-muted/80 text-xs font-medium transition">
                            ❓ Lihat Soal
                        </Link>
                        <button type="button" @click="confirmDelete(card)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-destructive/10 hover:bg-destructive/20 text-destructive text-xs font-medium transition">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="deleteDialog.open"
            title="Hapus Card"
            :message="deleteDialogMessage"
            confirm-text="Ya, Hapus"
            @update:open="deleteDialog.open = $event"
            @confirm="doDelete"
        />
    </AdminLayout>
</template>
