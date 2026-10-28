<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Pagination from '@/Components/shared/Pagination.vue';

const route = inject('route');

const props = defineProps({
    packages: { type: Object, required: true },
    packageStatuses: { type: Object, default: () => ({}) },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    userBidangGroup: { type: String, default: null },
    userBidangRaw: { type: String, default: '' },
    currentSort: { type: String, default: 'latest' },
    filters: { type: Object, default: () => ({}) },
});

/**
 * Warna otomatis per pekan (pekan 1 merah, pekan 2 kuning, begitupun seterusnya auto random/deterministik).
 */
const pekanColors = [
    'from-red-500 to-rose-700',      // Pekan 1: merah
    'from-amber-400 to-yellow-500',  // Pekan 2: kuning
    'from-emerald-500 to-teal-600',  // Pekan 3: hijau
    'from-sky-500 to-blue-600',      // Pekan 4: biru
    'from-violet-500 to-purple-600', // Pekan 5: ungu
    'from-orange-500 to-amber-600',  // Pekan 6: oranye
    'from-fuchsia-500 to-pink-600',  // Pekan 7: pink/fuchsia
    'from-cyan-500 to-teal-700',     // Pekan 8: cyan
    'from-indigo-500 to-indigo-700', // Pekan 9: indigo
    'from-lime-500 to-green-600',    // Pekan 10: lime
    'from-purple-500 to-fuchsia-600',// Pekan 11
    'from-rose-500 to-red-600',      // Pekan 12
];

const packageIcons = [
    'mdi:book-open-page-variant',
    'mdi:target',
    'mdi:lightbulb-outline',
    'mdi:rocket-launch',
    'mdi:trophy',
    'mdi:flash',
];

function getColorClass(index, pkg) {
    if (pkg && pkg.title) {
        const match = pkg.title.match(/pekan\s*(\d+)/i);
        if (match) {
            const weekNum = parseInt(match[1], 10);
            if (weekNum > 0) {
                return pekanColors[(weekNum - 1) % pekanColors.length];
            }
        }
    }
    return pekanColors[index % pekanColors.length];
}

function getIcon(index, pkg) {
    if (pkg && pkg.title) {
        const match = pkg.title.match(/pekan\s*(\d+)/i);
        if (match) {
            const weekNum = parseInt(match[1], 10);
            if (weekNum > 0) {
                return packageIcons[(weekNum - 1) % packageIcons.length];
            }
        }
    }
    return packageIcons[index % packageIcons.length];
}

const statusMeta = {
    active: { label: 'Sedang Aktif', cls: 'bg-emerald-500/95 text-white', icon: 'mdi:play-circle' },
    upcoming: { label: 'Akan Datang', cls: 'bg-amber-500/95 text-white', icon: 'mdi:clock-outline' },
    expired: { label: 'Berakhir', cls: 'bg-slate-700/95 text-white', icon: 'mdi:close-circle-outline' },
    no_limit: { label: 'Tanpa Batas', cls: 'bg-indigo-500/95 text-white', icon: 'mdi:infinity' },
};

/* ======================================================================== */
/*  STATUS PENGERJAAN & AKSI LANGSUNG KE SOAL                               */
/*  Klik kartu = langsung masuk bagian soal (tanpa halaman perantara).       */
/*  PR pekanan hanya boleh dikerjakan 1 kali per siswa.                      */
/* ======================================================================== */

const startingId = ref(null);

function attemptInfo(pkg) {
    return props.packageStatuses?.[pkg.id] || { status: 'none', session_id: null };
}

function attemptStatus(pkg) {
    return attemptInfo(pkg).status || 'none';
}

function isLocked(pkg) {
    return pkg.schedule_status === 'expired' || pkg.schedule_status === 'upcoming';
}

/**
 * Aksi utama kartu PR:
 *   completed   -> lihat hasil (pengerjaan 1x sudah selesai)
 *   in_progress -> lanjutkan sesi yang sama
 *   none        -> langsung masuk ke bagian soal
 */
