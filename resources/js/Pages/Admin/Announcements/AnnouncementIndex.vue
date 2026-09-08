<script setup>
import { ref, inject } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/shared/Pagination.vue';

const route = inject('route');
const page = usePage();

const props = defineProps({
    announcements: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const showCreateModal = ref(false);
const deletingId = ref(null);

const form = useForm({
    title: '',
    content: '',
    priority: 'normal',
});

function doSearch() {
    router.get(route('admin.announcements.index'), { search: search.value }, { preserveState: true, replace: true });
}

function submit() {
    form.post(route('admin.announcements.store'), {
        onSuccess: () => {
            form.reset();
            showCreateModal.value = false;
        },
    });
}

function confirmDelete(id) {
    deletingId.value = id;
}

function deleteAnnouncement(id) {
    router.delete(route('admin.announcements.destroy', id), {
        onSuccess: () => deletingId.value = null,
    });
}

function getPriorityConfig(p) {
    const map = {
        low: { label: 'Rendah', color: 'bg-slate-100 text-slate-700 ring-slate-200', dot: 'bg-slate-400', icon: 'mdi:flag-outline' },
        normal: { label: 'Normal', color: 'bg-blue-50 text-blue-700 ring-blue-200', dot: 'bg-blue-500', icon: 'mdi:bell-outline' },
        high: { label: 'Penting', color: 'bg-amber-50 text-amber-700 ring-amber-200', dot: 'bg-amber-500', icon: 'mdi:alert-outline' },
        urgent: { label: 'Mendesak', color: 'bg-red-50 text-red-700 ring-red-200', dot: 'bg-red-500', icon: 'mdi:fire' },
    };
    return map[p] || map.normal;
}

function formatDate(d) {
    if (!d) return '';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Pengumuman - Admin" />

        <template #header-title>Pengumuman</template>
        <template #header-sub>Kelola pengumuman untuk siswa — otomatis masuk ke notifikasi</template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-card border rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Total Pengumuman</p>
                        <p class="text-2xl font-bold mt-1">{{ stats.total || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-white shadow-sm">
                        <Icon icon="mdi:bullhorn-outline" class="w-5 h-5" />
                    </div>
                </div>
            </div>
            <div class="bg-card border rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-muted-foreground font-medium">Aktif</p>
                        <p class="text-2xl font-bold mt-1 text-emerald-600">{{ stats.active || 0 }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 ring-1 ring-emerald-200">
                        <Icon icon="mdi:check-circle-outline" class="w-5 h-5" />
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-primary to-primary/80 rounded-2xl p-5 text-white shadow-md hidden lg:flex flex-col justify-center">
                <p class="text-xs text-white/70 font-medium">Tips</p>
                <p class="text-sm font-medium mt-1 leading-snug">Pengumuman akan dikirim sebagai notifikasi ke semua siswa & bisa diklik untuk detail.</p>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between mb-6">
            <div class="relative flex-1 sm:max-w-sm">
                <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <input v-model="search" @keyup.enter="doSearch" placeholder="Cari pengumuman..." class="w-full pl-9 pr-4 py-2.5 bg-card border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
            </div>
            <div class="flex gap-2">
                <button @click="doSearch" class="inline-flex items-center gap-2 px-4 py-2.5 bg-card border rounded-xl text-sm font-medium hover:bg-accent transition">
                    <Icon icon="mdi:magnify" class="w-4 h-4" /> Cari
                </button>
                <button @click="showCreateModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-semibold hover:bg-primary/90 shadow-sm hover:shadow-md transition-all active:scale-[0.97]">
                    <Icon icon="mdi:plus" class="w-4 h-4" /> Buat Pengumuman
                </button>
            </div>
        </div>

        <!-- Announcement List -->
        <div v-if="announcements.data.length === 0" class="bg-card border rounded-2xl p-12 text-center shadow-sm">
            <div class="w-20 h-20 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-4">
                <Icon icon="mdi:bullhorn-outline" class="w-10 h-10 text-muted-foreground/40" />
            </div>
            <h3 class="font-semibold text-lg">Belum Ada Pengumuman</h3>
            <p class="text-sm text-muted-foreground mt-1 max-w-md mx-auto">Buat pengumuman pertama untuk memberi informasi penting kepada siswa. Notifikasi akan otomatis muncul di panel mereka.</p>
            <button @click="showCreateModal = true" class="mt-5 inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Buat Sekarang</button>
        </div>

        <div v-else class="space-y-3">
            <div v-for="(a, idx) in announcements.data" :key="a.id" :style="{ animationDelay: idx*40+'ms' }" class="group bg-card border rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-primary/20 transition-all duration-300 anim-fade-in-up">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center flex-shrink-0 ring-1 ring-primary/10">
                        <Icon icon="mdi:bullhorn" class="w-5 h-5 text-primary" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <h3 class="font-semibold text-sm md:text-base leading-tight pr-2">{{ a.title }}</h3>
                            <span :class="['inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full ring-1', getPriorityConfig(a.priority).color]">
                                <span :class="['w-1.5 h-1.5 rounded-full', getPriorityConfig(a.priority).dot]"></span>
                                {{ getPriorityConfig(a.priority).label }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground mt-2 line-clamp-3 leading-relaxed">{{ a.content }}</p>
                        <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-muted-foreground">
                            <span class="inline-flex items-center gap-1"><Icon icon="mdi:account-outline" class="w-3.5 h-3.5" /> {{ a.creator?.name || 'Admin' }}</span>
                            <span class="inline-flex items-center gap-1"><Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" /> {{ formatDate(a.created_at) }}</span>
                            <span :class="['inline-flex items-center gap-1 font-medium', a.is_active ? 'text-emerald-600' : 'text-slate-400']">
                                <Icon :icon="a.is_active ? 'mdi:eye-outline' : 'mdi:eye-off-outline'" class="w-3.5 h-3.5" /> {{ a.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t">
                    <Link :href="route('admin.announcements.show', a.id)" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 bg-accent hover:bg-accent/80 rounded-lg transition">
                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" /> Lihat
                    </Link>
                    <button @click="router.post(route('admin.announcements.toggle', a.id))" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 bg-card border hover:bg-accent rounded-lg transition">
                        <Icon :icon="a.is_active ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-3.5 h-3.5" /> {{ a.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                    <button v-if="deletingId !== a.id" @click="confirmDelete(a.id)" class="ml-auto inline-flex items-center gap-1.5 text-xs font-medium text-destructive hover:bg-destructive/10 px-3 py-1.5 rounded-lg transition">
                        <Icon icon="mdi:trash-can-outline" class="w-3.5 h-3.5" /> Hapus
                    </button>
                    <div v-else class="ml-auto flex items-center gap-2">
                        <button @click="deletingId=null" class="text-xs px-3 py-1.5 rounded-lg bg-card border hover:bg-accent transition">Batal</button>
                        <button @click="deleteAnnouncement(a.id)" class="text-xs px-3 py-1.5 rounded-lg bg-destructive text-destructive-foreground hover:bg-destructive/90 transition font-semibold">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="announcements.links" />
        </div>

        <!-- Create Modal -->
        <Transition name="modal">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCreateModal=false"></div>
                <div class="relative w-full max-w-lg bg-card rounded-2xl shadow-2xl border max-h-[90vh] flex flex-col overflow-hidden">
                    <div class="px-6 py-5 border-b bg-gradient-to-r from-primary/5 to-transparent">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-sm">
                                    <Icon icon="mdi:bullhorn" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-base">Buat Pengumuman Baru</h3>
                                    <p class="text-xs text-muted-foreground">Akan dikirim ke semua siswa via notifikasi</p>
                                </div>
                            </div>
                            <button @click="showCreateModal=false" class="w-8 h-8 rounded-lg hover:bg-accent flex items-center justify-center transition">
                                <Icon icon="mdi:close" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <div>
                            <label class="text-sm font-semibold mb-1.5 block">Judul Pengumuman <span class="text-destructive">*</span></label>
                            <input v-model="form.title" type="text" placeholder="Contoh: Ujian Akhir Semester Minggu Depan" class="w-full px-4 py-2.5 bg-background border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                            <p v-if="form.errors.title" class="text-xs text-destructive mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold mb-1.5 block">Prioritas</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" v-for="p in ['low','normal','high','urgent']" :key="p" @click="form.priority=p" :class="['px-3 py-2.5 rounded-xl text-xs font-semibold border-2 transition-all', form.priority===p ? 'border-primary bg-primary/5 text-primary' : 'border-border bg-card hover:border-primary/30']">
                                    {{ p==='low' ? 'Rendah' : p==='normal' ? 'Normal' : p==='high' ? 'Penting' : 'Mendesak' }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-semibold mb-1.5 block">Isi Pengumuman <span class="text-destructive">*</span></label>
                            <textarea v-model="form.content" rows="5" placeholder="Tulis isi pengumuman dengan jelas..." class="w-full px-4 py-3 bg-background border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none"></textarea>
                            <div class="flex justify-between mt-1">
                                <p v-if="form.errors.content" class="text-xs text-destructive">{{ form.errors.content }}</p>
                                <p class="text-xs text-muted-foreground ml-auto">{{ form.content.length }}/5000</p>
                            </div>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2.5">
                            <Icon icon="mdi:information-outline" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                            <p class="text-xs text-amber-800 leading-relaxed">Pengumuman akan langsung masuk ke <b>Notifikasi</b> semua siswa aktif dan bisa diklik untuk melihat detail lengkap.</p>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showCreateModal=false" class="flex-1 px-4 py-2.5 bg-card border rounded-xl text-sm font-semibold hover:bg-accent transition">Batal</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-bold hover:bg-primary/90 shadow-sm disabled:opacity-50 transition flex items-center justify-center gap-2">
                                <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                <template v-else><Icon icon="mdi:send" class="w-4 h-4" /> Kirim Pengumuman</template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
.modal-enter-active { transition: all 0.3s cubic-bezier(0.16,1,0.3,1); }
.modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from { opacity:0; }
.modal-enter-from > div:last-child { transform: scale(0.95) translateY(8px); }
.modal-leave-to { opacity:0; }
</style>
