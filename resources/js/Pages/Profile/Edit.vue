<script setup>
import { inject,  ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Select from '@/Components/ui/select/Select.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
const route = inject('route');

const page = usePage();
const user = page.props.auth?.user;

const form = useForm({
    name: user?.name || '',
    phone: user?.phone || '',
    student_class: user?.student_class || '',
    bidang: user?.bidang || '',
    level: user?.level || '',
    school_name: user?.school_name || '',
    address: user?.address || '',
    gender: user?.gender || '',
    religion: user?.religion || '',
});

function submit() {
    form.put(route('profile.update'));
}
</script>

<template>
    <UserLayout>
        <Head title="Profil Saya - KPM Belajar Online" />

        <template #header-title>Profil Saya</template>
        <template #header-sub>Perbarui informasi profil Anda</template>

        <div class="max-w-2xl">
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold">Informasi Profil</h2>
                    <p class="text-sm text-muted-foreground mt-1">Perbarui informasi profil Anda di sini.</p>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap</Label>
                            <Input id="name" v-model="form.name" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">Nomor Telepon</Label>
                            <Input id="phone" v-model="form.phone" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="student_class">Kelas</Label>
                            <Input id="student_class" v-model="form.student_class" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="school_name">Nama Sekolah</Label>
                            <Input id="school_name" v-model="form.school_name" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="bidang">Bidang</Label>
                            <Input id="bidang" v-model="form.bidang" />
                        </div>
                        <div class="space-y-2">
                            <Label for="level">Level</Label>
                            <Input id="level" v-model="form.level" />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">Jenis Kelamin</Label>
                            <Select id="gender" v-model="form.gender">
                                <option value="">Pilih</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="religion">Agama</Label>
                            <Select id="religion" v-model="form.religion">
                                <option value="">Pilih</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="address">Alamat</Label>
                        <Textarea id="address" v-model="form.address" :rows="3" />
                    </div>

                    <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md text-sm">
                        <ul class="space-y-1">
                            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </UserLayout>
</template>
