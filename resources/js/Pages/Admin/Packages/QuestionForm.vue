<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
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
    type: 'pilihan_ganda',
    options: props.question?.options ? [...props.question.options] : ['', '', '', ''],
    correct_option_index: getInitialCorrectIndex(),
    explanation: props.question?.explanation || '',
    image: null,
});

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
        const result = await response.json();
        if (result.url) {
            insertUploadedImage(result.url);
        }
    } catch (error) {
        console.error('Upload gagal:', error);
    }
    e.target.value = '';
}

function addOption() {
    form.options.push('');
}

function removeOption(index) {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
        if (form.correct_option_index >= index && form.correct_option_index > 0) {
            form.correct_option_index--;
        }
    }
}

function submit() {
    if (!form.card_id) {
        alert('Pilih card tujuan soal terlebih dahulu.');
        return;
    }

    const data = new FormData();
    data.append('card_id', form.card_id);
    data.append('question', form.question);
    data.append('explanation', form.explanation || '');
    if (form.image) data.append('image', form.image);

    const validOptions = form.options.map(o => o.trim());
    validOptions.forEach((opt, i) => {
        data.append(`options[${i}]`, opt);
    });

    const chosenAnswer = validOptions[form.correct_option_index] !== undefined
        ? validOptions[form.correct_option_index]
        : validOptions[0] || '';
    data.append('correct_answer', chosenAnswer);

    if (isEdit.value) {
        data.append('_method', 'PUT');
        router.post(route('admin.packages.update-question', [props.package.id, props.question.id]), data, {
            forceFormData: true,
        });
    } else {
        router.post(route('admin.packages.add-question', props.package.id), data, {
            forceFormData: true,
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="(isEdit ? 'Edit' : 'Tambah') + ' Soal - ' + package.title" />

        <template #header-title>{{ isEdit ? 'Edit' : 'Tambah' }} Soal</template>
        <template #header-sub>{{ package.title }}</template>

        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Soal
        </Link>

        <div>
            <div class="bg-card rounded-xl border shadow-sm">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Card Selection -->
                    <div class="p-6 border-b">
                        <div class="space-y-2">
                            <Label for="card_id">Pilih Card <span class="text-destructive">*</span></Label>
                            <Select id="card_id" v-model="form.card_id" required class="w-full">
                                <option value="" disabled>-- Pilih Card Tujuan Soal --</option>
                                <option v-for="c in cards" :key="c.id" :value="c.id">{{ c.title }}</option>
                            </Select>
                            <p v-if="cards.length === 0" class="text-xs text-destructive">
                                Belum ada card pada paket ini. Silakan buat card terlebih dahulu di tab Card.
                            </p>
                            <p v-if="form.errors.card_id" class="text-xs text-destructive">{{ form.errors.card_id }}</p>
                        </div>
                    </div>

                    <!-- Question Input -->
                    <div class="p-6 border-b space-y-4">
                        <div class="flex items-center justify-between">
                            <Label for="question">Isi Pertanyaan <span class="text-destructive">*</span></Label>
                            <label class="cursor-pointer inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                <span>🖼️ Sisipkan Gambar ke Soal</span>
                                <input type="file" accept="image/*" class="hidden" @change="uploadInlineImage" />
                            </label>
                        </div>

                        <Textarea
                            id="question"
                            v-model="form.question"
                            required
                            :rows="5"
                            placeholder="Tuliskan pertanyaan di sini (mendukung format teks atau tag HTML)..."
                            class="font-sans"
                        />
                        <p v-if="form.errors.question" class="text-xs text-destructive">{{ form.errors.question }}</p>

                        <!-- Existing Images Library -->
                        <div v-if="existingImages.length > 0" class="mt-4 pt-3 border-t">
                            <Label class="mb-2 block text-xs">Galeri Gambar Tersimpan (Klik untuk menyisipkan):</Label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="img in existingImages" :key="img.filename" type="button"
                                        @click="insertUploadedImage(img.url)"
                                        class="w-16 h-16 rounded-lg border overflow-hidden hover:ring-2 hover:ring-primary transition cursor-pointer">
                                    <img :src="img.url" :alt="img.filename" class="w-full h-full object-cover" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="p-6 border-b">
                        <div class="space-y-2">
                            <Label>Gambar Utama Soal (Opsional)</Label>
                            <div v-if="imagePreview" class="relative w-44 h-28 rounded-lg overflow-hidden border mb-3">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                                <button type="button" @click="removeImage" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow">✕</button>
                            </div>
                            <Input type="file" accept="image/*" @change="handleImage" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="p-6 border-b space-y-4">
                        <div class="flex items-center justify-between">
                            <Label>Pilihan Ganda & Kunci Jawaban <span class="text-destructive">*</span></Label>
                            <span class="text-xs text-muted-foreground">Pilih radio button untuk menandai kunci jawaban yang benar</span>
                        </div>

                        <div class="space-y-3">
                            <div v-for="(opt, index) in form.options" :key="index" class="flex items-center gap-2">
                                <label :for="'opt_radio_' + index" class="cursor-pointer flex items-center gap-1.5 flex-shrink-0">
                                    <input
                                        :id="'opt_radio_' + index"
                                        type="radio"
                                        :value="index"
                                        v-model="form.correct_option_index"
                                        class="w-4 h-4 text-green-600 cursor-pointer"
                                    />
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                          :class="form.correct_option_index === index ? 'bg-green-600 text-white' : 'bg-muted text-muted-foreground'">
                                        {{ String.fromCharCode(65 + index) }}
                                    </span>
                                </label>
                                <Input v-model="form.options[index]" :placeholder="'Pilihan ' + String.fromCharCode(65 + index)" class="flex-1" required />
                                <Button type="button" variant="ghost" size="sm" @click="removeOption(index)" :disabled="form.options.length <= 2" class="text-destructive hover:text-destructive flex-shrink-0">
                                    ✕
                                </Button>
                            </div>
                        </div>

                        <div>
                            <Button type="button" variant="outline" size="sm" @click="addOption">
                                ➕ Tambah Opsi
                            </Button>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="p-6 border-b">
                        <div class="space-y-2">
                            <Label for="explanation">Pembahasan (Opsional)</Label>
                            <Textarea id="explanation" v-model="form.explanation" :rows="3" placeholder="Tuliskan langkah penyelesaian atau penjelasan soal..." />
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="p-6 flex items-center justify-end gap-3 bg-muted/20">
                        <Link :href="route('admin.packages.edit.questions', package.id)" class="inline-flex items-center justify-center px-4 py-2 border rounded-md text-sm font-medium hover:bg-muted transition">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing || cards.length === 0">
                            {{ form.processing ? 'Menyimpan...' : (isEdit ? '💾 Simpan Perubahan' : '➕ Tambah Soal') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
