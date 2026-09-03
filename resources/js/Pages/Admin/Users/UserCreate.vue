<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
const route = inject('route');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    student_class: '',
    bidang: '',
    level: '',
    school_name: '',
    phone: '',
    gender: '',
    religion: '',
    address: '',
    profile_photo: null,
});

const photoPreview = ref(null);

function handlePhoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return; }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function submit() {
    form.post(route('admin.users.store'), { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <Head title="Tambah User - Admin" />

        <template #header-title>Tambah User Baru</template>
        <template #header-sub>Buat akun baru untuk pengguna</template>

        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6 group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar User
        </Link>

        <div>
            <div class="bg-card rounded-xl border shadow-sm overflow-hidden">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Photo -->
                    <div class="p-6 border-b flex flex-col sm:flex-row items-start sm:items-center gap-5">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-muted flex items-center justify-center flex-shrink-0 ring-4 ring-background shadow-inner">
                            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                            <svg v-else class="w-8 h-8 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <Label class="text-sm font-semibold">Foto Profil</Label>
                            <Input type="file" accept="image/*" @change="handlePhoto" class="mt-1.5" />
                            <p class="text-xs text-muted-foreground mt-1.5">JPG, PNG, WebP. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Info Akun -->
                    <div class="p-6 border-b">
                        <div class="flex items-center gap-2.5 mb-5">
                            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold">Info Akun</h2>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Nama Lengkap <span class="text-destructive">*</span></Label>
                                <Input v-model="form.name" required placeholder="Nama lengkap" class="h-10" />
                                <p v-if="form.errors.name" class="text-xs text-destructive flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Email <span class="text-destructive">*</span></Label>
                                <Input v-model="form.email" type="email" required placeholder="email@contoh.com" class="h-10" />
                                <p v-if="form.errors.email" class="text-xs text-destructive flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                    {{ form.errors.email }}
                                </p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Password <span class="text-destructive">*</span></Label>
                                    <Input v-model="form.password" type="password" required minlength="8" placeholder="Minimal 8 karakter" class="h-10" />
                                    <p v-if="form.errors.password" class="text-xs text-destructive flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                        {{ form.errors.password }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Konfirmasi Password <span class="text-destructive">*</span></Label>
                                    <Input v-model="form.password_confirmation" type="password" required placeholder="Ulangi password" class="h-10" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Siswa -->
                    <div class="p-6 border-b">
                        <div class="flex items-center gap-2.5 mb-5">
                            <div class="w-9 h-9 rounded-lg bg-warning-500/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-warning-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold">Data Siswa</h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Kelas</Label>
                                <Input v-model="form.student_class" placeholder="Contoh: X IPA 1" class="h-10" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Bidang</Label>
                                <Input v-model="form.bidang" placeholder="Contoh: Matematika" class="h-10" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Level</Label>
                                <Input v-model="form.level" placeholder="Contoh: Pemula" class="h-10" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Sekolah</Label>
                                <Input v-model="form.school_name" placeholder="Nama sekolah" class="h-10" />
                            </div>
                        </div>
                    </div>

                    <!-- Info Kontak -->
                    <div class="p-6">
                        <div class="flex items-center gap-2.5 mb-5">
                            <div class="w-9 h-9 rounded-lg bg-fern/10 flex items-center justify-center">
                                <svg class="w-4.5 h-4.5 text-fern" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold">Info Kontak</h2>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">No. HP/WA</Label>
                                    <Input v-model="form.phone" placeholder="08xxx" class="h-10" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium">Jenis Kelamin</Label>
                                    <Select v-model="form.gender" class="h-10">
                                        <option value="">Pilih</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </Select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Agama</Label>
                                <Select v-model="form.religion" class="h-10">
                                    <option value="">Pilih</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-medium">Alamat</Label>
                                <Textarea v-model="form.address" :rows="2" placeholder="Alamat lengkap" class="resize-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 bg-muted/30 border-t flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <Link :href="route('admin.users.index')" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border bg-background text-sm font-medium hover:bg-accent transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing" class="gap-2 h-10 px-6">
                            <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <div v-else class="spinner"></div>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan User' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
