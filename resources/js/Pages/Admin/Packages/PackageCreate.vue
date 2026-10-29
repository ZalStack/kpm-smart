<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Switch from '@/Components/ui/switch/Switch.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    initialKelas: { type: String, default: '' },
    initialBidang: { type: String, default: '' },
    initialLevel: { type: String, default: '' },
});

const form = useForm({
    title: '',
    description: '',
    kelas: props.initialKelas || '',
    bidang: props.initialBidang || '',
    level: props.initialLevel || '',
    is_active: true,
    start_date: '',
    end_date: '',
    start_time: '',
    end_time: '',
    show_answer_key: false,
    show_explanation: true,
    show_score: true,
    thumbnail: null,
});

const thumbnailPreview = ref(null);

function handleThumbnail(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        e.target.value = '';
        return;
    }
    form.thumbnail = file;
    const reader = new FileReader();
    reader.onload = (ev) => { thumbnailPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function removeThumbnail() {
    form.thumbnail = null;
    thumbnailPreview.value = null;
}

function submit() {
    form.post(route('admin.packages.store'), { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <Head title="Tambah PR Baru - Admin" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:plus-circle-outline" class="w-5 h-5" />
                </span>
                <span>Tambah PR Baru</span>
            </div>
        </template>
        <template #header-sub>
            Lengkapi detail paket PR Pekanan tugas evaluasi untuk siswa kelas khusus
        </template>

        <div class="max-w-4xl mx-auto space-y-5">
            <!-- Back navigation -->
            <Link :href="route('admin.packages.index')"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Kelola PR Pekanan
            </Link>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Basic Info -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:information-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Informasi Dasar Paket</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Judul Paket Soal <span class="text-destructive">*</span></Label>
                            <Input id="title" v-model="form.title" required placeholder="Contoh: Latihan Soal Matematika Bab 1..." class="rounded-xl" />
                            <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi Singkat <span class="text-destructive">*</span></Label>
                            <Textarea id="description" v-model="form.description" required rows="3" placeholder="Jelaskan cakupan materi atau petunjuk pengerjaan..." class="rounded-xl resize-none text-sm" />
                            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="kelas">Kelas Target</Label>
                                <Input id="kelas" v-model="form.kelas" placeholder="Contoh: 10 atau Semua" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label for="bidang">Bidang Studi</Label>
                                <Input id="bidang" v-model="form.bidang" placeholder="Contoh: Matematika, IPA" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label for="level">Tingkat / Level</Label>
                                <Input id="level" v-model="form.level" placeholder="Contoh: A1, B1, C2" class="rounded-xl" />
                            </div>
                        </div>

                        <!-- Thumbnail Dropzone -->
                        <div class="space-y-2 pt-2">
                            <Label>Gambar Cover / Thumbnail</Label>
                            <div v-if="thumbnailPreview" class="relative w-48 h-28 rounded-2xl overflow-hidden border border-border/60 shadow-sm">
                                <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                                <button type="button" @click="removeThumbnail" class="absolute top-2 right-2 w-6 h-6 bg-destructive text-white rounded-full flex items-center justify-center hover:scale-110 transition">
                                    <Icon icon="mdi:close" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            <Input v-else type="file" accept="image/*" @change="handleThumbnail" class="rounded-xl" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP (Maks. 2MB)</p>
                        </div>

                        <!-- Active Switch -->
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Status Publikasi Paket</p>
                                <p class="text-xs text-muted-foreground">Aktifkan agar paket soal dapat dilihat dan dikerjakan siswa</p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Schedule -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:calendar-clock" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Jadwal & Batas Pengerjaan (Opsional)</h3>
                    </div>

                    <p class="text-xs text-muted-foreground">Kosongkan bila paket soal dapat dikerjakan kapan saja tanpa batasan tanggal.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="start_date">Tanggal Mulai</Label>
                            <Input id="start_date" type="date" v-model="form.start_date" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label for="end_date">Tanggal Berakhir</Label>
                            <Input id="end_date" type="date" v-model="form.end_date" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label for="start_time">Jam Mulai</Label>
                            <Input id="start_time" type="time" v-model="form.start_time" class="rounded-xl" />
                        </div>
                        <div class="space-y-2">
                            <Label for="end_time">Jam Berakhir</Label>
                            <Input id="end_time" type="time" v-model="form.end_time" class="rounded-xl" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Exam Settings -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:cog-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Pengaturan Hasil & Evaluasi Siswa</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Kunci Jawaban</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat melihat kunci jawaban benar setelah tes selesai</p>
                            </div>
                            <Switch v-model="form.show_answer_key" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Pembahasan Soal</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat membaca uraian penjelasan untuk tiap butir soal</p>
                            </div>
                            <Switch v-model="form.show_explanation" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Skor Akhir</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat melihat perolehan nilai dan persentase akurasi</p>
                            </div>
                            <Switch v-model="form.show_score" />
                        </div>
                    </div>
                </div>

                <!-- Footer CTA -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link :href="route('admin.packages.index')">
                        <Button variant="ghost" type="button">Batal</Button>
                    </Link>
                    <Button type="submit" :loading="form.processing" class="gap-2 font-semibold shadow-sm min-h-11">
                        <Icon v-if="!form.processing" icon="mdi:check" class="w-4 h-4" />
                        {{ form.processing ? 'Menyimpan Paket...' : 'Simpan & Buat Soal' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
