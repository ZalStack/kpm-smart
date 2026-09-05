<script setup>
import { inject,  ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
    'from-purple-500 to-indigo-500', 'from-green-600 to-green-700', 'from-red-500 to-red-600',
    'from-yellow-400 to-amber-500', 'from-orange-500 to-orange-600', 'from-blue-500 to-blue-600',
    'from-pink-500 to-pink-600', 'from-teal-500 to-teal-600', 'from-indigo-500 to-indigo-600',
    'from-rose-500 to-rose-600', 'from-cyan-500 to-cyan-600', 'from-amber-500 to-amber-600',
];

const packageIcons = [
    { icon: 'mdi:book-open-page-variant', color: '' },
    { icon: 'mdi:book-open-variant', color: '' },
    { icon: 'mdi:target', color: 'text-emerald-500' },
    { icon: 'mdi:lightbulb-outline', color: 'text-blue-500' },
    { icon: 'mdi:rocket-launch', color: '' },
    { icon: 'mdi:star-shooting', color: '' },
    { icon: 'mdi:graduation-cap', color: '' },
    { icon: 'mdi:chart-bar', color: '' },
    { icon: 'mdi:flash', color: '' },
    { icon: 'mdi:trophy', color: 'text-yellow-500' },
    { icon: 'mdi:diamond-stone', color: '' },
    { icon: 'mdi:fire', color: 'text-orange-500' },
];

