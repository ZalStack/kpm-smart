<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
import { Icon } from '@iconify/vue/offline';
const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
    cards: { type: Array, default: () => [] },
    card: { type: Object, default: null },
    question: { type: Object, default: null },
    existingImages: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.question);

function getInitialCorrectIndex() {
    if (!props.question) return 0;
    if (props.question.correct_option_index !== undefined) return props.question.correct_option_index;
    if (props.question.options && props.question.correct_answer) {
        const idx = props.question.options.indexOf(props.question.correct_answer);
        return idx >= 0 ? idx : 0;
    }
    return 0;
}

const form = useForm({
    card_id: props.question?.card_id || props.card?.id || (props.cards[0]?.id || ''),
    question: props.question?.question || '',
    type: props.question?.type || 'pilihan_ganda',
    options: props.question?.options ? [...props.question.options] : ['', '', '', ''],
    correct_option_index: getInitialCorrectIndex(),
    explanation: props.question?.explanation || '',
    image: null,
});

const correct_answer_text = ref(props.question?.correct_answer || '');
const isShortAnswer = computed(() => form.type === 'isian_singkat');

const initialImage = props.question?.image || props.question?.image_path;
const imagePreview = ref(initialImage ? (initialImage.startsWith('http') || initialImage.startsWith('/') ? initialImage : `/storage/${initialImage}`) : null);

function handleImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        e.target.value = '';
        return;
    }
    form.image = file;
    const reader = new FileReader();
    reader.onload = (ev) => { imagePreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function removeImage() {
    form.image = null;
    imagePreview.value = null;
}

function insertUploadedImage(url) {
    form.question += `\n<img src="${url}" alt="gambar soal" class="max-w-full h-auto rounded-md my-2" />\n`;
}

async function uploadInlineImage(e) {
    const file = e.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('image', file);
    try {
        const csrfEl = document.querySelector('meta[name="csrf-token"]');
        const token = csrfEl ? csrfEl.content : '';
        const response = await fetch(route('admin.packages.upload-image', props.package.id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: formData,
        });
        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            alert(err.message || 'Upload gambar gagal. Silakan coba lagi.');
            return;
        }
        const result = await response.json();
        if (result.url) {
            insertUploadedImage(result.url);
        } else {
            alert('Upload gambar gagal. URL tidak ditemukan.');
        }
    } catch (error) {
        console.error('Upload gagal:', error);
        alert('Upload gambar gagal. Periksa koneksi internet Anda.');
    }
    e.target.value = '';
}

function addOption() {
    form.options.push('');
}

function removeOption(index) {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
        if (form.correct_option_index === index) {
            form.correct_option_index = index > 0 ? index - 1 : 0;
        } else if (form.correct_option_index > index) {
            form.correct_option_index--;
        }
    }
}

function submit() {
    if (!form.card_id) {
        alert('Pilih card tujuan soal terlebih dahulu.');
        return;
    }

    form.transform((data) => {
        const fd = new FormData();
        fd.append('card_id', data.card_id);
        fd.append('question', data.question);
        fd.append('explanation', data.explanation || '');

        if (data.image) fd.append('image', data.image);

        if (isShortAnswer.value) {
            fd.append('type', 'isian_singkat');
            fd.append('correct_answer', correct_answer_text.value);
        } else {
            fd.append('type', 'pilihan_ganda');

            const validOptions = data.options.map(o => o.trim());
            validOptions.forEach((opt, i) => {
                fd.append(`options[${i}]`, opt);
            });

            const chosenAnswer = validOptions[data.correct_option_index] !== undefined
                ? validOptions[data.correct_option_index]
                : validOptions[0] || '';
            fd.append('correct_answer', chosenAnswer);
        }

        if (isEdit.value) fd.append('_method', 'PUT');

        return fd;
    }).post(
        isEdit.value
            ? route('admin.packages.update-question', [props.package.id, props.question.id])
            : route('admin.packages.add-question', props.package.id),
        { forceFormData: true }
    );
}
</script>

