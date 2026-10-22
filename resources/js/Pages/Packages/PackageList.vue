<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
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

function applyFilters() {
    router.get(route('user.packages.index'), {
        search: search.value,
        bidang: props.userBidangGroup ? props.userBidangGroup : bidang.value,
        kelas: kelas.value,
        level: level.value,
        sort: sort.value,
    }, { preserveState: true, replace: true });
}

function onSortChange(newSort) {
    sort.value = newSort;
    applyFilters();
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
</script>

<template>
    <UserLayout>
        <Head title="PR Pekanan - KPM SMART" />

        <!-- Header -->
        <div class="mb-6 sm:mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                        <Icon icon="mdi:book-open-page-variant" class="w-3.5 h-3.5" />
                        Tugas Terjadwal
                    </span>
                    <span v-if="userBidangGroup" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        <Icon :icon="userBidangGroup === 'Matematika' ? 'mdi:calculator-variant' : 'mdi:atom'" class="w-3.5 h-3.5" />
                        Bidang: {{ userBidangGroup }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-foreground">PR Pekanan</h1>
                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Daftar tugas PR pekanan yang wajib dikerjakan sesuai bidang studi dan kelasmu.</p>
            </div>
            <div class="flex items-center gap-2.5 self-start md:self-auto">
                <div class="text-xs font-semibold text-muted-foreground bg-card border border-border/80 px-4 py-2 rounded-xl shadow-2xs">
                    <strong class="text-foreground font-bold">{{ packages.total }}</strong> PR Tersedia
                </div>
            </div>
        </div>

        <!-- Student Bidang Information Banner -->
        <div
            v-if="userBidangGroup"
            class="mb-6 p-4 rounded-2xl border transition-all duration-300"
            :class="userBidangGroup === 'Matematika'
                ? 'bg-gradient-to-r from-blue-50/70 via-indigo-50/50 to-card border-blue-200 text-blue-950'
                : 'bg-gradient-to-r from-violet-50/70 via-purple-50/50 to-card border-violet-200 text-violet-950'"
        >
            <div class="flex items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-sm"
                        :class="userBidangGroup === 'Matematika' ? 'bg-blue-600' : 'bg-violet-600'"
                    >
                        <Icon :icon="userBidangGroup === 'Matematika' ? 'mdi:calculator' : 'mdi:flask-outline'" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-bold">
                            Tersortir Otomatis untuk Bidang:
                            <span class="underline decoration-2 underline-offset-2">{{ userBidangGroup }}</span>
                        </p>
                        <p class="text-[11px] sm:text-xs opacity-80 mt-0.5">
                            Berdasarkan data profil siswa (<strong class="font-semibold">{{ userBidangRaw || userBidangGroup }}</strong>), sistem hanya menampilkan tugas PR {{ userBidangGroup }}.
                        </p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white/80 border border-current/20 shrink-0">
                    <Icon icon="mdi:lock-outline" class="w-3.5 h-3.5" />
                    Terkunci Sesuai Bidang
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Filter & Sort Toolbar -->
            <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-xs border border-border/80">
                <form @submit.prevent="applyFilters" class="space-y-3.5">
                    <!-- Top row: Search input & Submit -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground">
                                <Icon icon="mdi:magnify" class="w-5 h-5" />
                            </span>
                            <Input
                                v-model="search"
                                placeholder="Cari judul PR Pekanan atau topik..."
                                class="pl-10 h-11 rounded-xl"
                            />
                        </div>
                        <div class="flex gap-2">
                            <Button type="submit" size="default" class="h-11 px-6 rounded-xl flex-1 sm:flex-initial shadow-xs">
                                <Icon icon="mdi:filter-variant" class="w-4 h-4" />
                                Terapkan
                            </Button>
                            <Button type="button" variant="ghost" size="default" class="h-11 px-4 rounded-xl" @click="resetFilters">
                                Reset
                            </Button>
                        </div>
                    </div>

                    <!-- Bottom row: Select Filters & Sort -->
                    <div class="pt-2 border-t border-border/60 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
                        <!-- Filters -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <!-- Bidang Selector -->
                            <div class="relative">
                                <Select
                                    v-model="bidang"
                                    :disabled="isBidangLocked"
                                    class="w-full h-10 rounded-xl text-xs bg-background"
                                    :class="isBidangLocked ? 'opacity-85 cursor-not-allowed bg-muted/40' : ''"
                                >
                                    <option v-if="!isBidangLocked" value="">Semua Bidang</option>
                                    <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                                </Select>
                            </div>

                            <!-- Level Selector -->
                            <Select v-model="level" class="w-full h-10 rounded-xl text-xs bg-background">
                                <option value="">Semua Level</option>
                                <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                            </Select>

                            <!-- Kelas Selector -->
                            <Select v-model="kelas" class="w-full h-10 rounded-xl text-xs bg-background col-span-2 sm:col-span-1">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                            </Select>
                        </div>

                        <!-- Sort By Dropdown -->
                        <div class="flex items-center gap-2 self-end lg:self-auto">
                            <span class="text-muted-foreground font-semibold flex items-center gap-1 shrink-0">
                                <Icon icon="mdi:sort-variant" class="w-4 h-4 text-primary" />
                                Urutkan:
                            </span>
                            <select
                                v-model="sort"
                                @change="applyFilters"
                                class="h-10 px-3 py-1.5 rounded-xl border border-border bg-background text-xs font-bold text-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition cursor-pointer"
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

            <!-- Packages Grid -->
            <div v-if="packages.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                <div
                    v-for="(pkg, index) in packages.data"
                    :key="pkg.id"
                    :style="{ animationDelay: (index * 60) + 'ms' }"
                    class="bg-card rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 group hover:-translate-y-1 border border-border/80 hover:border-primary/40 flex flex-col anim-fade-in-up opacity-0 [animation-fill-mode:forwards]"
                >
                    <!-- Header Banner -->
                    <div class="relative h-36 sm:h-40 overflow-hidden">
                        <img
                            v-if="pkg.thumbnail"
                            :src="'/storage/' + pkg.thumbnail"
                            :alt="pkg.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        />
                        <div
                            v-else
                            :class="['w-full h-full flex items-center justify-center bg-gradient-to-br relative overflow-hidden', getColorClass(index)]"
                        >
                            <div class="absolute inset-0 opacity-15">
                                <div class="grid-dots absolute inset-0"></div>
                            </div>
                            <div class="relative z-10 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur border border-white/25 flex items-center justify-center text-white text-3xl mx-auto shadow-sm group-hover:scale-110 transition-transform duration-300">
                                    <Icon :icon="getIcon(index)" class="w-7 h-7" />
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span
                                v-if="pkg.schedule_status === 'active'"
                                class="bg-blue-600 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1.5"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Sedang Aktif
                            </span>
                            <span
                                v-else-if="pkg.schedule_status === 'upcoming'"
                                class="bg-amber-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1"
                            >
                                <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" />
                                Akan Datang
                            </span>
                            <span
                                v-else-if="pkg.schedule_status === 'expired'"
                                class="bg-slate-700 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1"
                            >
                                <Icon icon="mdi:close-circle-outline" class="w-3.5 h-3.5" />
                                Berakhir
                            </span>
                        </div>

                        <!-- Card Count Pill -->
                        <div class="absolute top-3 right-3 bg-black/40 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full border border-white/20">
                            {{ getTotalCards(pkg) }} Kartu Soal
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex flex-wrap gap-1.5 mb-2.5">
                            <span v-if="pkg.bidang" class="text-[10px] font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-md border border-primary/20">
                                {{ pkg.bidang }}
                            </span>
                            <span v-if="pkg.level" class="text-[10px] font-bold bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md border border-indigo-200">
                                Level {{ pkg.level }}
                            </span>
                            <span v-if="pkg.kelas" class="text-[10px] font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md border border-amber-200">
                                Kelas {{ pkg.kelas }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-foreground line-clamp-1 mb-1.5 group-hover:text-primary transition-colors">
                            {{ pkg.title }}
                        </h3>

                        <p class="text-muted-foreground text-xs line-clamp-2 mb-4 leading-relaxed flex-1">
                            {{ pkg.description || 'Tidak ada deskripsi tambahan.' }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-muted-foreground pt-3 border-t border-border/60 mb-4">
                            <span class="flex items-center gap-1.5 font-medium">
                                <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-primary" />
                                {{ getTotalQuestions(pkg) }} Butir Soal
                            </span>
                            <span v-if="pkg.schedule_label" class="text-[11px] text-muted-foreground/80 truncate max-w-[140px]">
                                {{ pkg.schedule_label }}
                            </span>
                        </div>

                        <!-- CTA Button -->
                        <div>
                            <span
                                v-if="pkg.schedule_status === 'expired'"
                                class="w-full text-center bg-muted text-muted-foreground py-2.5 rounded-xl font-bold text-xs cursor-not-allowed flex items-center justify-center gap-1.5"
                            >
                                <Icon icon="mdi:close-octagon" class="w-4 h-4" />
                                Jadwal Berakhir
                            </span>
                            <span
                                v-else-if="pkg.schedule_status === 'upcoming'"
                                class="w-full text-center bg-amber-50 text-amber-700 border border-amber-200 py-2.5 rounded-xl font-bold text-xs cursor-not-allowed flex items-center justify-center gap-1.5"
                            >
                                <Icon icon="mdi:clock-outline" class="w-4 h-4" />
                                Belum Dimulai
                            </span>
                            <Link
                                v-else
                                :href="route('user.packages.show', pkg.id)"
                                class="w-full text-center bg-primary text-primary-foreground py-2.5 rounded-xl font-bold hover:bg-primary/90 hover:shadow-md transition-all duration-200 text-xs flex items-center justify-center gap-2 active:scale-98 shadow-xs"
                            >
                                <Icon icon="mdi:book-open-page-variant" class="w-4 h-4" />
                                Buka PR Pekanan
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-card rounded-2xl border border-border/80 anim-fade-in-up">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-muted/60 flex items-center justify-center text-muted-foreground/60">
                    <Icon icon="mdi:folder-open-outline" class="w-8 h-8" />
                </div>
                <h3 class="text-base font-bold text-foreground">Tidak Ada PR Pekanan Ditemukan</h3>
                <p class="text-muted-foreground mt-1 text-xs max-w-sm mx-auto">
                    {{ userBidangGroup ? `Saat ini belum ada tugas PR ${userBidangGroup} yang cocok dengan pencarianmu.` : 'Coba ubah kata kunci pencarian atau reset filter untuk melihat semua PR yang tersedia.' }}
                </p>
                <Button variant="outline" size="sm" class="mt-4 rounded-xl" @click="resetFilters">
                    Reset Semua Filter
                </Button>
            </div>

            <!-- Pagination -->
            <Pagination :links="packages.links" />
        </div>
    </UserLayout>
</template>
