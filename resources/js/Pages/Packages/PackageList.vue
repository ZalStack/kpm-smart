<script setup>
import { inject, ref } from 'vue';
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
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters?.search || '');
const bidang = ref(props.filters?.bidang || '');
const kelas = ref(props.filters?.kelas || '');
const level = ref(props.filters?.level || '');

const packageColors = [
    'from-emerald-600 to-teal-700',
    'from-teal-600 to-cyan-700',
    'from-indigo-600 to-blue-700',
    'from-amber-500 to-orange-600',
    'from-purple-600 to-indigo-700',
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

function applyFilters() {
    router.get(route('user.packages.index'), {
        search: search.value,
        bidang: bidang.value,
        kelas: kelas.value,
        level: level.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    bidang.value = '';
    kelas.value = '';
    level.value = '';
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
</script>

<template>
    <UserLayout>
        <Head title="Soal Tugas PR - KPM SMART" />

        <!-- Header -->
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">Bank Soal Tugas PR</h1>
                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Pilih paket soal yang sesuai dengan jenjang dan kelasmu.</p>
            </div>
            <div class="text-xs font-semibold text-muted-foreground bg-card border border-border/80 px-3.5 py-1.5 rounded-full self-start sm:self-auto shadow-2xs">
                {{ packages.total }} Soal Tersedia
            </div>
        </div>

        <div class="space-y-6">
            <!-- Filter Toolbar -->
            <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-xs border border-border/80">
                <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground">
                            <Icon icon="mdi:magnify" class="w-5 h-5" />
                        </span>
                        <Input
                            v-model="search"
                            placeholder="Cari judul soal atau deskripsi..."
                            class="pl-10"
                        />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 md:w-auto">
                        <Select v-model="bidang" class="w-full md:w-36">
                            <option value="">Semua Bidang</option>
                            <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                        </Select>
                        <Select v-model="level" class="w-full md:w-36">
                            <option value="">Semua Level</option>
                            <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                        </Select>
                        <Select v-model="kelas" class="w-full md:w-36">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                        </Select>
                    </div>
                    <div class="flex gap-2">
                        <Button type="submit" size="default" class="px-5 flex-1 sm:flex-initial">
                            <Icon icon="mdi:filter-variant" class="w-4 h-4" />
                            Filter
                        </Button>
                        <Button type="button" variant="ghost" size="default" class="px-4" @click="resetFilters">
                            Reset
                        </Button>
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
                                class="bg-emerald-600 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1.5"
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
                            <span v-if="pkg.level" class="text-[10px] font-bold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-md border border-teal-200">
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
                                Buka Paket Tugas
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
                <h3 class="text-base font-bold text-foreground">Tidak Ada Soal Tugas Ditemukan</h3>
                <p class="text-muted-foreground mt-1 text-xs max-w-sm mx-auto">
                    Coba ubah kata kunci pencarian atau reset filter untuk melihat semua soal yang tersedia.
                </p>
                <Button variant="outline" size="sm" class="mt-4" @click="resetFilters">
                    Reset Semua Filter
                </Button>
            </div>

            <!-- Pagination -->
            <Pagination :links="packages.links" />
        </div>
    </UserLayout>
</template>