<template>
    <AdminLayout>
        <Head :title="(isEdit ? 'Edit' : 'Tambah') + ' Soal - ' + package.title" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon :icon="isEdit ? 'mdi:pencil-outline' : 'mdi:plus-circle-outline'" class="w-5 h-5" />
                </span>
                <span>{{ isEdit ? 'Edit' : 'Tambah' }} Soal</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="max-w-4xl mx-auto space-y-6">
            <Link :href="route('admin.packages.edit.questions', package.id)"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Daftar Soal
            </Link>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
                <!-- Section 1: Card & Type -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:folder-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Target Card & Tipe Soal</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <Label for="card_id" class="text-xs font-semibold text-foreground">
                                Pilih Card Tujuan <span class="text-destructive">*</span>
                            </Label>
                            <Select id="card_id" v-model="form.card_id" required class="w-full h-11 rounded-xl bg-background">
                                <option value="" disabled>-- Pilih Card Tujuan --</option>
                                <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </Select>
                            <p v-if="cards.length === 0" class="text-xs text-destructive font-medium">
                                Belum ada card pada paket ini. Buat card terlebih dahulu di tab Card.
                            </p>
                            <p v-if="form.errors.card_id" class="text-xs text-destructive">{{ form.errors.card_id }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">
                                Format Tipe Soal <span class="text-destructive">*</span>
                            </Label>
                            <div class="grid grid-cols-2 gap-2">
                                <label :class="[
                                    'flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition select-none',
                                    form.type === 'pilihan_ganda' ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'bg-background border-border/60 text-muted-foreground hover:bg-muted/40'
                                ]">
                                    <input type="radio" value="pilihan_ganda" v-model="form.type" class="sr-only" />
                                    <Icon icon="mdi:format-list-bulleted" class="w-4 h-4" />
                                    <span>Pilihan Ganda</span>
                                </label>
                                <label :class="[
                                    'flex items-center justify-center gap-2 p-2.5 rounded-xl border text-xs font-bold cursor-pointer transition select-none',
                                    form.type === 'isian_singkat' ? 'bg-primary/10 border-primary text-primary shadow-sm' : 'bg-background border-border/60 text-muted-foreground hover:bg-muted/40'
                                ]">
                                    <input type="radio" value="isian_singkat" v-model="form.type" class="sr-only" />
                                    <Icon icon="mdi:form-textbox" class="w-4 h-4" />
                                    <span>Isian Singkat</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Question Content -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-border/50">
                        <div class="flex items-center gap-2">
                            <Icon icon="mdi:help-circle-outline" class="w-5 h-5 text-primary" />
                            <h3 class="font-bold text-base tracking-tight text-foreground">Isi Pertanyaan</h3>
                        </div>

                        <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 transition">
                            <Icon icon="mdi:image-plus" class="w-4 h-4" />
                            <span>Sisipkan Gambar ke Soal</span>
                            <input type="file" accept="image/*" class="hidden" @change="uploadInlineImage" />
                        </label>
                    </div>

                    <div class="space-y-2">
                        <Textarea
                            id="question"
                            v-model="form.question"
                            required
                            :rows="6"
                            placeholder="Tuliskan pertanyaan di sini (mendukung teks, LaTeX matematika $...$, atau tag HTML)..."
                            class="font-sans leading-relaxed rounded-2xl bg-background p-4 border-border/60"
                        />
                        <p v-if="form.errors.question" class="text-xs text-destructive">{{ form.errors.question }}</p>

                        <!-- Existing Images Library -->
                        <div v-if="existingImages.length > 0" class="pt-3 border-t border-border/40">
                            <Label class="mb-2 block text-xs font-semibold text-muted-foreground">Galeri Gambar Tersimpan (Klik untuk menyisipkan):</Label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="img in existingImages" :key="img.filename" type="button"
                                        @click="insertUploadedImage(img.url)"
                                        class="w-16 h-16 rounded-xl border border-border/60 overflow-hidden hover:ring-2 hover:ring-primary transition-all cursor-pointer hover:scale-105 shadow-sm bg-muted/20">
                                    <img :src="img.url" :alt="img.filename" class="w-full h-full object-cover" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Image Dropzone -->
                    <div class="pt-2 border-t border-border/40 space-y-2">
                        <Label class="text-xs font-semibold text-foreground">Gambar Utama Soal (Opsional)</Label>
                        <div v-if="imagePreview" class="relative w-48 h-32 rounded-2xl overflow-hidden border border-border/60 mb-3 shadow-sm bg-muted/20">
                            <img :src="imagePreview" class="w-full h-full object-contain p-1" />
                            <button type="button" @click="removeImage" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-destructive text-destructive-foreground text-xs flex items-center justify-center shadow transition hover:scale-110">
                                <Icon icon="mdi:close" class="w-4 h-4" />
                            </button>
                        </div>
                        <Input type="file" accept="image/*" @change="handleImage" class="h-11 rounded-xl bg-background" />
                        <p class="text-[11px] text-muted-foreground">Format yang didukung: JPG, PNG, WebP. Ukuran maksimal 2MB.</p>
                    </div>
                </div>

                <!-- Section 3: Options / Answer Key -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <!-- Pilihan Ganda -->
                    <template v-if="!isShortAnswer">
                        <div class="flex items-center justify-between pb-4 border-b border-border/50">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:format-list-checks" class="w-5 h-5 text-primary" />
                                <h3 class="font-bold text-base tracking-tight text-foreground">Pilihan Ganda & Kunci Jawaban</h3>
                            </div>
                            <span class="text-xs text-muted-foreground">Pilih radio untuk menentukan kunci jawaban</span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(opt, index) in form.options" :key="index" class="flex items-center gap-2.5">
                                <label :for="'opt_radio_' + index" class="cursor-pointer flex items-center gap-2 shrink-0">
                                    <input
                                        :id="'opt_radio_' + index"
                                        type="radio"
                                        :value="index"
                                        v-model="form.correct_option_index"
                                        class="w-4 h-4 text-emerald-600 cursor-pointer"
                                    />
                                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black transition"
                                          :class="form.correct_option_index === index ? 'bg-emerald-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                                        {{ String.fromCharCode(65 + index) }}
                                    </span>
                                </label>
                                <Input v-model="form.options[index]" :placeholder="'Teks Opsi ' + String.fromCharCode(65 + index)" class="flex-1 h-11 rounded-xl bg-background" required />
                                <Button type="button" variant="ghost" size="sm" @click="removeOption(index)" :disabled="form.options.length <= 2" class="text-destructive hover:text-destructive shrink-0 h-10 w-10 p-0 rounded-xl">
                                    <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <div class="pt-2">
                            <Button type="button" variant="outline" size="sm" @click="addOption" class="h-10 px-4 rounded-xl font-semibold gap-1.5">
                                <Icon icon="mdi:plus" class="w-4 h-4" /> Tambah Opsi
                            </Button>
                        </div>
                    </template>

                    <!-- Isian Singkat -->
                    <template v-else>
                        <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                            <Icon icon="mdi:check-circle-outline" class="w-5 h-5 text-primary" />
                            <h3 class="font-bold text-base tracking-tight text-foreground">Kunci Jawaban Isian Singkat</h3>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Kunci Jawaban <span class="text-destructive">*</span></Label>
                            <Input v-model="correct_answer_text" placeholder="Masukkan teks jawaban yang benar..." required class="h-11 rounded-xl bg-background font-semibold" />
                            <p class="text-[11px] text-muted-foreground">Jawaban akan otomatis dinilai benar jika sama (tidak sensitif huruf besar/kecil).</p>
                        </div>
                    </template>
                </div>

                <!-- Section 4: Explanation -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:lightbulb-on-outline" class="w-5 h-5 text-amber-500" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Pembahasan Soal (Opsional)</h3>
                    </div>

                    <div class="space-y-1.5">
                        <Textarea id="explanation" v-model="form.explanation" :rows="3" placeholder="Tuliskan langkah pengerjaan atau trik penyelesaian soal ini..." class="rounded-2xl bg-background p-4 border-border/60" />
                    </div>
                </div>

                <!-- Submit Action Bar -->
                <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm flex items-center justify-end gap-3">
                    <Link :href="route('admin.packages.edit.questions', package.id)" class="px-5 py-2.5 rounded-xl border border-border text-xs sm:text-sm font-semibold hover:bg-muted transition">
                        Batal
                    </Link>
                    <Button type="submit" :disabled="form.processing || cards.length === 0" class="h-11 px-6 rounded-xl font-semibold gap-2 shadow-sm">
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else :icon="isEdit ? 'mdi:content-save' : 'mdi:plus'" class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Soal') }}</span>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
