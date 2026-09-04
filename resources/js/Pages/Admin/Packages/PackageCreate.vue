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
import { Icon } from '@iconify/vue';
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
        <Head title="Tambah Soal - Admin" />

        <template #header-title>Tambah Soal Baru</template>
        <template #header-sub>Buat soal tugas baru untuk pengguna</template>

        <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-200 mb-6 hover:translate-x-[-2px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Soal
        </Link>

        <div>
            <div class="bg-card rounded-2xl border shadow-sm animate-fade-in-up">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Informasi Dasar -->
                    <div class="p-6 border-b space-y-4">
                        <h2 class="text-lg font-semibold mb-4 inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg w-fit"><Icon icon="mdi:pencil-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Informasi Dasar</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Judul Soal <span class="text-destructive">*</span></Label>
                                <Input v-model="form.title" required placeholder="Contoh: Soal Tugas Matematika Kelas 10" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Deskripsi <span class="text-destructive">*</span></Label>
                                <Textarea v-model="form.description" required :rows="3" placeholder="Deskripsi singkat soal tugas..." class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label>Kelas</Label>
                                    <Input v-model="form.kelas" placeholder="Contoh: X IPA 1" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Bidang</Label>
                                    <Input v-model="form.bidang" placeholder="Contoh: Matematika" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Level</Label>
                                    <Input v-model="form.level" placeholder="Contoh: Pemula" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Thumbnail</Label>
                                <div v-if="thumbnailPreview" class="mb-3 relative w-40 h-24 rounded-xl overflow-hidden border shadow-sm">
                                    <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                                </div>
                                <Input type="file" accept="image/*" @change="handleThumbnail" />
                                <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <Switch v-model="form.is_active" />
                                <Label>Aktifkan soal</Label>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal -->
                    <div class="p-6 border-b space-y-4">
                        <h2 class="text-lg font-semibold mb-4 inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg w-fit"><Icon icon="mdi:calendar-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Jadwal Pengerjaan</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Tanggal Mulai</Label>
                                <Input type="date" v-model="form.start_date" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            </div>
                            <div class="space-y-2">
                                <Label>Tanggal Berakhir</Label>
                                <Input type="date" v-model="form.end_date" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.end_date" class="text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Jam Mulai</Label>
                                <Input type="time" v-model="form.start_time" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            </div>
                            <div class="space-y-2">
                                <Label>Jam Berakhir</Label>
                                <Input type="time" v-model="form.end_time" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Soal -->
                    <div class="p-6 space-y-4">
                        <h2 class="text-lg font-semibold mb-4 inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg w-fit"><Icon icon="mdi:cog-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Pengaturan Soal</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                                <div><p class="text-sm font-medium">Tampilkan Kunci Jawaban</p><p class="text-xs text-muted-foreground">User dapat melihat kunci jawaban setelah mengerjakan</p></div>
                                <Switch v-model="form.show_answer_key" />
                            </div>
                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                                <div><p class="text-sm font-medium">Tampilkan Pembahasan</p><p class="text-xs text-muted-foreground">User dapat melihat pembahasan setelah mengerjakan</p></div>
                                <Switch v-model="form.show_explanation" />
                            </div>
                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                                <div><p class="text-sm font-medium">Tampilkan Skor</p><p class="text-xs text-muted-foreground">User dapat melihat skor setelah mengerjakan</p></div>
                                <Switch v-model="form.show_score" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 border-t flex items-center justify-end gap-3 bg-muted/20">
                        <Link :href="route('admin.packages.index')" variant="outline" class="inline-flex items-center justify-center px-4 py-2 border rounded-lg text-sm font-medium hover:bg-muted transition-all duration-200 active:scale-95">Batal</Link>
                        <Button type="submit" :disabled="form.processing" class="transition-all duration-300 hover:shadow-lg active:scale-95">{{ form.processing ? 'Menyimpan...' : 'Simpan Soal' }}</Button>
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
