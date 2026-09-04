<script setup>
import { inject, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Switch from '@/Components/ui/switch/Switch.vue';
import { Icon } from '@iconify/vue';

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

        <template #header-title>Edit Soal</template>
        <template #header-sub>{{ package.title }}</template>

        <!-- Navigation / Tabs Header -->
        <div class="space-y-4 mb-6">
            <Link :href="route('admin.packages.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-all duration-200 hover:translate-x-[-2px]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Kembali ke Daftar Soal
            </Link>

            <div class="flex flex-wrap gap-2 pt-2">
                <Link :href="route('admin.packages.edit.informasi', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary text-primary-foreground shadow-sm transition-all duration-300 hover:shadow-md hover:shadow-primary/20 active:scale-95 relative">
                    <span class="absolute bottom-0 left-2 right-2 h-0.5 bg-white/60 rounded-full"></span>
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Informasi
                </Link>
                <Link :href="route('admin.packages.edit.cards', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Card
                </Link>
                <Link :href="route('admin.packages.edit.questions', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:help-circle-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Soal
                </Link>
                <Link :href="route('admin.packages.detail', package.id)" class="px-4 py-2 rounded-lg text-sm font-medium bg-muted text-muted-foreground hover:bg-muted/80 transition-all duration-200 hover:shadow-sm active:scale-95">
                    <Icon icon="mdi:eye-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Detail
                </Link>
            </div>
        </div>

        <div>
            <div class="bg-card rounded-2xl border shadow-sm overflow-hidden animate-fade-in-up">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Informasi Dasar -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg">
                            <Icon icon="mdi:pencil-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Informasi Dasar
                        </h3>

                        <div class="space-y-2">
                            <Label for="title">Judul Soal <span class="text-destructive">*</span></Label>
                            <Input id="title" v-model="form.title" required placeholder="Contoh: Latihan Soal Matematika" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.title" class="text-xs text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Deskripsi <span class="text-destructive">*</span></Label>
                            <Textarea id="description" v-model="form.description" required :rows="3" placeholder="Deskripsi mengenai soal..." class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                            <p v-if="form.errors.description" class="text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="kelas">Kelas</Label>
                                <Input id="kelas" v-model="form.kelas" placeholder="Contoh: Kelas 10" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.kelas" class="text-xs text-destructive">{{ form.errors.kelas }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="bidang">Bidang</Label>
                                <Input id="bidang" v-model="form.bidang" placeholder="Contoh: Matematika" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.bidang" class="text-xs text-destructive">{{ form.errors.bidang }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="level">Level</Label>
                                <Input id="level" v-model="form.level" placeholder="Contoh: Pemula" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.level" class="text-xs text-destructive">{{ form.errors.level }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Thumbnail Soal</Label>
                            <div v-if="thumbnailPreview" class="mb-3 relative w-44 h-28 rounded-xl overflow-hidden border shadow-sm">
                                <img :src="thumbnailPreview" class="w-full h-full object-cover" />
                            </div>
                            <Input type="file" accept="image/*" @change="handleThumbnail" />
                            <p class="text-xs text-muted-foreground">Format: JPG, PNG, WebP. Maks 2MB. Kosongkan bila tidak diubah.</p>
                            <p v-if="form.errors.thumbnail" class="text-xs text-destructive">{{ form.errors.thumbnail }}</p>
                        </div>

                        <div class="pt-2 flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                            <div>
                                <p class="text-sm font-medium">Status Soal</p>
                                <p class="text-xs text-muted-foreground">Aktifkan agar soal dapat diakses oleh siswa</p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>
                    </div>

                    <!-- Jadwal Pengerjaan -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg">
                            <Icon icon="mdi:calendar-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Jadwal Pengerjaan (Opsional)
                        </h3>
                        <p class="text-xs text-muted-foreground">Kosongkan bila soal dapat dikerjakan kapan saja tanpa batasan tanggal/jam.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_date">Tanggal Mulai</Label>
                                <Input id="start_date" v-model="form.start_date" type="date" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.start_date" class="text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_date">Tanggal Berakhir</Label>
                                <Input id="end_date" v-model="form.end_date" type="date" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.end_date" class="text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="start_time">Jam Mulai</Label>
                                <Input id="start_time" v-model="form.start_time" type="time" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.start_time" class="text-xs text-destructive">{{ form.errors.start_time }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="end_time">Jam Berakhir</Label>
                                <Input id="end_time" v-model="form.end_time" type="time" class="transition-all duration-200 focus:shadow-md focus:ring-2 focus:ring-primary/20" />
                                <p v-if="form.errors.end_time" class="text-xs text-destructive">{{ form.errors.end_time }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Soal -->
                    <div class="p-6 border-b space-y-4">
                        <h3 class="text-base font-semibold inline-flex items-center gap-2 bg-gradient-to-r from-primary/10 to-transparent px-3 py-1.5 rounded-lg">
                            <Icon icon="mdi:cog-outline" class="w-5 h-5 inline-block align-middle mr-1" /> Pengaturan Soal
                        </h3>
                        <p class="text-xs text-muted-foreground">Atur visibilitas kunci jawaban, pembahasan, dan skor untuk siswa setelah mengerjakan.</p>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                                <div>
                                    <p class="text-sm font-medium">Tampilkan Kunci Jawaban</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Siswa dapat melihat jawaban yang benar setelah selesai</p>
                                </div>
                                <Switch v-model="form.show_answer_key" />
                            </div>

                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
                                <div>
                                    <p class="text-sm font-medium">Tampilkan Pembahasan</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Siswa dapat melihat penjelasan/pembahasan soal setelah selesai</p>
                                </div>
                                <Switch v-model="form.show_explanation" />
                            </div>

                            <div class="flex items-center justify-between p-4 bg-muted/30 rounded-xl border border-transparent hover:border-border/50 transition-all duration-200">
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
                        <Link :href="route('admin.packages.index')" class="inline-flex items-center justify-center px-4 py-2 border rounded-lg text-sm font-medium hover:bg-muted transition-all duration-200 active:scale-95">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing" class="transition-all duration-300 hover:shadow-lg active:scale-95">
                            {{ form.processing ? 'Menyimpan...' : '' }}<Icon v-if="!form.processing" icon="mdi:content-save" class="w-4 h-4 inline-block align-middle mr-1" />{{ form.processing ? '' : 'Simpan Perubahan' }}
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