function openPackage(pkg) {
    if (isLocked(pkg) || startingId.value) return;

    const info = attemptInfo(pkg);

    if ((info.status === 'completed' || info.status === 'in_progress') && info.session_id) {
        router.visit(route('user.practice.show', info.session_id));
        return;
    }

    startingId.value = pkg.id;
    router.post(route('user.practice.start', pkg.id), {}, {
        onFinish: () => {
            startingId.value = null;
        },
    });
}

function ctaLabel(pkg) {
    if (pkg.schedule_status === 'expired') return 'Jadwal Berakhir';
    if (pkg.schedule_status === 'upcoming') return 'Belum Dimulai';

    const status = attemptStatus(pkg);
    if (status === 'completed') return 'Lihat Hasil';
    if (status === 'in_progress') return 'Lanjutkan';
    if (startingId.value === pkg.id) return 'Membuka Soal...';
    return 'Kerjakan Sekarang';
}

function ctaIcon(pkg) {
    if (pkg.schedule_status === 'expired') return 'mdi:close-octagon';
    if (pkg.schedule_status === 'upcoming') return 'mdi:clock-outline';

    const status = attemptStatus(pkg);
    if (status === 'completed') return 'mdi:chart-bar';
    if (status === 'in_progress') return 'mdi:play';
    if (startingId.value === pkg.id) return 'mdi:loading';
    return 'mdi:pencil-outline';
}
</script>

