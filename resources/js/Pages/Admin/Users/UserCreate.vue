<script setup>
import { inject,  ref } from 'vue';
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

        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar User
        </Link>

        <div>
            <div class="bg-card rounded-xl border shadow-sm">
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- Photo -->
                    <div class="p-6 border-b flex items-center gap-6">
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-muted flex items-center justify-center flex-shrink-0">
                            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                            <svg v-else class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div>
                            <Label>Foto Profil</Label>
                            <Input type="file" accept="image/*" @change="handlePhoto" class="mt-1" />
                            <p class="text-xs text-muted-foreground mt-1">JPG, PNG, WebP. Maks 2MB.</p>
                        </div>
                    </div>

                    <!-- Info Akun -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold mb-4">👤 Info Akun</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label>Nama Lengkap <span class="text-destructive">*</span></Label>
                                <Input v-model="form.name" required placeholder="Nama lengkap" />
                                <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Email <span class="text-destructive">*</span></Label>
                                <Input v-model="form.email" type="email" required placeholder="email@contoh.com" />
                                <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Password <span class="text-destructive">*</span></Label>
                                    <Input v-model="form.password" type="password" required minlength="8" placeholder="Minimal 8 karakter" />
                                    <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label>Konfirmasi Password <span class="text-destructive">*</span></Label>
                                    <Input v-model="form.password_confirmation" type="password" required placeholder="Ulangi password" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Siswa -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold mb-4">🎓 Data Siswa</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Kelas</Label>
                                <Input v-model="form.student_class" placeholder="Contoh: X IPA 1" />
                            </div>
                            <div class="space-y-2">
                                <Label>Bidang</Label>
                                <Input v-model="form.bidang" placeholder="Contoh: Matematika" />
                            </div>
                            <div class="space-y-2">
                                <Label>Level</Label>
                                <Input v-model="form.level" placeholder="Contoh: Pemula" />
                            </div>
                            <div class="space-y-2">
                                <Label>Sekolah</Label>
                                <Input v-model="form.school_name" placeholder="Nama sekolah" />
                            </div>
                        </div>
                    </div>

                    <!-- Info Kontak -->
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold mb-4">📱 Info Kontak</h2>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>No. HP/WA</Label>
                                    <Input v-model="form.phone" placeholder="08xxx" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Jenis Kelamin</Label>
                                    <Select v-model="form.gender">
                                        <option value="">Pilih</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </Select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Agama</Label>
                                <Select v-model="form.religion">
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
                                <Label>Alamat</Label>
                                <Textarea v-model="form.address" :rows="2" placeholder="Alamat lengkap" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 flex items-center justify-end gap-3">
                        <Link :href="route('admin.users.index')">Batal</Link>
                        <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan User' }}</Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
