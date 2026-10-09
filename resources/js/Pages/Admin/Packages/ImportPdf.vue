<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Label from '@/Components/ui/label/Label.vue';
import Select from '@/Components/ui/select/Select.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
    cards: { type: Array, default: () => [] },
});

const selectedCard = ref(props.cards[0]?.id || '');
const pdfFile = ref(null);
const answerKeyPdf = ref(null);
const imagesZip = ref(null);
const isProcessing = ref(false);

function handlePdfChange(e) {
    const file = e.target.files[0];
    if (file && file.size > 2 * 1024 * 1024) {
        alert('Ukuran file PDF maksimal 2MB.');
        e.target.value = '';
        pdfFile.value = null;
        return;
    }
    pdfFile.value = file;
}

function handleAnswerKeyChange(e) {
    const file = e.target.files[0];
    if (file && file.size > 2 * 1024 * 1024) {
        alert('Ukuran file PDF kunci jawaban maksimal 2MB.');
        e.target.value = '';
        answerKeyPdf.value = null;
        return;
    }
    answerKeyPdf.value = file;
}

function handleZipChange(e) {
    const file = e.target.files[0];
    if (file && file.size > 20 * 1024 * 1024) {
        alert('Ukuran file ZIP gambar maksimal 20MB.');
        e.target.value = '';
        imagesZip.value = null;
        return;
    }
    imagesZip.value = file;
}

