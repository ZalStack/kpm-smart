<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Pagination from '@/Components/shared/Pagination.vue';

const route = inject('route');

defineProps({
    packages: { type: Object, required: true },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    userBidangGroup: { type: String, default: null },
    userBidangRaw: { type: String, default: '' },
    currentSort: { type: String, default: 'latest' },
    filters: { type: Object, default: () => ({}) },
});

/**
 * Warna otomatis per pekan (pekan 1 merah, pekan 2 kuning, dst).
 * Deterministik berdasarkan urutan pekan agar tidak berubah tiap muat ulang.
 */
const pekanColors = [
    'from-red-500 to-rose-700',
    'from-amber-400 to-yellow-500',
    'from-emerald-500 to-teal-600',
    'from-sky-500 to-blue-600',
    'from-violet-500 to-purple-600',
    'from-orange-500 to-amber-600',
    'from-fuchsia-500 to-pink-600',
    'from-cyan-500 to-teal-700',
    'from-indigo-500 to-indigo-700',
    'from-lime-500 to-green-600',
    'from-purple-500 to-fuchsia-600',
    'from-rose-500 to-red-600',
];

const packageIcons = [
    'mdi:book-open-page-variant',
    'mdi:target',
    'mdi:lightbulb-outline',
    'mdi:rocket-launch',
    'mdi:trophy',
    'mdi:flash',
];

function getColorClass(index) {
    return pekanColors[index % pekanColors.length];
}

function getIcon(index) {
    return packageIcons[index % packageIcons.length];
}

function getTotalCards(pkg) {
    return pkg.cards ? pkg.cards.length : 0;
}

function getTotalQuestions(pkg) {
    return pkg.questions ? pkg.questions.length : 0;
}

const statusMeta = {
    active: { label: 'Sedang Aktif', cls: 'bg-emerald-500/95 text-white', icon: 'mdi:play-circle' },
    upcoming: { label: 'Akan Datang', cls: 'bg-amber-500/95 text-white', icon: 'mdi:clock-outline' },
    expired: { label: 'Berakhir', cls: 'bg-slate-700/95 text-white', icon: 'mdi:close-circle-outline' },
};
</script>

