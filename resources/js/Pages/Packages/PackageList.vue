<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Pagination from '@/Components/shared/Pagination.vue';

const route = inject('route');

const props = defineProps({
    packages: { type: Object, required: true },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    userBidangGroup: { type: String, default: null },
    userBidangRaw: { type: String, default: '' },
    currentSort: { type: String, default: 'latest' },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters?.search || '');
const bidang = ref(props.filters?.bidang || (props.userBidangGroup ? props.userBidangGroup : ''));
const kelas = ref(props.filters?.kelas || '');
const level = ref(props.filters?.level || '');
const sort = ref(props.filters?.sort || props.currentSort || 'latest');
const showFilters = ref(false);

const packageColors = [
    'from-blue-600 to-indigo-700',
    'from-indigo-600 to-violet-700',
    'from-sky-500 to-blue-700',
    'from-violet-600 to-purple-700',
    'from-amber-500 to-orange-600',
    'from-cyan-600 to-blue-700',
];

const packageIcons = [
    'mdi:book-open-page-variant',
    'mdi:target',
    'mdi:lightbulb-outline',
    'mdi:rocket-launch',
    'mdi:trophy',
    'mdi:flash',
];

const isFilterActive = computed(() =>
    !!(search.value || kelas.value || level.value || (bidang.value && !props.userBidangGroup))
);

function applyFilters() {
    router.get(route('user.packages.index'), {
        search: search.value,
        bidang: props.userBidangGroup ? props.userBidangGroup : bidang.value,
        kelas: kelas.value,
        level: level.value,
        sort: sort.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    bidang.value = props.userBidangGroup ? props.userBidangGroup : '';
    kelas.value = '';
    level.value = '';
    sort.value = 'latest';
    applyFilters();
}

function getColorClass(index) {
    return packageColors[index % packageColors.length];
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

const isBidangLocked = computed(() => !!props.userBidangGroup);

const statusMeta = {
    active: { label: 'Sedang Aktif', cls: 'bg-emerald-500/95 text-white', icon: 'mdi:play-circle' },
    upcoming: { label: 'Akan Datang', cls: 'bg-amber-500/95 text-white', icon: 'mdi:clock-outline' },
    expired: { label: 'Berakhir', cls: 'bg-slate-700/95 text-white', icon: 'mdi:close-circle-outline' },
};
</script>

<template>
    <UserLayout>
        <Head title="PR Pekanan - KPM SMART" />

        <template #header-title>PR Pekanan</template>
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

        <!-- Toolbar Filter -->
        <div class="ui-card anim-fade-in-up anim-delay-1 mb-6 p-4 sm:p-5">
            <form @submit.prevent="applyFilters" class="space-y-3.5">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Icon icon="mdi:magnify" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="search" placeholder="Cari judul PR Pekanan atau topik..." class="h-11 rounded-xl pl-10" />
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary h-11 flex-1 px-6 sm:flex-initial">
                            <Icon icon="mdi:filter-variant" class="h-4 w-4" />
                            Terapkan
                        </button>
                        <button
                            type="button"
                            class="btn-ghost h-11 px-4 lg:hidden"
                            :class="{ '!bg-primary/10 !text-primary': showFilters }"
                            @click="showFilters = !showFilters"
                        >
                            <Icon icon="mdi:tune-variant" class="h-4 w-4" />
                            Filter
                            <span v-if="isFilterActive" class="h-2 w-2 rounded-full bg-primary"></span>
                        </button>
                        <button type="button" class="btn-ghost h-11 px-4" @click="resetFilters">Reset</button>
                    </div>
                </div>

                <!-- Filter tambahan: terlipat di mobile, tampil di desktop -->
                <div :class="['flex-col gap-3 border-t border-border/60 pt-3.5 lg:flex lg:flex-row lg:items-center lg:justify-between', showFilters ? 'flex' : 'hidden']">
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                        <div class="relative">
                            <Select
                                v-model="bidang"
                                :disabled="isBidangLocked"
                                class="h-10 w-full rounded-xl text-xs bg-background"
                                :class="isBidangLocked ? 'cursor-not-allowed opacity-85' : ''"
                            >
                                <option v-if="!isBidangLocked" value="">Semua Bidang</option>
                                <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                            </Select>
                        </div>

                        <Select v-model="level" class="h-10 w-full rounded-xl bg-background text-xs">
                            <option value="">Semua Level</option>
                            <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                        </Select>

                        <Select v-model="kelas" class="h-10 w-full rounded-xl bg-background text-xs">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-muted-foreground">
                            <Icon icon="mdi:sort-variant" class="h-4 w-4 text-primary" />
                            Urutkan
                        </span>
                        <select
                            v-model="sort"
                            @change="applyFilters"
                            class="h-10 cursor-pointer rounded-xl border border-border bg-background px-3 py-1.5 text-xs font-bold text-foreground transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        >
                            <option value="latest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                            <option value="deadline">Batas Waktu Terdekat</option>
                            <option value="title_asc">Judul (A-Z)</option>
                            <option value="title_desc">Judul (Z-A)</option>
                        </select>
                    </div>
                </div>
            </form>
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
                            Buka PR Pekanan
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
            <h3 class="text-base font-bold text-foreground">Tidak Ada PR Pekanan Ditemukan</h3>
            <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                {{ userBidangGroup ? `Saat ini belum ada tugas PR ${userBidangGroup} yang cocok dengan pencarianmu.` : 'Coba ubah kata kunci pencarian atau reset filter untuk melihat semua PR yang tersedia.' }}
            </p>
            <button type="button" class="btn-outline mt-5 px-5 py-2.5 text-xs" @click="resetFilters">
                Reset Semua Filter
            </button>
        </div>

        <Pagination :links="packages.links" />
    </UserLayout>
</template>