<template>
    <UserLayout>
        <Head title="PR Siswa Kelas Khusus - KPM SMART" />

        <template #header-title>PR Siswa Kelas Khusus</template>
        <template #header-sub>Klik salah satu kartu PR untuk langsung masuk ke bagian soal dan kerjakan sekarang.</template>
        <template #header-actions>
            <span class="ui-card-flat flex items-center gap-2 px-4 py-2.5 text-sm">
                <span class="font-extrabold text-primary">{{ packages.total }}</span>
                <span class="text-muted-foreground">PR Tersedia</span>
            </span>
        </template>

        <!-- Grid Paket (Langsung Muncul Card Tugas Yang Tersedia) -->
        <div v-if="packages.data.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="(pkg, index) in packages.data"
                :key="pkg.id"
                :style="{ animationDelay: (index * 60) + 'ms' }"
                class="ui-card anim-fade-in-up group relative flex flex-col overflow-hidden opacity-0 transition-all duration-300 [animation-fill-mode:forwards]"
                :class="[
                    isLocked(pkg)
                        ? 'cursor-not-allowed opacity-80'
                        : 'cursor-pointer hover:-translate-y-1 hover:border-primary/40 hover:shadow-[0_18px_40px_-18px_rgba(37,99,235,0.45)]',
                ]"
                :role="isLocked(pkg) ? undefined : 'button'"
                :tabindex="isLocked(pkg) ? undefined : 0"
                :aria-label="isLocked(pkg) ? undefined : 'Kerjakan PR ' + pkg.title"
                @click="openPackage(pkg)"
                @keydown.enter.prevent="openPackage(pkg)"
                @keydown.space.prevent="openPackage(pkg)"
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
                    <div v-else :class="['relative flex h-full w-full items-center justify-center overflow-hidden bg-gradient-to-br', getColorClass(index, pkg)]">
                        <div class="grid-dots absolute inset-0 opacity-20"></div>
                        <span class="relative z-10 flex h-14 w-14 items-center justify-center rounded-2xl border border-white/25 bg-white/20 text-white shadow-sm backdrop-blur transition-transform duration-300 group-hover:scale-110">
                            <Icon :icon="getIcon(index, pkg)" class="h-7 w-7" />
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

                    <!-- Status pengerjaan -->
                    <span
                        v-if="attemptStatus(pkg) === 'completed'"
                        class="chip absolute right-3 top-3 border border-white/20 bg-emerald-500/95 text-white shadow-sm backdrop-blur"
                    >
                        <Icon icon="mdi:check-circle" class="h-3.5 w-3.5" />
                        Selesai
                    </span>
                    <span
                        v-else-if="attemptStatus(pkg) === 'in_progress'"
                        class="chip absolute right-3 top-3 border border-white/20 bg-sky-500/95 text-white shadow-sm backdrop-blur"
                    >
                        <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                        Berjalan
                    </span>
                    <span v-else class="chip absolute right-3 top-3 border border-white/20 bg-black/40 text-white backdrop-blur">
                        {{ pkg.total_cards }} Kartu
                    </span>

                    <!-- Sorotan aksi -->
                    <div
                        v-if="!isLocked(pkg)"
                        class="pointer-events-none absolute inset-0 flex items-end justify-end bg-gradient-to-t from-black/45 via-transparent to-transparent p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100 group-focus-visible:opacity-100"
                    >
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-white/20 text-white backdrop-blur transition-transform duration-300 group-hover:translate-x-0 group-hover:scale-105 sm:translate-x-2">
                            <Icon icon="mdi:arrow-right" class="h-5 w-5" />
                        </span>
                    </div>
                </div>

                <!-- Isi Kartu -->
                <div class="flex flex-1 flex-col p-5">
                    <div class="mb-2 flex flex-wrap items-center gap-1.5">
                        <span v-if="pkg.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">{{ pkg.bidang }}</span>
                        <span v-if="pkg.kelas" class="chip bg-muted text-muted-foreground">Kelas {{ pkg.kelas }}</span>
                        <span v-if="pkg.level" class="chip bg-muted text-muted-foreground">Level {{ pkg.level }}</span>
                    </div>

                    <h3 class="text-base font-bold text-foreground transition-colors group-hover:text-primary">
                        {{ pkg.title }}
                    </h3>

                    <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-muted-foreground">
                        {{ pkg.description || 'Tidak ada deskripsi tambahan.' }}
                    </p>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t border-border/60 pt-3 text-xs text-muted-foreground">
                        <span class="flex items-center gap-1.5 font-medium">
                            <Icon icon="mdi:help-circle-outline" class="h-4 w-4 text-primary" />
                            {{ pkg.total_questions }} Butir Soal
                        </span>
                        <span v-if="pkg.schedule_label" class="truncate text-right text-[11px]">{{ pkg.schedule_label }}</span>
                    </div>

                    <!-- Aksi: langsung ke bagian soal -->
                    <div class="mt-4">
                        <span
                            v-if="pkg.schedule_status === 'expired'"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-muted py-2.5 text-xs font-bold text-muted-foreground"
                        >
                            <Icon icon="mdi:close-octagon" class="h-4 w-4" />
                            Jadwal Berakhir
                        </span>
                        <span
                            v-else-if="pkg.schedule_status === 'upcoming'"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 py-2.5 text-xs font-bold text-amber-700"
                        >
                            <Icon icon="mdi:clock-outline" class="h-4 w-4" />
                            Belum Dimulai
                        </span>
                        <button
                            v-else
                            type="button"
                            class="w-full py-2.5 text-xs"
                            :class="attemptStatus(pkg) === 'completed'
                                ? 'btn-outline border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                : 'btn-primary'"
                            :disabled="startingId === pkg.id"
                            @click.stop="openPackage(pkg)"
                        >
                            <Icon
                                :icon="ctaIcon(pkg)"
                                class="h-4 w-4"
                                :class="{ 'animate-spin': startingId === pkg.id }"
                            />
                            {{ ctaLabel(pkg) }}
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <!-- State Kosong -->
        <div v-else class="ui-card flex flex-col items-center justify-center px-4 py-16 text-center">
            <div class="empty-state-icon">
                <Icon icon="mdi:folder-open-outline" class="h-8 w-8" />
            </div>
            <h3 class="text-base font-bold text-foreground">
                Belum Ada PR Siswa Kelas Khusus
            </h3>
            <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                <template v-if="userBidangGroup">
                    Saat ini belum ada tugas PR {{ userBidangGroup }} yang tersedia untukmu. Silakan kembali lagi sesuai jadwal.
                </template>
                <template v-else>
                    Saat ini belum ada tugas PR yang tersedia. Silakan kembali lagi sesuai jadwal.
                </template>
            </p>
            <Link :href="route('user.dashboard')" class="btn-outline mt-5 px-5 py-2.5 text-xs">
                Kembali ke Beranda
            </Link>
        </div>

        <Pagination :links="packages.links" />
    </UserLayout>
</template>
