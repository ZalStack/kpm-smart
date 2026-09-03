<script setup>
import { inject, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Switch from '@/Components/ui/switch/Switch.vue';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
});

const form = useForm({
    title: props.package.title || '',
    description: props.package.description || '',
    kelas: props.package.kelas || '',
    bidang: props.package.bidang || '',
    level: props.package.level || '',
    is_active: Boolean(props.package.is_active),
    start_date: props.package.start_date || '',
    end_date: props.package.end_date || '',
    start_time: props.package.start_time ? props.package.start_time.substring(0, 5) : '',
    end_time: props.package.end_time ? props.package.end_time.substring(0, 5) : '',
    show_answer_key: Boolean(props.package.show_answer_key),
    show_explanation: props.package.show_explanation !== undefined ? Boolean(props.package.show_explanation) : true,
    show_score: props.package.show_score !== undefined ? Boolean(props.package.show_score) : true,
    thumbnail: null,
});

const thumbnailPreview = ref(props.package.thumbnail ? '/storage/' + props.package.thumbnail : null);

function handleThumbnail(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran thumbnail terlalu besar (maks 2MB).');
        e.target.value = '';
        return;
    }
    form.thumbnail = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
        thumbnailPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
}

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('admin.packages.update', props.package.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Edit Informasi - ' + package.title" />

        <template #header-title>Edit Paket</template>
        <template #header-sub>{{ package.title }}</template>

        <!-- Navigation / Tabs Header -->
        <div class="space-y-4 mb-6">
            <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali ke Daftar Paket
            </Link>

            <div class="flex flex-wrap gap-2 pt-2">
                <Link :href="route('admin.packages.edit.informasi', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground shadow-sm">
                    📝 Informasi
                </Link>
                <Link :href="route('admin.packages.edit.cards', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition">
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

        <div class="max-w-4xl">
            <div class="bg-card rounded-xl border shadow-sm overflow-hidden">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Informasi Dasar -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold flex items-center gap-2">
                            📝 Informasi Dasar
                        </h3>

                        <div class="space-y-2">
                            <Label for="title">Judul Paket <span class="text-destructive">*</span></Label>
                            <Input id="title" v-model="form.title" required placeholder="Contoh: Latihan Soal Matematika" />
                            <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi <span class="text-destructive">*</span></Label>
                            <Textarea id="description" v-model="form.description" required :rows="3" placeholder="Deskripsi mengenai paket soal..." />
                            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="kelas">Kelas</Label>
                                <Input id="kelas" v-model="form.kelas" placeholder="Contoh: Kelas 10" />
                                <p v-if="form.errors.kelas" class="text-xs text-destructive">{{ form.errors.kelas }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="bidang">Bidang</Label>
                                <Input id="bidang" v-model="form.bidang" placeholder="Contoh: Matematika" />
                                <p v-if="form.errors.bidang" class="text-xs text-destructive">{{ form.errors.bidang }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="level">Level</Label>
                                <Input id="level" v-model="form.level" placeholder="Contoh: Pemula" />
                                <p v-if="form.errors.level" class="text-xs text-destructive">{{ form.errors.level }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Thumbnail Paket</Label>
                            <div v-if="thumbnailPreview" class="mb-3 relative w-44 h-28 rounded-lg overflow-hidden border">
                                <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                            </div>
                            <Input type="file" accept="image/*" @change="handleThumbnail" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB. Kosongkan bila tidak diubah.</p>
                            <p v-if="form.errors.thumbnail" class="text-xs text-destructive">{{ form.errors.thumbnail }}</p>
                        </div>

                        <div class="pt-2 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">Status Paket</p>
                                <p class="text-xs text-muted-foreground">Aktifkan agar paket dapat diakses oleh siswa</p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>
                    </div>

                    <!-- Jadwal Pengerjaan -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold flex items-center gap-2">
                            📅 Jadwal Pengerjaan (Opsional)
                        </h3>
                        <p class="text-xs text-muted-foreground">Kosongkan bila paket dapat dikerjakan kapan saja tanpa batasan tanggal/jam.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date">Tanggal Mulai</Label>
                                <Input id="start_date" v-model="form.start_date" type="date" />
                                <p v-if="form.errors.start_date" class="text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_date">Tanggal Berakhir</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" />
                                <p v-if="form.errors.end_date" class="text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_time">Jam Mulai</Label>
                                <Input id="start_time" v-model="form.start_time" type="time" />
                                <p v-if="form.errors.start_time" class="text-xs text-destructive">{{ form.errors.start_time }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_time">Jam Berakhir</Label>
                                <Input id="end_time" v-model="form.end_time" type="time" />
                                <p v-if="form.errors.end_time" class="text-xs text-destructive">{{ form.errors.end_time }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Soal -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold flex items-center gap-2">
                            ⚙️ Pengaturan Soal
                        </h3>
                        <p class="text-xs text-muted-foreground">Atur visibilitas kunci jawaban, pembahasan, dan skor untuk siswa setelah mengerjakan.</p>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 bg-muted/40 rounded-lg border">
                                <div>
                                    <p class="text-sm font-medium">Tampilkan Kunci Jawaban</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Siswa dapat melihat jawaban yang benar setelah selesai</p>
                                </div>
                                <Switch v-model="form.show_answer_key" />
                            </div>

                            <div class="flex items-center justify-between p-4 bg-muted/40 rounded-lg border">
                                <div>
                                    <p class="text-sm font-medium">Tampilkan Pembahasan</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Siswa dapat melihat penjelasan/pembahasan soal setelah selesai</p>
                                </div>
                                <Switch v-model="form.show_explanation" />
                            </div>

                            <div class="flex items-center justify-between p-4 bg-muted/40 rounded-lg border">
                                <div>
                                    <p class="text-sm font-medium">Tampilkan Skor / Nilai</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Siswa dapat melihat nilai akhir setelah selesai</p>
                                </div>
                                <Switch v-model="form.show_score" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Submit -->
                    <div class="p-6 flex items-center justify-end gap-3 bg-muted/20">
                        <Link :href="route('admin.packages.index')" class="inline-flex items-center justify-center px-4 py-2 border rounded-md text-sm font-medium hover:bg-muted transition">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : '💾 Simpan Perubahan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
