<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
import { Icon } from '@iconify/vue/offline';

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

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:clipboard-text-outline" class="w-5 h-5" />
                </span>
                <span>Kelola Card</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="max-w-5xl mx-auto space-y-6">
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
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-card text-foreground shadow-sm flex items-center gap-1.5">
                        <Icon icon="mdi:clipboard-text-outline" class="w-3.5 h-3.5 text-primary" /> Card ({{ totalCards }})
                    </Link>
                    <Link :href="route('admin.packages.edit.questions', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5 transition">
                        <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5" /> Soal ({{ totalQuestions }})
                    </Link>
                    <Link :href="route('admin.packages.detail', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5 transition">
                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" /> Detail
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <Icon icon="mdi:card-multiple-outline" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Total Card</p>
                        <p class="text-2xl font-black tracking-tight text-foreground">{{ totalCards }}</p>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                        <Icon icon="mdi:help-circle-outline" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Total Soal</p>
                        <p class="text-2xl font-black tracking-tight text-foreground">{{ totalQuestions }}</p>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                        <Icon icon="mdi:chart-timeline-variant-shimmer" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Rata-rata Soal/Card</p>
                        <p class="text-2xl font-black tracking-tight text-foreground">{{ avgQuestions }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Card Form Card -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                    <Icon icon="mdi:plus-circle-outline" class="w-5 h-5 text-primary" />
                    <h3 class="font-bold text-base tracking-tight">Tambah Card Baru</h3>
                </div>

                <form @submit.prevent="submitAddCard" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="card_title" class="text-xs font-semibold text-foreground">
                                Judul Card <span class="text-destructive">*</span>
                            </Label>
                            <Input id="card_title" v-model="form.card_title" required placeholder="Contoh: Bab 1: Aljabar & Geometri" class="h-11 rounded-xl bg-background" />
                            <p v-if="form.errors.card_title" class="text-xs text-destructive">{{ form.errors.card_title }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="card_description" class="text-xs font-semibold text-foreground">
                                Deskripsi <span class="text-destructive">*</span>
                            </Label>
                            <Input id="card_description" v-model="form.card_description" required placeholder="Deskripsi materi atau petunjuk pengerjaan..." class="h-11 rounded-xl bg-background" />
                            <p v-if="form.errors.card_description" class="text-xs text-destructive">{{ form.errors.card_description }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <Button type="submit" :disabled="form.processing" class="h-11 px-6 rounded-xl font-semibold gap-2 shadow-sm">
                            <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <Icon v-else icon="mdi:plus" class="w-4 h-4" />
                            <span>{{ form.processing ? 'Menambahkan...' : 'Tambah Card' }}</span>
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Cards List -->
            <div class="bg-card rounded-3xl border border-border/60 overflow-hidden shadow-sm">
                <div class="p-5 sm:p-6 border-b border-border/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-numbered" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight">Daftar Card</h3>
                    </div>
                    <span class="text-xs font-semibold text-muted-foreground bg-muted/60 px-3 py-1 rounded-full">
                        {{ totalCards }} Card
                    </span>
                </div>

                <div v-if="!package.cards || package.cards.length === 0" class="p-12 text-center text-muted-foreground">
                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center mx-auto mb-3">
                        <Icon icon="mdi:clipboard-text-outline" class="w-8 h-8 text-muted-foreground/40" />
                    </div>
                    <p class="font-semibold text-foreground">Belum ada card pada paket ini</p>
                    <p class="text-xs text-muted-foreground mt-1">Gunakan formulir di atas untuk membuat card pertama.</p>
                </div>

                <div v-else class="divide-y divide-border/50">
                    <div v-for="(card, index) in package.cards" :key="card.id"
                         class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-muted/30 transition-colors">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary font-black text-sm flex items-center justify-center shrink-0">
                                {{ index + 1 }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap">
                                    <h4 class="font-bold text-sm sm:text-base text-foreground truncate">{{ card.title }}</h4>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary">
                                        <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5" />
                                        {{ getCardQuestionCount(card.id) }} Soal
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1 line-clamp-2 leading-relaxed">{{ card.description }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <Link :href="route('admin.packages.edit.questions', package.id)"
                                  class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-muted hover:bg-muted/80 text-xs font-semibold text-foreground transition">
                                <Icon icon="mdi:format-list-bulleted" class="w-4 h-4 text-primary" /> Kelola Soal
                            </Link>
                            <button type="button" @click="confirmDelete(card)"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-destructive/10 hover:bg-destructive/20 text-xs font-semibold text-destructive transition">
                                <Icon icon="mdi:trash-can-outline" class="w-4 h-4" /> Hapus
                            </button>
                        </div>
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
