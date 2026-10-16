<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
const route = inject('route');

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
    student_class: props.user.student_class || '',
    bidang: props.user.bidang || '',
    level: props.user.level || '',
    school_name: props.user.school_name || '',
    phone: props.user.phone || '',
    gender: props.user.gender || '',
    religion: props.user.religion || '',
    address: props.user.address || '',
    profile_photo: null,
});

const photoPreview = ref(
    props.user.profile_photo
        ? ((props.user.profile_photo.startsWith('http://') || props.user.profile_photo.startsWith('https://') || props.user.profile_photo.startsWith('//'))
            ? props.user.profile_photo
            : '/storage/' + props.user.profile_photo)
        : null
);
const dragOver = ref(false);

function handlePhoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return; }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function handleDrop(e) {
    dragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return; }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function submit() {
    form.put(route('admin.users.update', props.user.id), { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Edit ' + user.name + ' - Admin'" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:pencil-outline" class="w-5 h-5" />
                </span>
                <span>Edit Siswa</span>
            </div>
        </template>
        <template #header-sub>{{ user.name }} &bull; {{ user.email }}</template>

        <div class="max-w-4xl mx-auto space-y-6">
            <Link :href="route('admin.users.show', user.id)" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Detail Siswa
            </Link>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6">
                <!-- Section 1: Photo & Account -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:account-badge-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Foto & Informasi Akun</h3>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-muted/20 border border-border/40">
                        <div
                            class="relative group shrink-0"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                        >
                            <label class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-muted flex items-center justify-center cursor-pointer border-2 border-dashed transition"
                                   :class="dragOver ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50'">
                                <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                <div v-else class="flex flex-col items-center gap-1 text-muted-foreground">
                                    <Icon icon="mdi:camera-outline" class="w-7 h-7" />
                                    <span class="text-[10px] font-bold">Ganti Foto</span>
                                </div>
                            </label>
                            <input type="file" accept="image/*" @change="handlePhoto" class="sr-only" />
                        </div>

                        <div class="text-center sm:text-left space-y-1">
                            <Label class="text-xs font-bold text-foreground">Foto Profil Siswa</Label>
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Format gambar JPG, PNG, atau WebP. Kosongkan jika tidak ingin mengubah foto profil saat ini.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2">
                        <div class="space-y-1.5 sm:col-span-2">
                            <Label class="text-xs font-semibold text-foreground">Nama Lengkap <span class="text-destructive">*</span></Label>
                            <Input v-model="form.name" required class="h-11 rounded-xl bg-background" />
                            <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5 sm:col-span-2">
                            <Label class="text-xs font-semibold text-foreground">Email <span class="text-destructive">*</span></Label>
                            <Input v-model="form.email" type="email" required class="h-11 rounded-xl bg-background" />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5 sm:col-span-2 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300">
                            <p class="text-xs font-semibold flex items-center gap-1.5">
                                <Icon icon="mdi:shield-lock-outline" class="w-4 h-4 text-amber-600" />
                                Kata Sandi & Username Dikelola Terpusat di Web Induk
                            </p>
                            <p class="text-[11px] text-muted-foreground mt-0.5">
                                Perubahan kata sandi atau username siswa hanya dapat dilakukan melalui portal Web Induk untuk menjaga konsistensi login di seluruh sistem.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Student Academic Data -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:school-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Data Akademis Siswa</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Tingkat Kelas</Label>
                            <Select v-model="form.student_class" class="h-11 rounded-xl bg-background">
                                <option value="">Pilih Kelas</option>
                                <option v-for="k in 12" :key="k" :value="String(k)">Kelas {{ k }}</option>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Bidang Studi</Label>
                            <Select v-model="form.bidang" class="h-11 rounded-xl bg-background">
                                <option value="">Pilih Bidang</option>
                                <option value="MATEMATIKA">MATEMATIKA</option>
                                <option value="IPA">IPA</option>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Level Kemampuan</Label>
                            <Select v-model="form.level" class="h-11 rounded-xl bg-background">
                                <option value="">Pilih Level</option>
                                <option value="A1">A1</option>
                                <option value="A2">A2</option>
                                <option value="B1">B1</option>
                                <option value="B2">B2</option>
                                <option value="C1">C1</option>
                                <option value="C2">C2</option>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Asal Sekolah</Label>
                            <Input v-model="form.school_name" class="h-11 rounded-xl bg-background" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Contact & Personal Details -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 pb-4 border-b border-border/50">
                        <Icon icon="mdi:card-account-phone-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Kontak & Informasi Pribadi</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">No. WhatsApp / HP</Label>
                            <Input v-model="form.phone" class="h-11 rounded-xl bg-background" />
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-foreground">Jenis Kelamin</Label>
                            <Select v-model="form.gender" class="h-11 rounded-xl bg-background">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </Select>
                        </div>

                        <div class="space-y-1.5 sm:col-span-2">
                            <Label class="text-xs font-semibold text-foreground">Agama</Label>
                            <Select v-model="form.religion" class="h-11 rounded-xl bg-background">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </Select>
                        </div>

                        <div class="space-y-1.5 sm:col-span-2">
                            <Label class="text-xs font-semibold text-foreground">Alamat Domisili</Label>
                            <Textarea v-model="form.address" rows="3" class="rounded-2xl bg-background p-4 border-border/60" />
                        </div>
                    </div>
                </div>

                <!-- Submit Action Bar -->
                <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm flex items-center justify-end gap-3">
                    <Link :href="route('admin.users.show', user.id)" class="px-5 py-2.5 rounded-xl border border-border text-xs sm:text-sm font-semibold hover:bg-muted transition">
                        Batal
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="h-11 px-6 rounded-xl font-semibold gap-2 shadow-sm">
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save" class="w-4 h-4" />
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
