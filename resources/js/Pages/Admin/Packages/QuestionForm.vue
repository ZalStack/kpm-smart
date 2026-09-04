<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
import { Icon } from '@iconify/vue';
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

        <template #header-title>{{ isEdit ? 'Edit' : 'Tambah' }} Soal</template>
        <template #header-sub>{{ package.title }}</template>

        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-200 mb-6 hover:translate-x-[-2px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Soal
        </Link>

        <div>
            <div class="bg-card rounded-2xl border shadow-sm animate-fade-in-up">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Card Selection -->
                    <div class="p-6 border-b space-y-2">
                        <div class="space-y-2">
                            <Label for="card_id">Pilih Card <span class="text-destructive">*</span></Label>
                            <Select id="card_id" v-model="form.card_id" required class="w-full transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled>-- Pilih Card Tujuan Soal --</option>
                                <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </Select>
                            <p v-if="cards.length === 0" class="text-xs text-destructive">
                                Belum ada card pada soal ini. Silakan buat card terlebih dahulu di tab Card.
                            </p>
                            <p v-if="form.errors.card_id" class="text-xs text-destructive">{{ form.errors.card_id }}</p>
                        </div>
                    </div>

                    <!-- Tipe Soal -->
                    <div class="p-6 border-b space-y-3">
                        <Label>Tipe Soal <span class="text-destructive">*</span></Label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="pilihan_ganda" v-model="form.type" class="w-4 h-4 text-primary" />
                                <span class="text-sm">Pilihan Ganda</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="isian_singkat" v-model="form.type" class="w-4 h-4 text-primary" />
                                <span class="text-sm">Isian Singkat</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question Input -->
                    <div class="p-6 border-b space-y-4">
                        <div class="flex items-center justify-between">
                            <Label for="question">Isi Pertanyaan <span class="text-destructive">*</span></Label>
                            <label class="cursor-pointer inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                <span><Icon icon="mdi:image-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Sisipkan Gambar ke Soal</span>
                                <input type="file" accept="image/*" class="hidden" @change="uploadInlineImage" />
                            </label>
                        </div>

                        <Textarea
                            id="question"
                            v-model="form.question"
                            required
                            :rows="5"
                            placeholder="Tuliskan pertanyaan di sini (mendukung format teks atau tag HTML)..."
                            class="font-sans transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20"
                        />
                        <p v-if="form.errors.question" class="text-xs text-destructive">{{ form.errors.question }}</p>

                        <!-- Existing Images Library -->
                        <div v-if="existingImages.length > 0" class="mt-4 pt-3 border-t">
                            <Label class="mb-2 block text-xs">Galeri Gambar Tersimpan (Klik untuk menyisipkan):</Label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="img in existingImages" :key="img.filename" type="button"
                                        @click="insertUploadedImage(img.url)"
                                        class="w-16 h-16 rounded-xl border overflow-hidden hover:ring-2 hover:ring-primary transition-all duration-200 cursor-pointer hover:scale-105 hover:shadow-md">
                                    <img :src="img.url" :alt="img.filename" class="w-full h-full object-cover" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="p-6 border-b">
                        <div class="space-y-2">
                            <Label>Gambar Utama Soal (Opsional)</Label>
                            <div v-if="imagePreview" class="relative w-44 h-28 rounded-xl overflow-hidden border mb-3 shadow-sm">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                                <button type="button" @click="removeImage" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow transition-all duration-200 hover:scale-110">                                <Icon icon="mdi:close" class="w-3 h-3 inline-block align-middle" /></button>
                            </div>
                            <Input type="file" accept="image/*" @change="handleImage" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Options -->
                    <div v-if="!isShortAnswer" class="p-6 border-b space-y-4">
                        <div class="flex items-center justify-between">
                            <Label>Pilihan Ganda & Kunci Jawaban <span class="text-destructive">*</span></Label>
                            <span class="text-xs text-muted-foreground">Pilih radio button untuk menandai kunci jawaban yang benar</span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(opt, index) in form.options" :key="index" class="flex items-center gap-2 group/opt">
                                <label :for="'opt_radio_' + index" class="cursor-pointer flex items-center gap-1.5 flex-shrink-0">
                                    <input
                                        :id="'opt_radio_' + index"
                                        type="radio"
                                        :value="index"
                                        v-model="form.correct_option_index"
                                        class="w-4 h-4 text-green-600 cursor-pointer"
                                    />
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                                          :class="form.correct_option_index === index ? 'bg-green-600 text-white shadow-sm' : 'bg-muted text-muted-foreground'">
                                        {{ String.fromCharCode(65 + index) }}
                                    </span>
                                </label>
                                <Input v-model="form.options[index]" :placeholder="'Pilihan ' + String.fromCharCode(65 + index)" class="flex-1 transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" required />
                                <Button type="button" variant="ghost" size="sm" @click="removeOption(index)" :disabled="form.options.length <= 2" class="text-destructive hover:text-destructive flex-shrink-0 transition-all duration-200 hover:scale-110">
                                    <Icon icon="mdi:close" class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <div>
                            <Button type="button" variant="outline" size="sm" @click="addOption" class="transition-all duration-200 hover:shadow-sm active:scale-95">
                                <Icon icon="mdi:plus" class="w-4 h-4 inline-block align-middle mr-1" /> Tambah Opsi
                            </Button>
                        </div>
                    </div>

                    <!-- Kunci Jawaban Isian Singkat -->
                    <div v-if="isShortAnswer" class="p-6 border-b space-y-4">
                        <div class="space-y-2">
                            <Label>Kunci Jawaban (Isian Singkat) <span class="text-destructive">*</span></Label>
                            <Input v-model="correct_answer_text" placeholder="Masukkan kunci jawaban yang benar..." required class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            <p class="text-xs text-muted-foreground">Jawaban akan dinilai benar jika siswa mengetik teks yang sama (tidak case-sensitive).</p>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="p-6 border-b">
                        <div class="space-y-2">
                            <Label for="explanation">Pembahasan (Opsional)</Label>
                            <Textarea id="explanation" v-model="form.explanation" :rows="3" placeholder="Tuliskan langkah penyelesaian atau penjelasan soal..." class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="p-6 flex items-center justify-end gap-3 bg-muted/20">
                        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center justify-center px-4 py-2 border rounded-lg text-sm font-medium hover:bg-muted transition-all duration-200 active:scale-95">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing || cards.length === 0" class="transition-all duration-300 hover:shadow-lg active:scale-95">
                            {{ form.processing ? 'Menyimpan...' : '' }}<Icon v-if="!form.processing" :icon="isEdit ? 'mdi:content-save' : 'mdi:plus'" class="w-4 h-4 inline-block align-middle mr-1" />{{ form.processing ? '' : (isEdit ? 'Simpan Perubahan' : 'Tambah Soal') }}
                        </Button>
                    </div>
                </form>
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
