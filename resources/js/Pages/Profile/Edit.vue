<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';

const route = inject('route');
const page = usePage();
const props = defineProps({
    user: Object,
});
const user = computed(() => props.user || page.props.auth?.user);

const profesiOrtuOptions = ['Petani', 'Pedagang', 'Karyawan Swasta', 'ASN/PNS', 'Wiraswasta', 'Nelayan', 'Buruh', 'Tidak Bekerja', 'Lainnya'];
const pendidikanOrtuOptions = ['Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1-D3', 'S1', 'S2', 'S3'];

const normalizeDate = (val) => {
    if (!val) return '';
    const s = String(val).trim();
    return s.includes('T') ? s.split('T')[0] : (s.includes(' ') ? s.split(' ')[0] : s);
};

const formatTanggal = (value) => {
    if (!value) return '';
    const clean = normalizeDate(value);
    const parsed = new Date(`${clean}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) return clean;
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
    date_of_birth: normalizeDate(user.value?.date_of_birth),
    parent_profession: user.value?.parent_profession || '',
    parent_education: user.value?.parent_education || '',
    father_name: user.value?.father_name || user.value?.parent_name || '',
    father_phone: user.value?.father_phone || user.value?.parent_phone || '',
    father_education: user.value?.father_education || user.value?.parent_education || '',
    father_profession: user.value?.father_profession || user.value?.parent_profession || '',
    mother_name: user.value?.mother_name || '',
    mother_phone: user.value?.mother_phone || '',
    mother_education: user.value?.mother_education || '',
    mother_profession: user.value?.mother_profession || '',
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
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('user.profile.update'), {
        forceFormData: true,
    });
}

/** Bagian formulir yang bisa dilipat di mobile agar tidak membanjiri layar kecil. */
const sections = [
    { key: 'account', title: 'Informasi Akun', icon: 'mdi:card-account-details-outline', open: true },
    { key: 'academic', title: 'Data Akademik', icon: 'mdi:school-outline', open: false },
    { key: 'personal', title: 'Data Pribadi & Orang Tua', icon: 'mdi:account-group-outline', open: false },
];
const openSections = ref(sections.filter(s => s.open).map(s => s.key));

function toggleSection(key) {
    const i = openSections.value.indexOf(key);
    if (i >= 0) openSections.value.splice(i, 1);
    else openSections.value.push(key);
}
</script>

<template>
    <UserLayout>
        <Head title="Pengaturan Profil - PR KK" />

        <template #header-title>Pengaturan Profil</template>
        <template #header-sub>Kelola data diri, akademik, kontak orang tua, dan foto profil akun belajarmu.</template>

        <div class="mx-auto max-w-6xl space-y-6">
            <!-- ====== KARTU PROFIL ====== -->
            <section class="ui-card anim-fade-in-up overflow-hidden">
                <!-- Pita gradasi latar -->
                <div class="relative h-20 bg-gradient-to-r from-primary via-indigo-600 to-violet-700 sm:h-24">
                    <div class="grid-dots absolute inset-0 opacity-30"></div>
                </div>

                <div class="flex flex-col gap-5 px-5 pb-6 sm:flex-row sm:items-end sm:px-7">
                    <!-- Avatar -->
                    <div class="-mt-12 shrink-0 sm:-mt-14">
                        <div class="relative h-24 w-24 overflow-hidden rounded-2xl border-4 border-background bg-gradient-to-br from-primary/20 to-primary/5 shadow-md ring-4 ring-primary/10 sm:h-28 sm:w-28">
                            <span class="absolute inset-0 flex items-center justify-center text-3xl font-black text-primary select-none sm:text-4xl">{{ userInitial }}</span>
                            <img v-if="profilePhotoUrl" :src="profilePhotoUrl" alt="Foto Profil" class="absolute inset-0 h-full w-full object-cover" @error="$event.target.style.display='none'" />
                        </div>

                        <button
                            v-if="photoPreview"
                            type="button"
                            @click="removePhoto"
                            class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-destructive text-destructive-foreground shadow-lg transition hover:scale-110 sm:right-3 sm:top-3"
                            aria-label="Hapus foto baru"
                        >
                            <Icon icon="mdi:close" class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Info & Dropzone -->
                    <div class="min-w-0 flex-1 space-y-3 text-center sm:text-left">
                        <div>
                            <h2 class="truncate text-lg font-extrabold tracking-tight text-foreground sm:text-xl">{{ user?.name }}</h2>
                            <p class="truncate text-sm text-muted-foreground">{{ user?.email }}</p>
                        </div>

                        <div class="flex flex-wrap justify-center gap-2 sm:justify-start">
                            <span class="chip border border-primary/20 bg-primary/10 text-primary">
                                <Icon icon="mdi:school" class="h-3.5 w-3.5" />
                                Kelas {{ user?.student_class || '-' }}
                            </span>
                            <span class="chip border border-emerald-200 bg-emerald-50 text-emerald-700">
                                <Icon icon="mdi:calculator-variant" class="h-3.5 w-3.5" />
                                {{ user?.bidang || '-' }}
                            </span>
                            <span class="chip border border-amber-200 bg-amber-50 text-amber-700">
                                <Icon icon="mdi:star-outline" class="h-3.5 w-3.5" />
                                Level {{ user?.level || '-' }}
                            </span>
                        </div>

                        <div
                            @dragover.prevent="photoDragOver = true"
                            @dragleave="photoDragOver = false"
                            @drop="handleDrop"
                            @click="$refs.fileInput.click()"
                            :class="[
                                'mt-1 cursor-pointer rounded-2xl border-2 border-dashed p-4 text-center transition-all duration-200',
                                photoDragOver ? 'border-primary bg-primary/5' : 'border-border/70 hover:border-primary/50 hover:bg-muted/30'
                            ]"
                        >
                            <p class="text-xs font-bold text-foreground">Klik atau seret foto baru ke sini untuk mengganti avatar</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">JPG, PNG, atau WebP (Maks. 2MB)</p>
                        </div>
                        <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handlePhotoSelect" />
                        <p v-if="form.errors?.profile_photo" class="text-xs text-destructive">{{ form.errors.profile_photo }}</p>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- ====== KOLOM FORM ====== -->
                <div class="space-y-4 lg:col-span-2">
                    <!-- Informasi Akun -->
                    <section class="ui-card anim-fade-in-up overflow-hidden">
                        <button type="button" class="flex w-full items-center justify-between gap-3 p-5 text-left sm:p-6" @click="toggleSection('account')">
                            <span class="flex items-center gap-2.5">
                                <span class="icon-box-sm bg-primary/10 text-primary">
                                    <Icon icon="mdi:card-account-details-outline" class="h-5 w-5" />
                                </span>
                                <span class="text-base font-bold text-foreground">Informasi Akun</span>
                            </span>
                            <Icon icon="mdi:chevron-down" class="h-5 w-5 shrink-0 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': openSections.includes('account') }" />
                        </button>

                        <div v-show="openSections.includes('account')" class="space-y-4 border-t border-border/60 p-5 sm:p-6">
                            <p class="text-xs text-muted-foreground">Tanda (<span class="text-destructive">*</span>) wajib diisi.</p>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Nama Lengkap <span class="text-destructive">*</span></Label>
                                    <Input id="name" v-model="form.name" required placeholder="Masukkan nama lengkap" class="rounded-xl" />
                                    <p v-if="form.errors?.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Surel / Email <span class="text-destructive">*</span></Label>
                                    <Input id="email" :value="user?.email || '-'" disabled class="rounded-xl bg-muted/40 opacity-75 cursor-not-allowed" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="username">Username</Label>
                                    <Input id="username" :value="user?.username || '-'" disabled class="rounded-xl bg-muted/40 opacity-75 cursor-not-allowed" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone">Telepon / WhatsApp</Label>
                                    <Input id="phone" v-model="form.phone" placeholder="0812..." class="rounded-xl" />
                                    <p v-if="form.errors?.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Alamat</Label>
                                <Textarea id="address" v-model="form.address" rows="2" placeholder="Tuliskan alamat lengkap..." class="resize-none rounded-xl text-sm" />
                                <p v-if="form.errors?.address" class="text-xs text-destructive">{{ form.errors.address }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Data Akademik -->
                    <section class="ui-card anim-fade-in-up overflow-hidden">
                        <button type="button" class="flex w-full items-center justify-between gap-3 p-5 text-left sm:p-6" @click="toggleSection('academic')">
                            <span class="flex items-center gap-2.5">
                                <span class="icon-box-sm bg-indigo-50 text-indigo-600">
                                    <Icon icon="mdi:school-outline" class="h-5 w-5" />
                                </span>
                                <span class="text-base font-bold text-foreground">Data Akademik</span>
                            </span>
                            <Icon icon="mdi:chevron-down" class="h-5 w-5 shrink-0 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': openSections.includes('academic') }" />
                        </button>

                        <div v-show="openSections.includes('academic')" class="border-t border-border/60 p-5 sm:p-6">
                            <p class="mb-5 text-xs text-muted-foreground">Kelas, Bidang, dan Level hanya dapat diubah oleh SuperAdmin / Administrator.</p>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="student_class" class="flex items-center gap-1">
                                        Kelas
                                        <Icon icon="mdi:lock" class="h-3 w-3 text-muted-foreground" />
                                    </Label>
                                    <Input id="student_class" :value="user?.student_class || '-'" disabled class="rounded-xl bg-muted/40 opacity-75 cursor-not-allowed" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="bidang" class="flex items-center gap-1">
                                        Bidang
                                        <Icon icon="mdi:lock" class="h-3 w-3 text-muted-foreground" />
                                    </Label>
                                    <Input id="bidang" :value="user?.bidang || '-'" disabled class="rounded-xl bg-muted/40 opacity-75 cursor-not-allowed" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="school_name">Asal Sekolah</Label>
                                    <Input id="school_name" v-model="form.school_name" placeholder="Contoh: SMP Negeri 1..." class="rounded-xl" />
                                    <p v-if="form.errors?.school_name" class="text-xs text-destructive">{{ form.errors.school_name }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="level" class="flex items-center gap-1">
                                        Level Berbakat
                                        <Icon icon="mdi:lock" class="h-3 w-3 text-muted-foreground" />
                                    </Label>
                                    <Input id="level" :value="user?.level || '-'" disabled class="rounded-xl bg-muted/40 opacity-75 cursor-not-allowed" />
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Data Pribadi & Orang Tua -->
                    <section class="ui-card anim-fade-in-up overflow-hidden">
                        <button type="button" class="flex w-full items-center justify-between gap-3 p-5 text-left sm:p-6" @click="toggleSection('personal')">
                            <span class="flex items-center gap-2.5">
                                <span class="icon-box-sm bg-emerald-50 text-emerald-600">
                                    <Icon icon="mdi:account-group-outline" class="h-5 w-5" />
                                </span>
                                <span class="text-base font-bold text-foreground">Data Pribadi &amp; Orang Tua</span>
                            </span>
                            <Icon icon="mdi:chevron-down" class="h-5 w-5 shrink-0 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': openSections.includes('personal') }" />
                        </button>

                        <div v-show="openSections.includes('personal')" class="space-y-5 border-t border-border/60 p-5 sm:p-6">
                            <!-- Lahir -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                            </div>

                            <!-- Ayah -->
                            <div class="space-y-4 rounded-2xl border border-border/60 bg-muted/30 p-4 sm:p-5">
                                <h4 class="flex items-center gap-2 text-sm font-bold text-foreground">
                                    <Icon icon="mdi:account" class="h-4 w-4 text-primary" />
                                    Data Ayah
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <Label for="father_name" class="text-xs">Nama Lengkap Ayah</Label>
                                        <Input id="father_name" v-model="form.father_name" placeholder="Nama lengkap ayah" class="rounded-xl text-xs" />
                                        <p v-if="form.errors?.father_name" class="text-[11px] text-destructive">{{ form.errors.father_name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="father_phone" class="text-xs">No. HP / WA Ayah</Label>
                                        <Input id="father_phone" v-model="form.father_phone" placeholder="08xxxxxxxxxx" class="rounded-xl text-xs" />
                                        <p v-if="form.errors?.father_phone" class="text-[11px] text-destructive">{{ form.errors.father_phone }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="father_profession" class="text-xs">Profesi / Pekerjaan Ayah</Label>
                                        <select id="father_profession" v-model="form.father_profession" class="w-full rounded-xl border border-input bg-background px-3.5 py-2 text-xs ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="">— Pilih Profesi Ayah —</option>
                                            <option v-for="profesi in profesiOrtuOptions" :key="profesi" :value="profesi">{{ profesi }}</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="father_education" class="text-xs">Pendidikan Terakhir Ayah</Label>
                                        <select id="father_education" v-model="form.father_education" class="w-full rounded-xl border border-input bg-background px-3.5 py-2 text-xs ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="">— Pilih Pendidikan Ayah —</option>
                                            <option v-for="pendidikan in pendidikanOrtuOptions" :key="pendidikan" :value="pendidikan">{{ pendidikan }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Ibu -->
                            <div class="space-y-4 rounded-2xl border border-border/60 bg-muted/30 p-4 sm:p-5">
                                <h4 class="flex items-center gap-2 text-sm font-bold text-foreground">
                                    <Icon icon="mdi:account-heart-outline" class="h-4 w-4 text-rose-500" />
                                    Data Ibu
                                </h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-1.5">
                                        <Label for="mother_name" class="text-xs">Nama Lengkap Ibu</Label>
                                        <Input id="mother_name" v-model="form.mother_name" placeholder="Nama lengkap ibu" class="rounded-xl text-xs" />
                                        <p v-if="form.errors?.mother_name" class="text-[11px] text-destructive">{{ form.errors.mother_name }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="mother_phone" class="text-xs">No. HP / WA Ibu</Label>
                                        <Input id="mother_phone" v-model="form.mother_phone" placeholder="08xxxxxxxxxx" class="rounded-xl text-xs" />
                                        <p v-if="form.errors?.mother_phone" class="text-[11px] text-destructive">{{ form.errors.mother_phone }}</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="mother_profession" class="text-xs">Profesi / Pekerjaan Ibu</Label>
                                        <select id="mother_profession" v-model="form.mother_profession" class="w-full rounded-xl border border-input bg-background px-3.5 py-2 text-xs ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="">— Pilih Profesi Ibu —</option>
                                            <option v-for="profesi in profesiOrtuOptions" :key="profesi" :value="profesi">{{ profesi }}</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label for="mother_education" class="text-xs">Pendidikan Terakhir Ibu</Label>
                                        <select id="mother_education" v-model="form.mother_education" class="w-full rounded-xl border border-input bg-background px-3.5 py-2 text-xs ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring">
                                            <option value="">— Pilih Pendidikan Ibu —</option>
                                            <option v-for="pendidikan in pendidikanOrtuOptions" :key="pendidikan" :value="pendidikan">{{ pendidikan }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Aksi Simpan -->
                    <div class="ui-card anim-fade-in-up flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <p class="text-xs text-muted-foreground">Perubahan akan tersimpan setelah kamu menekan tombol simpan.</p>
                        <button type="button" class="btn-primary w-full px-8 py-3 sm:w-auto" :disabled="form.processing" @click="submit">
                            <span v-if="form.processing" class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                            <Icon v-else icon="mdi:content-save-outline" class="h-4 w-4" />
                            {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>

                <!-- ====== SIDEBAR ====== -->
                <div class="space-y-4">
                    <div class="ui-card anim-fade-in-up p-5 sm:p-6">
                        <h3 class="pb-3 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Ringkasan</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3 border-b border-border/20 py-1.5">
                                <dt class="text-muted-foreground">Kelas</dt>
                                <dd class="text-right font-bold text-foreground">{{ user?.student_class || '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-border/20 py-1.5">
                                <dt class="text-muted-foreground">Bidang</dt>
                                <dd class="text-right font-bold text-foreground">{{ user?.bidang || '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-border/20 py-1.5">
                                <dt class="text-muted-foreground">Level Berbakat</dt>
                                <dd class="text-right font-bold text-foreground">{{ user?.level || '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-border/20 py-1.5">
                                <dt class="shrink-0 text-muted-foreground">Tempat, Tgl Lahir</dt>
                                <dd class="text-right font-bold text-foreground">{{ birthLabel }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-border/20 py-1.5">
                                <dt class="shrink-0 text-muted-foreground">Profesi Orang Tua</dt>
                                <dd class="text-right font-bold text-foreground">{{ form.parent_profession || '-' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-1.5">
                                <dt class="shrink-0 text-muted-foreground">Pendidikan Orang Tua</dt>
                                <dd class="text-right font-bold text-foreground">{{ form.parent_education || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="ui-card anim-fade-in-up p-5 sm:p-6">
                        <span class="icon-box-sm mb-3 bg-amber-500/10 text-amber-600">
                            <Icon icon="mdi:shield-lock-outline" class="h-5 w-5" />
                        </span>
                        <h3 class="text-sm font-bold text-foreground">Keamanan Akun</h3>
                        <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Ubah password secara berkala agar akunmu tetap aman.</p>

                        <Link :href="route('user.profile.change-password')" class="btn-outline mt-4 w-full border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100">
                            <Icon icon="mdi:lock-reset" class="h-4 w-4" />
                            Ubah Password
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