<template>
    <UserLayout>
        <Head title="PR Siswa Kelas Khusus - KPM SMART" />

        <template #header-title>PR Siswa Kelas Khusus</template>
        <template #header-sub>Daftar tugas PR pekanan yang wajib dikerjakan sesuai bidang studi dan kelasmu.</template>
        <template #header-actions>
            <span class="ui-card-flat flex items-center gap-2 px-4 py-2.5 text-sm">
                <span class="font-extrabold text-primary">{{ packages.total }}</span>
                <span class="text-muted-foreground">PR Tersedia</span>
            </span>
        </template>

        <!-- Banner bidang terkunci -->
        <div
            v-if="userBidangGroup"
            class="anim-fade-in-up mb-6 flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5"
            :class="userBidangGroup === 'Matematika'
                ? 'border-blue-200 bg-gradient-to-r from-blue-50 to-card'
                : 'border-violet-200 bg-gradient-to-r from-violet-50 to-card'"
        >
            <div class="flex items-start gap-3 sm:items-center">
                <span class="icon-box shrink-0 text-white" :class="userBidangGroup === 'Matematika' ? 'bg-blue-600' : 'bg-violet-600'">
                    <Icon :icon="userBidangGroup === 'Matematika' ? 'mdi:calculator-variant' : 'mdi:flask-outline'" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-bold text-foreground">
                        Tersortir otomatis untuk bidang
                        <span class="underline decoration-2 underline-offset-2">{{ userBidangGroup }}</span>
                    </p>
                    <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">
                        Berdasarkan data profil siswa ({{ userBidangRaw || userBidangGroup }}), sistem hanya menampilkan tugas PR {{ userBidangGroup }}.
                    </p>
                </div>
            </div>
            <span class="chip shrink-0 self-start border border-current/20 bg-white/80 text-muted-foreground sm:self-auto">
                <Icon icon="mdi:lock-outline" class="h-3.5 w-3.5" />
                Terkunci Sesuai Bidang
            </span>
        </div>

        <!-- Grid Paket -->
        <div v-if="packages.data.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="(pkg, index) in packages.data"
                :key="pkg.id"
                :style="{ animationDelay: (index * 60) + 'ms' }"
                class="ui-card anim-fade-in-up group flex flex-col overflow-hidden opacity-0 [animation-fill-mode:forwards]"
            >
                <!-- Gambar / Banner -->
                <div class="relative h-36 overflow-hidden sm:h-40">
                    <img
                        v-if="pkg.thumbnail"
                        :src="'/storage/' + pkg.thumbnail"
                        :alt="pkg.title"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div v-else :class="['relative flex h-full w-full items-center justify-center overflow-hidden bg-gradient-to-br', getColorClass(index)]">
                        <div class="grid-dots absolute inset-0 opacity-20"></div>
                        <span class="relative z-10 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/25 bg-white/20 text-white shadow-sm backdrop-blur transition-transform duration-300 group-hover:scale-110">
                            <Icon :icon="getIcon(index)" class="h-7 w-7" />
                        </span>
                    </div>

                    <!-- Status jadwal -->
                    <span :class="['chip absolute left-3 top-3 shadow-sm', statusMeta[pkg.schedule_status]?.cls]">
                        <Icon
                            :icon="statusMeta[pkg.schedule_status]?.icon || 'mdi:information-outline'"
                            class="h-3.5 w-3.5"
                            :class="{ 'animate-pulse': pkg.schedule_status === 'active' }"
                        />
                        {{ statusMeta[pkg.schedule_status]?.label }}
                    </span>

                    <span class="chip absolute right-3 top-3 border border-white/20 bg-black/40 text-white backdrop-blur">
                        {{ getTotalCards(pkg) }} Kartu
                    </span>
                </div>

                <!-- Isi Kartu -->
                <div class="flex flex-1 flex-col p-5">
                    <div class="mb-2.5 flex flex-wrap gap-1.5">
                        <span v-if="pkg.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">{{ pkg.bidang }}</span>
                        <span v-if="pkg.level" class="chip border border-indigo-200 bg-indigo-50 text-indigo-700">Level {{ pkg.level }}</span>
                        <span v-if="pkg.kelas" class="chip border border-amber-200 bg-amber-50 text-amber-700">Kelas {{ pkg.kelas }}</span>
                    </div>

                    <h3 class="line-clamp-1 text-base font-bold text-foreground transition-colors group-hover:text-primary">
                        {{ pkg.title }}
                    </h3>

                    <p class="mt-1.5 line-clamp-2 flex-1 text-sm leading-relaxed text-muted-foreground">
                        {{ pkg.description || 'Tidak ada deskripsi tambahan.' }}
                    </p>

                    <div class="mt-4 flex items-center justify-between border-t border-border/60 pt-3 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1.5 font-medium">
                            <Icon icon="mdi:help-circle-outline" class="h-4 w-4 text-primary" />
                            {{ getTotalQuestions(pkg) }} Butir Soal
                        </span>
                        <span v-if="pkg.schedule_label" class="truncate pl-2 text-right text-[11px]">{{ pkg.schedule_label }}</span>
                    </div>

                    <div class="mt-4">
                        <span
                            v-if="pkg.schedule_status === 'expired'"
                            class="flex w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl bg-muted py-2.5 text-xs font-bold text-muted-foreground"
                        >
                            <Icon icon="mdi:close-octagon" class="h-4 w-4" />
                            Jadwal Berakhir
                        </span>
                        <span
                            v-else-if="pkg.schedule_status === 'upcoming'"
                            class="flex w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 py-2.5 text-xs font-bold text-amber-700"
                        >
                            <Icon icon="mdi:clock-outline" class="h-4 w-4" />
                            Belum Dimulai
                        </span>
                        <Link
                            v-else
                            :href="route('user.packages.show', pkg.id)"
                            class="btn-primary w-full py-2.5 text-xs"
                        >
                            <Icon icon="mdi:book-open-page-variant" class="h-4 w-4" />
                            Buka PR
                        </Link>
                    </div>
                </div>
            </article>
        </div>

        <!-- Keadaan Kosong -->
        <div v-else class="empty-state anim-fade-in-up">
            <div class="empty-state-icon">
                <Icon icon="mdi:folder-open-outline" class="h-8 w-8" />
            </div>
            <h3 class="text-base font-bold text-foreground">Belum Ada PR Siswa Kelas Khusus</h3>
            <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                {{ userBidangGroup ? `Saat ini belum ada tugas PR ${userBidangGroup} yang tersedia untukmu. Silakan kembali lagi sesuai jadwal.` : 'Saat ini belum ada tugas PR yang tersedia. Silakan kembali lagi sesuai jadwal.' }}
            </p>
            <Link :href="route('user.dashboard')" class="btn-outline mt-5 px-5 py-2.5 text-xs">
                Kembali ke Beranda
            </Link>
        </div>

        <Pagination :links="packages.links" />
    </UserLayout>
</template>