function submit() {
    if (!selectedCard.value) {
        alert('Pilih card tujuan soal terlebih dahulu.');
        return;
    }
    if (!pdfFile.value) {
        alert('Pilih file PDF soal terlebih dahulu.');
        return;
    }

    const formData = new FormData();
    formData.append('card_id', selectedCard.value);
    formData.append('pdf_file', pdfFile.value);
    if (answerKeyPdf.value) {
        formData.append('answer_key_pdf', answerKeyPdf.value);
    }
    if (imagesZip.value) {
        formData.append('images_zip', imagesZip.value);
    }

    isProcessing.value = true;
    router.post(route('admin.packages.import-pdf', props.package.id), formData, {
        forceFormData: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Import PDF - ' + package.title" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:file-pdf-box" class="w-5 h-5" />
                </span>
                <span>Import Soal dari PDF</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="max-w-4xl mx-auto space-y-6">
            <Link :href="route('admin.packages.edit.questions', package.id)"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Daftar Soal
            </Link>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Guidance Box -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:information-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Panduan Format Dokumen</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-muted-foreground">
                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <p class="font-bold text-foreground flex items-center gap-1.5">
                                <Icon icon="mdi:format-list-numbered" class="w-4 h-4 text-primary" /> Penomoran Soal
                            </p>
                            <p>Diawali nomor + titik, contoh: <code class="bg-background px-1.5 py-0.5 rounded border text-foreground font-mono">1. Hasil dari...</code></p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <p class="font-bold text-foreground flex items-center gap-1.5">
                                <Icon icon="mdi:format-list-bulleted" class="w-4 h-4 text-emerald-600" /> Pilihan Ganda
                            </p>
                            <p>Opsi di baris baru berawalan A-E, contoh: <code class="bg-background px-1.5 py-0.5 rounded border text-foreground font-mono">A. 24 cm</code></p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <p class="font-bold text-foreground flex items-center gap-1.5">
                                <Icon icon="mdi:form-textbox" class="w-4 h-4 text-blue-600" /> Isian Singkat
                            </p>
                            <p>Soal tanpa opsi akan menjadi isian singkat. Jawaban: <code class="bg-background px-1.5 py-0.5 rounded border text-foreground font-mono">Jawaban: 42</code></p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <p class="font-bold text-foreground flex items-center gap-1.5">
                                <Icon icon="mdi:image-outline" class="w-4 h-4 text-amber-600" /> Gambar & Rumus
                            </p>
                            <p>Format <code class="bg-background px-1.5 py-0.5 rounded border text-foreground font-mono">[GAMBAR:file.png]</code> dan rumus LaTeX <code class="bg-background px-1.5 py-0.5 rounded border text-foreground font-mono">$x^2$</code>.</p>
                        </div>
                    </div>
                </div>

                <!-- Form Upload Card -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:upload-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Upload Berkas</h3>
                    </div>

                    <!-- Card Selection -->
                    <div class="space-y-1.5">
                        <Label for="card_id" class="text-xs font-semibold text-foreground">
                            Pilih Card Tujuan <span class="text-destructive">*</span>
                        </Label>
                        <Select id="card_id" v-model="selectedCard" required class="w-full h-11 rounded-xl bg-background">
                            <option value="" disabled>-- Pilih Card Tujuan Soal --</option>
                            <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </Select>
                        <p v-if="cards.length === 0" class="text-xs text-destructive font-medium">
                            Paket soal ini belum memiliki Card. Buat Card terlebih dahulu di tab Card sebelum import.
                        </p>
                    </div>

                    <!-- PDF File -->
                    <div class="space-y-1.5 pt-2">
                        <Label for="pdf_file" class="text-xs font-semibold text-foreground">
                            File PDF Soal <span class="text-destructive">*</span>
                        </Label>
                        <input id="pdf_file" type="file" accept=".pdf" required @change="handlePdfChange"
                               class="flex h-11 w-full rounded-xl border border-input bg-background px-3 py-2 text-xs sm:text-sm file:border-0 file:bg-muted file:px-3 file:py-1 file:rounded-lg file:text-xs file:font-semibold file:text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring transition" />
                        <p class="text-[11px] text-muted-foreground">Format PDF dengan teks yang dapat diseleksi (bukan hasil scan gambar murni). Maksimal 2MB.</p>
                    </div>

                    <!-- Answer Key PDF -->
                    <div class="space-y-1.5 pt-2">
                        <Label for="answer_key_pdf" class="text-xs font-semibold text-foreground">
                            File PDF Kunci Jawaban (Opsional)
                        </Label>
                        <input id="answer_key_pdf" type="file" accept=".pdf" @change="handleAnswerKeyChange"
                               class="flex h-11 w-full rounded-xl border border-input bg-background px-3 py-2 text-xs sm:text-sm file:border-0 file:bg-muted file:px-3 file:py-1 file:rounded-lg file:text-xs file:font-semibold file:text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring transition" />
                        <p class="text-[11px] text-muted-foreground">Format kunci: <code class="bg-muted px-1.5 py-0.5 rounded text-[10px]">1. A</code> atau <code class="bg-muted px-1.5 py-0.5 rounded text-[10px]">1. Jawaban</code>. Maksimal 2MB.</p>
                    </div>

                    <!-- Images ZIP -->
                    <div class="space-y-1.5 pt-2">
                        <Label for="images_zip" class="text-xs font-semibold text-foreground">
                            File ZIP Gambar Pendukung (Opsional)
                        </Label>
                        <input id="images_zip" type="file" accept=".zip" @change="handleZipChange"
                               class="flex h-11 w-full rounded-xl border border-input bg-background px-3 py-2 text-xs sm:text-sm file:border-0 file:bg-muted file:px-3 file:py-1 file:rounded-lg file:text-xs file:font-semibold file:text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring transition" />
                        <p class="text-[11px] text-muted-foreground">Arsip ZIP yang memuat seluruh file gambar yang dirujuk dalam kode tag [GAMBAR:nama_file]. Maksimal 20MB.</p>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm flex items-center justify-end gap-3">
                    <Link :href="route('admin.packages.edit.questions', package.id)" class="px-5 py-2.5 rounded-xl border border-border text-xs sm:text-sm font-semibold hover:bg-muted transition">
                        Batal
                    </Link>
                    <Button type="submit" :disabled="isProcessing || cards.length === 0" class="h-11 px-6 rounded-xl font-semibold gap-2 shadow-sm">
                        <Icon v-if="isProcessing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:send" class="w-4 h-4" />
                        <span>{{ isProcessing ? 'Mengimpor & Memproses...' : 'Import & Generate Soal' }}</span>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
