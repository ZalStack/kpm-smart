<script setup>
import { inject,  ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
import Switch from '@/Components/ui/switch/Switch.vue';
const route = inject('route');

const form = useForm({
    title: '',
    description: '',
    kelas: '',
    bidang: '',
    level: '',
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

function submit() {
    form.post(route('admin.packages.store'), { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <Head title="Tambah Paket - Admin" />

        <template #header-title>Tambah Paket Baru</template>
        <template #header-sub>Buat paket tugas baru untuk pengguna</template>

        <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Paket
        </Link>

        <div>
            <div class="bg-card rounded-xl border shadow-sm">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Informasi Dasar -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold mb-4">📝 Informasi Dasar</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Judul Paket <span class="text-destructive">*</span></Label>
                                <Input v-model="form.title" required placeholder="Contoh: Paket Tugas Matematika Kelas 10" />
                                <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Deskripsi <span class="text-destructive">*</span></Label>
                                <Textarea v-model="form.description" required :rows="3" placeholder="Deskripsi singkat paket tugas..." />
                                <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label>Kelas</Label>
                                    <Input v-model="form.kelas" placeholder="Contoh: X IPA 1" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Bidang</Label>
                                    <Input v-model="form.bidang" placeholder="Contoh: Matematika" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Level</Label>
                                    <Input v-model="form.level" placeholder="Contoh: Pemula" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Thumbnail</Label>
                                <div v-if="thumbnailPreview" class="mb-3 relative w-40 h-24 rounded-lg overflow-hidden border">
                                    <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                                </div>
                                <Input type="file" accept="image/*" @change="handleThumbnail" />
                                <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <Switch v-model="form.is_active" />
                                <Label>Aktifkan paket</Label>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold mb-4">📅 Jadwal Pengerjaan</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Tanggal Mulai</Label>
                                <Input type="date" v-model="form.start_date" />
                            </div>
                            <div class="space-y-2">
                                <Label>Tanggal Berakhir</Label>
                                <Input type="date" v-model="form.end_date" />
                                <p v-if="form.errors.end_date" class="text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Jam Mulai</Label>
                                <Input type="time" v-model="form.start_time" />
                            </div>
                            <div class="space-y-2">
                                <Label>Jam Berakhir</Label>
                                <Input type="time" v-model="form.end_time" />
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Soal -->
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">⚙️ Pengaturan Soal</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div><p class="text-sm font-medium">Tampilkan Kunci Jawaban</p><p class="text-xs text-muted-foreground">User dapat melihat kunci jawaban setelah mengerjakan</p></div>
                                <Switch v-model="form.show_answer_key" />
                            </div>
                            <div class="flex items-center justify-between">
                                <div><p class="text-sm font-medium">Tampilkan Pembahasan</p><p class="text-xs text-muted-foreground">User dapat melihat pembahasan setelah mengerjakan</p></div>
                                <Switch v-model="form.show_explanation" />
                            </div>
                            <div class="flex items-center justify-between">
                                <div><p class="text-sm font-medium">Tampilkan Skor</p><p class="text-xs text-muted-foreground">User dapat melihat skor setelah mengerjakan</p></div>
                                <Switch v-model="form.show_score" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 border-t flex items-center justify-end gap-3">
                        <Link :href="route('admin.packages.index')" variant="outline">Batal</Link>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan Paket' }}</Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
