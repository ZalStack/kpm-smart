<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';

const route = inject('route');
const page = usePage();
const props = defineProps({
    user: Object,
});
const user = computed(() => props.user || page.props.auth?.user);

const profesiOrtuOptions = ['Petani', 'Pedagang', 'Karyawan Swasta', 'ASN/PNS', 'Wiraswasta', 'Nelayan', 'Buruh', 'Tidak Bekerja', 'Lainnya'];
const pendidikanOrtuOptions = ['Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1-D3', 'S1', 'S2', 'S3'];

const formatTanggal = (value) => {
    if (!value) return '';
    const parsed = new Date(`${value}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) return value;
    return parsed.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const form = useForm({
    name: user.value?.name || '',
    phone: user.value?.phone || '',
    school_name: user.value?.school_name || '',
    address: user.value?.address || '',
    parent_name: user.value?.parent_name || '',
    parent_phone: user.value?.parent_phone || '',
    place_of_birth: user.value?.place_of_birth || '',
    date_of_birth: user.value?.date_of_birth || '',
    parent_profession: user.value?.parent_profession || '',
    parent_education: user.value?.parent_education || '',
    profile_photo: null,
});

const birthLabel = computed(() => {
    const parts = [];
    if (form.place_of_birth) parts.push(form.place_of_birth);
    if (form.date_of_birth) parts.push(formatTanggal(form.date_of_birth));
    return parts.join(', ') || '-';
});

const photoPreview = ref(null);
const fileInput = ref(null);
const photoDragOver = ref(false);

const userInitial = computed(() => (user.value?.name || 'U').charAt(0).toUpperCase());

const profilePhotoUrl = computed(() => {
    if (photoPreview.value) return photoPreview.value;
    if (!user.value?.profile_photo) return null;
    if (user.value.profile_photo.startsWith('http://') || user.value.profile_photo.startsWith('https://') || user.value.profile_photo.startsWith('//')) {
        return user.value.profile_photo;
    }
    return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
});

function handlePhotoSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    processFile(file);
}

function processFile(file) {
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran foto profil maksimal 2MB');
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('Format foto harus JPG, PNG, atau WebP');
        return;
    }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (e) => { photoPreview.value = e.target.result; };
    reader.readAsDataURL(file);
}

function handleDrop(e) {
    e.preventDefault();
    photoDragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) processFile(file);
}

function removePhoto() {
    form.profile_photo = null;
    photoPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    form.put(route('user.profile.update'), {
        forceFormData: true,
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Pengaturan Profil - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:account-cog-outline" class="w-5 h-5" />
                </span>
                <span>Pengaturan Profil Siswa</span>
            </div>
        </template>
        <template #header-sub>
            Kelola data diri, akademik, kontak orang tua, dan foto profil akun belajarmu
        </template>

        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Profile Overview & Photo Card -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Photo Avatar -->
                    <div class="relative group shrink-0">
                        <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 flex items-center justify-center ring-4 ring-primary/10 shadow-md overflow-hidden">
                            <span class="text-3xl sm:text-4xl font-black text-primary select-none">{{ userInitial }}</span>
                            <img v-if="profilePhotoUrl" :src="profilePhotoUrl" alt="Foto Profil" class="absolute inset-0 w-full h-full object-cover" @error="$event.target.style.display='none'" />
                        </div>

                        <button v-if="photoPreview"
                                type="button"
                                @click="removePhoto"
                                class="absolute -top-2 -right-2 w-7 h-7 bg-destructive text-destructive-foreground rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition">
                            <Icon icon="mdi:close" class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Details & Dropzone -->
                    <div class="flex-1 space-y-3 text-center sm:text-left w-full">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-foreground">{{ user?.name }}</h2>
                            <p class="text-xs sm:text-sm text-muted-foreground">{{ user?.email }}</p>
                        </div>

                        <!-- Admin tags -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                <Icon icon="mdi:school" class="w-3.5 h-3.5" /> Kelas: {{ user?.student_class || '-' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                <Icon icon="mdi:calculator-variant" class="w-3.5 h-3.5" /> {{ user?.bidang || '-' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                <Icon icon="mdi:star-outline" class="w-3.5 h-3.5" /> Level: {{ user?.level || '-' }}
                            </span>
                        </div>

                        <!-- Dropzone trigger -->
                        <div @dragover.prevent="photoDragOver = true"
                             @dragleave="photoDragOver = false"
                             @drop="handleDrop"
                             @click="$refs.fileInput.click()"
                             :class="[
                                 'border-2 border-dashed rounded-2xl p-4 text-center cursor-pointer transition-all duration-200 mt-2',
                                 photoDragOver ? 'border-primary bg-primary/5' : 'border-border/70 hover:border-primary/50 hover:bg-muted/30'
                             ]">
                            <p class="text-xs font-bold text-foreground">Klik atau seret foto baru ke sini untuk mengganti avatar</p>
                            <p class="text-[11px] text-muted-foreground mt-0.5">JPG, PNG, atau WebP (Maks. 2MB)</p>
                        </div>
                        <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handlePhotoSelect" />
                        <p v-if="form.errors?.profile_photo" class="text-xs text-destructive mt-1">{{ form.errors.profile_photo }}</p>
                    </div>
                </div>
            </div>

            <!-- Two-Column Main Layout: Form (Left) & Sidebar Ringkasan (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Edit Form -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Section 1: Informasi Akun -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-border/50 mb-5">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:card-account-details-outline" class="w-5 h-5 text-primary" />
                                <h3 class="font-bold text-base text-foreground">Informasi Akun</h3>
                            </div>
                            <span class="text-xs text-muted-foreground">Tanda (<span class="text-destructive">*</span>) wajib</span>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Nama Lengkap <span class="text-destructive">*</span></Label>
                                    <Input id="name" v-model="form.name" required placeholder="Masukkan nama lengkap" class="rounded-xl" />
                                    <p v-if="form.errors?.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Surel / Email <span class="text-destructive">*</span></Label>
                                    <Input id="email" :value="user?.email || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="username">Username</Label>
                                    <Input id="username" :value="user?.username || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Telepon / WhatsApp</Label>
                                    <Input id="phone" v-model="form.phone" placeholder="0812..." class="rounded-xl" />
                                    <p v-if="form.errors?.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Alamat</Label>
                                <Textarea id="address" v-model="form.address" rows="2" placeholder="Tuliskan alamat lengkap..." class="rounded-xl resize-none text-sm" />
                                <p v-if="form.errors?.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Data Akademik (Filter Manajemen) -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-border/50 mb-4">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:school-outline" class="w-5 h-5 text-primary" />
                                <h3 class="font-bold text-base text-foreground">Data Akademik (Filter Manajemen)</h3>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground mb-5">Kelas, Bidang, Level hanya dapat diubah oleh SuperAdmin / Administrator.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="student_class" class="flex items-center gap-1">
                                    Kelas
                                    <Icon icon="mdi:lock" class="w-3 h-3 text-muted-foreground" />
                                </Label>
                                <Input id="student_class" :value="user?.student_class || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                            </div>

                            <div class="space-y-2">
                                <Label for="bidang" class="flex items-center gap-1">
                                    Bidang
                                    <Icon icon="mdi:lock" class="w-3 h-3 text-muted-foreground" />
                                </Label>
                                <Input id="bidang" :value="user?.bidang || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                            </div>

                            <div class="space-y-2">
                                <Label for="school_name">Asal Sekolah</Label>
                                <Input id="school_name" v-model="form.school_name" placeholder="Contoh: SMP Negeri 1..." class="rounded-xl" />
                                <p v-if="form.errors?.school_name" class="text-xs text-destructive">{{ form.errors.school_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="level" class="flex items-center gap-1">
                                    Level Berbakat
                                    <Icon icon="mdi:lock" class="w-3 h-3 text-muted-foreground" />
                                </Label>
                                <Input id="level" :value="user?.level || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                                <p class="text-[10px] text-muted-foreground">Hanya dapat diubah oleh SuperAdmin / Administrator.</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="kelas_student">Kelas (Student)</Label>
                                <Input id="kelas_student" :value="user?.student_class || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                            </div>

                            <div class="space-y-2">
                                <Label for="parent_name">Nama Orang Tua</Label>
                                <Input id="parent_name" v-model="form.parent_name" placeholder="Nama ayah / ibu..." class="rounded-xl" />
                                <p v-if="form.errors?.parent_name" class="text-xs text-destructive">{{ form.errors.parent_name }}</p>
                            </div>

                            <div class="space-y-2 sm:col-span-2">
                                <Label for="parent_phone">HP Orang Tua</Label>
                                <Input id="parent_phone" v-model="form.parent_phone" placeholder="0812..." class="rounded-xl" />
                                <p v-if="form.errors?.parent_phone" class="text-xs text-destructive">{{ form.errors.parent_phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Data Pribadi & Orang Tua -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-border/50 mb-5">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:account-group-outline" class="w-5 h-5 text-primary" />
                                <h3 class="font-bold text-base text-foreground">Data Pribadi &amp; Orang Tua</h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="place_of_birth">Tempat Lahir</Label>
                                <Input id="place_of_birth" v-model="form.place_of_birth" placeholder="contoh: Jakarta" class="rounded-xl" />
                                <p v-if="form.errors?.place_of_birth" class="text-xs text-destructive">{{ form.errors.place_of_birth }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="date_of_birth">Tanggal Lahir</Label>
                                <Input id="date_of_birth" type="date" v-model="form.date_of_birth" class="rounded-xl" />
                                <p v-if="form.errors?.date_of_birth" class="text-xs text-destructive">{{ form.errors.date_of_birth }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="parent_profession">Profesi Orang Tua</Label>
                                <select id="parent_profession" v-model="form.parent_profession" class="w-full px-4 py-2.5 rounded-xl border border-input bg-background text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                    <option value="">— Pilih Profesi —</option>
                                    <option v-for="profesi in profesiOrtuOptions" :key="profesi" :value="profesi">{{ profesi }}</option>
                                </select>
                                <p v-if="form.errors?.parent_profession" class="text-xs text-destructive">{{ form.errors.parent_profession }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="parent_education">Latar Pendidikan Orang Tua</Label>
                                <select id="parent_education" v-model="form.parent_education" class="w-full px-4 py-2.5 rounded-xl border border-input bg-background text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                    <option value="">— Pilih Pendidikan —</option>
                                    <option v-for="pendidikan in pendidikanOrtuOptions" :key="pendidikan" :value="pendidikan">{{ pendidikan }}</option>
                                </select>
                                <p v-if="form.errors?.parent_education" class="text-xs text-destructive">{{ form.errors.parent_education }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Message -->
                    <div v-if="page.props.flash?.success" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm font-medium flex items-center gap-2">
                        <Icon icon="mdi:check-circle" class="w-5 h-5 shrink-0" />
                        {{ page.props.flash.success }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end pt-2">
                        <Button type="button" @click="submit" :loading="form.processing" class="w-full sm:w-auto gap-2 font-bold px-8 py-3 rounded-xl shadow-md min-h-12 bg-primary hover:bg-primary/90 text-primary-foreground">
                            <Icon v-if="!form.processing" icon="mdi:content-save-outline" class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>

                </div>

                <!-- Right Column: Sidebar Ringkasan & Keamanan Akun -->
                <div class="space-y-6">

                    <!-- Ringkasan Card -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 shadow-sm">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground pb-3 border-b border-border/40">Ringkasan</h3>
                        <div class="mt-4 space-y-3 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-border/20">
                                <span class="text-muted-foreground">Kelas</span>
                                <span class="font-bold text-foreground">{{ user?.student_class || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-border/20">
                                <span class="text-muted-foreground">Bidang</span>
                                <span class="font-bold text-foreground">{{ user?.bidang || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-border/20">
                                <span class="text-muted-foreground">Level Berbakat</span>
                                <span class="font-bold text-foreground">{{ user?.level || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-border/20">
                                <span class="text-muted-foreground">Tempat, Tgl Lahir</span>
                                <span class="font-bold text-foreground text-right">{{ birthLabel }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-border/20">
                                <span class="text-muted-foreground">Profesi Orang Tua</span>
                                <span class="font-bold text-foreground text-right">{{ form.parent_profession || '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-muted-foreground">Pendidikan Orang Tua</span>
                                <span class="font-bold text-foreground text-right">{{ form.parent_education || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Keamanan Akun Card -->
                    <div class="bg-card rounded-3xl border border-border/60 p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                            <Icon icon="mdi:shield-lock-outline" class="w-5 h-5" />
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Keamanan Akun</h3>
                        <p class="text-xs text-muted-foreground mt-1 leading-relaxed">Ubah password secara berkala.</p>

                        <Link :href="route('user.profile.change-password')" class="block mt-4">
                            <Button variant="outline" class="w-full gap-2 font-bold rounded-xl text-amber-700 bg-amber-50 dark:bg-amber-950/30 hover:bg-amber-100 border-amber-200 dark:border-amber-800">
                                Ubah Password
                            </Button>
                        </Link>
                    </div>

                </div>

            </div>

        </div>
    </UserLayout>
</template>