function applyFilters() {
    router.get(route('packages.index'), {
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

function getScheduleLabel(pkg) {
    if (pkg.start_date || pkg.end_date) {
        return pkg.schedule_label || '';
    }
    return '';
}
</script>

<template>
    <UserLayout>
        <Head title="Soal Tugas - KPM SMART" />

        <template #header-title>Soal Tugas</template>
        <template #header-sub>Pilih soal tugas yang sesuai untuk dikerjakan</template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-card rounded-2xl p-5 shadow-card border border-border">
                <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground"><Icon icon="mdi:magnify" class="w-5 h-5" /></span>
                        <Input v-model="search" placeholder="Cari soal tugas..." class="pl-11 h-11 text-sm rounded-xl" />
                    </div>
                    <Select v-model="bidang" class="w-full sm:w-44 h-11 rounded-xl">
                        <option value="">Semua Bidang</option>
                        <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                    </Select>
                    <Select v-model="level" class="w-full sm:w-44 h-11 rounded-xl">
                        <option value="">Semua Level</option>
                        <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                    </Select>
                    <Select v-model="kelas" class="w-full sm:w-44 h-11 rounded-xl">
                        <option value="">Semua Kelas</option>
                        <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                    </Select>
                    <div class="flex gap-2">
                        <Button type="submit" variant="default" size="sm" class="h-11 px-5 rounded-xl"><Icon icon="mdi:magnify" class="w-4 h-4 mr-1" /> Cari</Button>
                        <Button type="button" variant="ghost" size="sm" class="h-11 px-4 rounded-xl" @click="resetFilters">Reset</Button>
                    </div>
                </form>
            </div>

            <p class="text-sm text-muted-foreground font-medium">{{ packages.total }} soal tersedia</p>

            <!-- Packages Grid -->
            <div v-if="packages.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                <div v-for="(pkg, index) in packages.data" :key="pkg.id"
                     :style="{ animationDelay: (index * 80) + 'ms' }"
                     class="relative bg-card rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group hover:-translate-y-1.5 border border-border/80 hover:border-primary/30 flex flex-col anim-fade-in-up opacity-0 [animation-fill-mode:forwards]">
                    <!-- Gradient Header -->
                    <div class="relative h-40 md:h-44 overflow-hidden">
                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" :alt="pkg.title"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                        <div v-else :class="['w-full h-full flex items-center justify-center bg-gradient-to-br relative overflow-hidden', getColorClass(index)]">
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full" viewBox="0 0 100 100"><circle cx="20" cy="20" r="15" fill="white" opacity="0.3"/><circle cx="80" cy="30" r="20" fill="white" opacity="0.2"/><circle cx="50" cy="70" r="25" fill="white" opacity="0.25"/></svg>
                            </div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl md:text-6xl mb-2 opacity-90 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-500"><Icon :icon="getIcon(index).icon" :class="['w-12 h-12', getIcon(index).color]" /></div>
                            </div>
                        </div>

                        <!-- Schedule Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span v-if="pkg.schedule_status === 'active'" class="bg-green-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Berlangsung
                            </span>
                            <span v-else-if="pkg.schedule_status === 'upcoming'" class="inline-flex items-center gap-1 bg-yellow-400 text-foreground text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg"><Icon icon="mdi:clock-outline" class="w-4 h-4" /> Akan Datang</span>
                            <span v-else-if="pkg.schedule_status === 'expired'" class="inline-flex items-center gap-1 bg-gray-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg"><Icon icon="mdi:close-octagon" class="w-4 h-4" /> Berakhir</span>
                            <span v-else class="inline-flex items-center gap-1 bg-primary/80 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg"><Icon icon="mdi:infinity" class="w-4 h-4" /> Tanpa Batas</span>
                        </div>
                        <span v-if="pkg.is_active" class="absolute top-3 right-3 inline-flex items-center gap-1 bg-card/90 backdrop-blur text-green-600 text-[10px] md:text-[11px] font-semibold px-2.5 py-1 rounded-full shadow border border-border"><Icon icon="mdi:check-circle" class="w-4 h-4 text-green-600" /> Aktif</span>
                    </div>

                    <div class="p-4 md:p-5 flex flex-col flex-1">
                        <h3 class="text-base md:text-lg font-bold text-foreground flex-1 line-clamp-1 mb-2">{{ pkg.title }}</h3>

                        <div class="flex flex-wrap gap-1.5 mb-2.5">
                            <span v-if="pkg.bidang" class="inline-flex items-center text-[10px] md:text-[11px] bg-primary/10 text-primary font-semibold px-2 py-0.5 rounded-full"><Icon icon="mdi:folder-outline" class="w-4 h-4" /> {{ pkg.bidang }}</span>
                            <span v-if="pkg.level" class="inline-flex items-center text-[10px] md:text-[11px] bg-foreground/10 text-foreground font-semibold px-2 py-0.5 rounded-full"><Icon icon="mdi:target" class="w-4 h-4 text-emerald-500" /> {{ pkg.level }}</span>
                            <span v-if="pkg.kelas" class="inline-flex items-center text-[10px] md:text-[11px] bg-yellow-400/15 text-yellow-700 font-semibold px-2 py-0.5 rounded-full"><Icon icon="mdi:school-outline" class="w-4 h-4" /> {{ pkg.kelas }}</span>
                        </div>

                        <p class="text-muted-foreground text-xs md:text-sm line-clamp-2 mb-3 leading-relaxed">{{ pkg.description }}</p>

                        <div v-if="getScheduleLabel(pkg)" class="flex items-center gap-1.5 text-[10px] md:text-[11px] text-muted-foreground mb-2.5 bg-muted/50 px-2 py-1.5 rounded-md">
                            <Icon icon="mdi:calendar-outline" class="w-4 h-4" />
                            <span>{{ getScheduleLabel(pkg) }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] md:text-[11px] text-muted-foreground mb-3">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                {{ getTotalCards(pkg) }} Card
                            </span>
                            <span class="w-px h-3 bg-border"></span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                                {{ getTotalQuestions(pkg) }} Soal
                            </span>
                        </div>

                        <div class="mt-auto pt-3 border-t border-border">
                            <span v-if="pkg.schedule_status === 'expired'" class="block w-full text-center bg-muted text-muted-foreground py-2.5 rounded-xl font-semibold text-xs md:text-sm cursor-not-allowed inline-flex items-center justify-center gap-1.5"><Icon icon="mdi:close-octagon" class="w-4 h-4" /> Jadwal Berakhir</span>
                            <span v-else-if="pkg.schedule_status === 'upcoming'" class="block w-full text-center bg-yellow-100 text-yellow-700 py-2.5 rounded-xl font-semibold text-xs md:text-sm cursor-not-allowed inline-flex items-center justify-center gap-1.5"><Icon icon="mdi:clock-outline" class="w-4 h-4" /> Belum Dimulai</span>
                            <Link v-else :href="route('packages.show', pkg.id)" class="block w-full text-center bg-primary text-white py-2.5 rounded-xl font-semibold hover:bg-primary/90 hover:shadow-lg transition-all duration-300 text-xs md:text-sm inline-flex items-center justify-center gap-1.5"><Icon icon="mdi:book-open-page-variant" class="w-4 h-4" /> Kerjakan Tugas</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 md:py-20 bg-card rounded-2xl shadow-sm border border-border anim-fade-in-up">
                <div class="w-20 h-20 md:w-24 md:h-24 mx-auto mb-6 rounded-full bg-muted/50 flex items-center justify-center">
                    <Icon icon="mdi:email-outline" class="w-10 h-10 md:w-12 md:h-12 text-muted-foreground/60" />
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-muted-foreground">Belum Ada Tugas</h3>
                <p class="text-muted-foreground mt-2 text-sm md:text-base max-w-md mx-auto">Tugas akan segera tersedia. Silakan cek kembali nanti.</p>
            </div>

            <!-- Pagination -->
            <Pagination :links="packages.links" />
        </div>
    </UserLayout>
</template>
