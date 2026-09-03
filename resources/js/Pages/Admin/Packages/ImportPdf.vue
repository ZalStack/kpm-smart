<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Label from '@/Components/ui/label/Label.vue';
import Select from '@/Components/ui/select/Select.vue';

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

        <template #header-title>Import Soal dari PDF</template>
        <template #header-sub>{{ package.title }}</template>

        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Soal
        </Link>

        <div>
            <div class="bg-card rounded-xl border shadow-sm overflow-hidden">
                <!-- Info Header -->
                <div class="p-5 sm:p-6 bg-gradient-to-r from-green-600 to-green-700 text-white">
                    <h2 class="font-bold text-base sm:text-lg flex items-center gap-2">
                        📄 Import Soal dari PDF
                    </h2>
                    <p class="text-xs text-white/80 mt-1">Upload file PDF untuk menambahkan soal secara otomatis</p>
                </div>

                <!-- Format Information -->
                <div class="m-5 sm:m-6 bg-primary/5 border border-primary/20 rounded-lg p-4">
                    <p class="font-semibold text-sm mb-2 flex items-center gap-2 text-primary">
                        ℹ️ Format PDF yang Didukung
                    </p>
                    <ul class="space-y-1.5 text-xs text-muted-foreground leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 mt-0.5">✓</span>
                            <span>Setiap soal diawali nomor + titik, contoh: <code class="bg-muted px-1 py-0.5 rounded text-[11px]">1. Ibu kota Indonesia adalah...</code></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 mt-0.5">✓</span>
                            <span>Pilihan jawaban di baris terpisah diawali huruf A-E + titik (contoh: <code class="bg-muted px-1 py-0.5 rounded text-[11px]">A. Jakarta</code>)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-600 mt-0.5">✓</span>
                            <span>Gambar dalam PDF <strong>otomatis diekstrak</strong> dan dikaitkan ke nomor soal yang bersesuaian</span>
                        </li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="p-5 sm:p-6 pt-0 space-y-5">
                    <!-- Card Selection -->
                    <div class="space-y-2">
                        <Label for="card_id">Pilih Card Tujuan <span class="text-destructive">*</span></Label>
                        <Select id="card_id" v-model="selectedCard" required class="w-full">
                            <option value="" disabled>-- Pilih Card --</option>
                            <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                        </Select>
                        <p v-if="cards.length === 0" class="text-xs text-destructive">
                            ⚠️ Paket ini belum memiliki Card. Silakan buat Card terlebih dahulu di tab Card sebelum import soal.
                        </p>
                    </div>

                    <!-- PDF File -->
                    <div class="space-y-2">
                        <Label for="pdf_file">File PDF Soal <span class="text-destructive">*</span></Label>
                        <input id="pdf_file" type="file" accept=".pdf" required @change="handlePdfChange"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                        <p class="text-xs text-muted-foreground">Format: PDF teks berurutan. Maks 2MB.</p>
                    </div>

                    <!-- Answer Key PDF -->
                    <div class="space-y-2">
                        <Label for="answer_key_pdf">File PDF Kunci Jawaban (Opsional)</Label>
                        <input id="answer_key_pdf" type="file" accept=".pdf" @change="handleAnswerKeyChange"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                        <p class="text-xs text-muted-foreground">Format: PDF berisi kunci jawaban nomor dan pilihan A-E. Maks 2MB.</p>
                    </div>

                    <!-- Images ZIP -->
                    <div class="space-y-2">
                        <Label for="images_zip">File ZIP Gambar Soal (Opsional)</Label>
                        <input id="images_zip" type="file" accept=".zip" @change="handleZipChange"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                        <p class="text-xs text-muted-foreground">Gunakan jika ingin menyertakan kumpulan gambar dengan format [GAMBAR:nama_file]. Maks 20MB.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center justify-center px-4 py-2 border rounded-md text-sm font-medium hover:bg-muted transition">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="isProcessing || cards.length === 0">
                            {{ isProcessing ? 'Mengimpor & Memproses...' : '📤 Import & Generate Soal' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
