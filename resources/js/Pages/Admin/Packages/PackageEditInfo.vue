<script setup>
import { inject, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Switch from '@/Components/ui/switch/Switch.vue';
import { Icon } from '@iconify/vue/offline';

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
    start_date: props.package.start_date ? String(props.package.start_date).substring(0, 10) : '',
    end_date: props.package.end_date ? String(props.package.end_date).substring(0, 10) : '',
    start_time: props.package.start_time ? String(props.package.start_time).substring(0, 5) : '',
    end_time: props.package.end_time ? String(props.package.end_time).substring(0, 5) : '',
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
        alert('Ukuran thumbnail maksimal 2MB.');
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

function removeThumbnail() {
    form.thumbnail = null;
    thumbnailPreview.value = null;
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

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:pencil-box-outline" class="w-5 h-5" />
                </span>
                <span>Edit Paket Soal</span>
            </div>
        </template>
        <template #header-sub>{{ package.title }}</template>

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Navigation Tab Pills -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <Link :href="route('admin.packages.index')"
                      class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                    <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                    Kembali ke Bank Soal
                </Link>

                <div class="flex items-center gap-1.5 p-1 bg-muted/60 rounded-xl w-fit">
                    <Link :href="route('admin.packages.edit.informasi', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-card text-foreground shadow-sm flex items-center gap-1.5">
                        <Icon icon="mdi:pencil" class="w-3.5 h-3.5 text-primary" /> Informasi
                    </Link>
                    <Link :href="route('admin.packages.edit.cards', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:clipboard-text-outline" class="w-3.5 h-3.5" /> Card ({{ package.cards?.length || 0 }})
                    </Link>
                    <Link :href="route('admin.packages.edit.questions', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:help-circle-outline" class="w-3.5 h-3.5" /> Soal ({{ package.questions?.length || 0 }})
                    </Link>
                    <Link :href="route('admin.packages.detail', package.id)"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-muted-foreground hover:text-foreground flex items-center gap-1.5">
                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" /> Detail
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Basic Info -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:information-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Informasi Dasar Paket</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Judul Soal <span class="text-destructive">*</span></Label>
                            <Input id="title" v-model="form.title" required placeholder="Judul paket soal..." class="rounded-xl" />
                            <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi <span class="text-destructive">*</span></Label>
                            <Textarea id="description" v-model="form.description" required rows="3" placeholder="Deskripsi paket..." class="rounded-xl resize-none text-sm" />
                            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="kelas">Kelas</Label>
                                <Input id="kelas" v-model="form.kelas" placeholder="Contoh: 10" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label for="bidang">Bidang</Label>
                                <Input id="bidang" v-model="form.bidang" placeholder="Contoh: Matematika" class="rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label for="level">Level</Label>
                                <Input id="level" v-model="form.level" placeholder="Contoh: A1" class="rounded-xl" />
                            </div>
                        </div>

                        <!-- Thumbnail -->
                        <div class="space-y-2 pt-2">
                            <Label>Thumbnail Soal</Label>
                            <div v-if="thumbnailPreview" class="relative w-48 h-28 rounded-2xl overflow-hidden border border-border/60 shadow-sm">
                                <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                                <button type="button" @click="removeThumbnail" class="absolute top-2 right-2 w-6 h-6 bg-destructive text-white rounded-full flex items-center justify-center hover:scale-110 transition">
                                    <Icon icon="mdi:close" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            <Input v-else type="file" accept="image/*" @change="handleThumbnail" class="rounded-xl" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP (Maks. 2MB). Kosongkan jika tidak ingin diubah.</p>
                        </div>

                        <!-- Active Toggle -->
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Status Publikasi</p>
                                <p class="text-xs text-muted-foreground">Aktifkan agar soal dapat diakses siswa</p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Schedule -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:calendar-clock" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Jadwal Pengerjaan (Opsional)</h3>
                    </div>

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
                        <h3 class="font-bold text-base text-foreground">Pengaturan Hasil & Pembahasan</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Kunci Jawaban</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat melihat kunci jawaban setelah pengerjaan</p>
                            </div>
                            <Switch v-model="form.show_answer_key" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Pembahasan</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat membaca uraian pembahasan soal</p>
                            </div>
                            <Switch v-model="form.show_explanation" />
                        </div>

                        <div class="flex items-center justify-between p-4 bg-muted/30 rounded-2xl border border-border/50">
                            <div>
                                <p class="text-sm font-bold text-foreground">Tampilkan Skor / Nilai</p>
                                <p class="text-xs text-muted-foreground">Siswa dapat melihat perolehan skor akhir</p>
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
                        <Icon v-if="!form.processing" icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Perubahan Paket' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
