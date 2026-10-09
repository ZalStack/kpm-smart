<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    packages: { type: Object, required: true },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const kelas = ref(props.filters?.kelas || '');
const bidang = ref(props.filters?.bidang || '');
const level = ref(props.filters?.level || '');
const deleteDialog = ref({ open: false, package: null });

const deleteDialogMessage = computed(() => `Apakah Anda yakin ingin menghapus soal '${deleteDialog.value.package?.title || ''}'?`);

function applyFilters() {
    router.get(route('admin.packages.index'), {
        search: search.value,
        status: status.value,
        kelas: kelas.value,
        bidang: bidang.value,
        level: level.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    status.value = '';
    kelas.value = '';
    bidang.value = '';
    level.value = '';
    applyFilters();
}

function confirmDelete(pkg) {
    deleteDialog.value = { open: true, package: pkg };
}

function doDelete() {
    if (deleteDialog.value.package) {
        router.delete(route('admin.packages.destroy', deleteDialog.value.package.id), {
            onSuccess: () => {
                deleteDialog.value = { open: false, package: null };
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Paket Soal - Admin" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:package-variant-closed" class="w-5 h-5" />
                </span>
                <span>Bank Soal Tugas</span>
            </div>
        </template>
        <template #header-sub>
            Kelola paket soal tugas, jadwal pengerjaan, kunci jawaban, dan pembahasan materi
        </template>

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Total Paket</p>
                        <p class="text-2xl sm:text-3xl font-black text-foreground mt-1">{{ stats.total || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <Icon icon="mdi:package-variant" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Paket Aktif</p>
                        <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ stats.active || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <Icon icon="mdi:check-circle-outline" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Nonaktif</p>
                        <p class="text-2xl sm:text-3xl font-black text-destructive mt-1">{{ stats.inactive || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-destructive/10 text-destructive flex items-center justify-center">
                        <Icon icon="mdi:close-circle-outline" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Total Soal</p>
                        <p class="text-2xl sm:text-3xl font-black text-primary mt-1">{{ stats.totalQuestions || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <Icon icon="mdi:help-circle-outline" class="w-6 h-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-sm border border-border/60 mb-6 space-y-4">
            <form @submit.prevent="applyFilters" class="space-y-3">
                <!-- Search row -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Icon icon="mdi:magnify" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                        <Input v-model="search" placeholder="Cari judul soal atau deskripsi..." class="pl-10 h-10 rounded-xl" />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button type="submit" size="sm" class="gap-1.5 h-10 px-4 font-semibold shadow-sm">
                            <Icon icon="mdi:filter-variant" class="w-4 h-4" /> Cari
                        </Button>
                        <Button type="button" variant="outline" size="sm" @click="resetFilters" class="h-10 px-3 font-semibold">
                            Reset
                        </Button>
                        <Link :href="route('admin.packages.create')">
                            <Button size="sm" class="gap-1.5 h-10 px-4 font-semibold bg-primary shadow-sm">
                                <Icon icon="mdi:plus" class="w-4 h-4" /> Tambah Soal
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-border/40">
                    <Select v-model="status" class="h-9 text-xs rounded-xl">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </Select>
                    <Select v-model="bidang" class="h-9 text-xs rounded-xl">
                        <option value="">Semua Bidang</option>
                        <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                    </Select>
                    <Select v-model="level" class="h-9 text-xs rounded-xl">
                        <option value="">Semua Level</option>
                        <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                    </Select>
                    <Select v-model="kelas" class="h-9 text-xs rounded-xl">
                        <option value="">Semua Kelas</option>
                        <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                    </Select>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-2xl shadow-sm border border-border/60 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/50 bg-muted/20 text-xs font-bold uppercase tracking-wider text-muted-foreground">
                            <th class="text-left px-5 py-3.5">#</th>
                            <th class="text-left px-5 py-3.5">Judul Paket Soal</th>
                            <th class="text-left px-5 py-3.5">Bidang / Level</th>
                            <th class="text-left px-5 py-3.5">Kelas</th>
                            <th class="text-left px-5 py-3.5">Status</th>
                            <th class="text-center px-5 py-3.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        <tr v-if="packages.data.length === 0">
                            <td colspan="6" class="px-5 py-16 text-center text-muted-foreground">
                                <div class="w-16 h-16 rounded-2xl bg-muted/70 flex items-center justify-center mx-auto mb-3 text-muted-foreground">
                                    <Icon icon="mdi:package-variant-remove" class="w-8 h-8" />
                                </div>
                                <p class="font-bold text-foreground">Tidak ada paket soal ditemukan</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Coba ubah filter atau buat paket soal baru.</p>
                            </td>
                        </tr>

                        <tr v-for="(pkg, idx) in packages.data" :key="pkg.id"
                            class="hover:bg-muted/30 transition-colors group">
                            <td class="px-5 py-4 text-xs text-muted-foreground font-mono">{{ packages.from + idx }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-sm flex-shrink-0 overflow-hidden border border-border/60">
                                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" class="w-full h-full object-cover" />
                                        <Icon v-else icon="mdi:package-variant" class="w-6 h-6 text-primary" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-foreground truncate max-w-xs group-hover:text-primary transition-colors">{{ pkg.title }}</p>
                                        <p class="text-xs text-muted-foreground truncate max-w-xs mt-0.5">{{ pkg.description || 'Tanpa deskripsi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="pkg.bidang" class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-primary/10 text-primary">
                                        {{ pkg.bidang }}
                                    </span>
                                    <span v-if="pkg.level" class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                        {{ pkg.level }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-foreground">
                                {{ pkg.kelas || '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border', pkg.is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200']">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="pkg.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.packages.detail', pkg.id)"
                                          class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition"
                                          title="Lihat Detail">
                                        <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                    </Link>
                                    <Link :href="route('admin.packages.edit.informasi', pkg.id)"
                                          class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition"
                                          title="Edit Informasi">
                                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                    </Link>
                                    <Link :href="route('admin.packages.edit.questions', pkg.id)"
                                          class="p-2 rounded-lg text-muted-foreground hover:text-primary hover:bg-primary/10 transition"
                                          title="Kelola Soal">
                                        <Icon icon="mdi:help-circle-outline" class="w-4 h-4" />
                                    </Link>
                                    <button type="button" @click="confirmDelete(pkg)"
                                            class="p-2 rounded-lg text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition"
                                            title="Hapus Paket">
                                        <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3 mb-6">
            <div v-if="packages.data.length === 0" class="text-center py-16 bg-card rounded-2xl shadow-sm border p-6">
                <Icon icon="mdi:package-variant-remove" class="w-10 h-10 text-muted-foreground mx-auto mb-2" />
                <p class="font-bold text-foreground">Tidak ada paket soal ditemukan</p>
            </div>

            <div v-for="pkg in packages.data" :key="pkg.id"
                 class="bg-card rounded-2xl border border-border/60 p-4 shadow-sm space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0 overflow-hidden border">
                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" class="w-full h-full object-cover" />
                        <Icon v-else icon="mdi:package-variant" class="w-6 h-6 text-primary" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-sm text-foreground truncate">{{ pkg.title }}</h4>
                        <p class="text-xs text-muted-foreground line-clamp-1 mt-0.5">{{ pkg.description || '-' }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <span v-if="pkg.bidang" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary">{{ pkg.bidang }}</span>
                            <span v-if="pkg.level" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-600">{{ pkg.level }}</span>
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-bold', pkg.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700']">
                                {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-border/40">
                    <Link :href="route('admin.packages.detail', pkg.id)" class="text-center py-2 rounded-xl bg-muted text-xs font-semibold hover:bg-muted/80 transition">Detail</Link>
                    <Link :href="route('admin.packages.edit.informasi', pkg.id)" class="text-center py-2 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition">Edit</Link>
                    <button type="button" @click="confirmDelete(pkg)" class="py-2 rounded-xl bg-destructive/10 text-destructive text-xs font-semibold hover:bg-destructive/20 transition">Hapus</button>
                </div>
            </div>
        </div>

        <Pagination :links="packages.links" />

        <ConfirmDialog
            :open="deleteDialog.open"
            title="Hapus Paket Soal"
            :message="deleteDialogMessage"
            confirm-text="Ya, Hapus"
            @update:open="deleteDialog.open = $event"
            @confirm="doDelete"
        />
    </AdminLayout>
</template>
