<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Card from '@/Components/ui/card/Card.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

function toggleActive() {
    if (confirm('Yakin ingin mengubah status akun ini?')) {
        router.post(route('admin.users.toggle-active', props.user.id));
    }
}

function deleteUser() {
    if (confirm('Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="user.name + ' - Detail User'" />

        <template #header-title>Detail User</template>
        <template #header-sub>Informasi lengkap mengenai pengguna</template>

        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar User
        </Link>

        <!-- Profile Header -->
        <div class="bg-card rounded-xl border shadow-sm overflow-hidden mb-6">
            <div class="h-32 bg-gradient-to-br from-[#344e41] to-primary relative">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent)]"></div>
            </div>
            <div class="px-6 pb-6 -mt-12 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                    <div class="w-24 h-24 rounded-full border-4 border-card overflow-hidden bg-primary/10 flex items-center justify-center shadow-lg">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-3xl font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                        <p class="text-muted-foreground text-sm">{{ user.email }}</p>
                        <Badge :variant="user.is_active ? 'success' : 'destructive'" class="mt-1">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                    </div>
                    <div class="flex gap-2" @click.stop>
                        <Link :href="route('admin.users.edit', user.id)" class="inline-flex items-center justify-center bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition">✏️ Edit</Link>
                        <Button variant="outline" size="sm" @click="toggleActive">{{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</Button>
                        <Button variant="destructive" size="sm" @click="deleteUser">Hapus</Button>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground mt-3 flex items-center gap-1">📅 Terdaftar sejak {{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}</p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Data Siswa -->
            <Card class="p-5">
                <h3 class="font-semibold mb-4">🎓 Data Siswa</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Kelas</span><span class="font-medium">{{ user.student_class || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Bidang</span><span class="font-medium">{{ user.bidang || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Level</span><span class="font-medium">{{ user.level || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Sekolah</span><span class="font-medium">{{ user.school_name || '-' }}</span></div>
                </div>
            </Card>

            <!-- Info Kontak -->
            <Card class="p-5">
                <h3 class="font-semibold mb-4">📱 Info Kontak</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">No. HP/WA</span><span class="font-medium">{{ user.phone || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Jenis Kelamin</span><span class="font-medium">{{ user.gender || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Agama</span><span class="font-medium">{{ user.religion || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Alamat</span><span class="font-medium text-right max-w-[200px]">{{ user.address || '-' }}</span></div>
                </div>
            </Card>

            <!-- Info Tambahan -->
            <Card class="p-5">
                <h3 class="font-semibold mb-4">ℹ️ Info Tambahan</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Role</span><Badge variant="outline">{{ user.role }}</Badge></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Status</span><Badge :variant="user.is_active ? 'success' : 'destructive'">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</Badge></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Terdaftar Sejak</span><span class="font-medium">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Terakhir Diperbarui</span><span class="font-medium">{{ user.updated_at ? new Date(user.updated_at).toLocaleDateString('id-ID') : '-' }}</span></div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
