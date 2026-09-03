<script setup>
import { inject,  ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters?.search || '');
const bidang = ref(props.filters?.bidang || '');
const kelas = ref(props.filters?.kelas || '');

const packageColors = [
    'from-purple-500 to-indigo-500', 'from-green-600 to-green-700', 'from-red-500 to-red-600',
    'from-yellow-400 to-amber-500', 'from-orange-500 to-orange-600', 'from-blue-500 to-blue-600',
    'from-pink-500 to-pink-600', 'from-teal-500 to-teal-600', 'from-indigo-500 to-indigo-600',
    'from-rose-500 to-rose-600', 'from-cyan-500 to-cyan-600', 'from-amber-500 to-amber-600',
];

const packageIcons = ['📖', '📚', '🎯', '💡', '🚀', '🌟', '🎓', '📊', '⚡', '🏆', '💎', '🔥'];

function applyFilters() {
    router.get(route('packages.index'), {
        search: search.value,
        bidang: bidang.value,
        kelas: kelas.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    bidang.value = '';
    kelas.value = '';
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
            <div class="bg-card rounded-2xl p-4 shadow-card border border-border">
                <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">🔍</span>
                        <Input v-model="search" placeholder="Cari soal..." class="pl-9" />
                    </div>
                    <Select v-model="bidang" class="w-full sm:w-40">
                        <option value="">Semua Bidang</option>
                        <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                    </Select>
                    <Select v-model="kelas" class="w-full sm:w-40">
                        <option value="">Semua Kelas</option>
                        <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                    </Select>
                    <div class="flex gap-2">
                        <Button type="submit" variant="default" size="sm">Cari</Button>
                        <Button type="button" variant="ghost" size="sm" @click="resetFilters">Reset</Button>
                    </div>
                </form>
            </div>

            <p class="text-sm text-muted-foreground">{{ packages.total }} soal tersedia</p>

            <!-- Packages Grid -->
            <div v-if="packages.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                <div v-for="(pkg, index) in packages.data" :key="pkg.id"
                     class="relative bg-card rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 group hover:-translate-y-1.5 border border-border/80 hover:border-primary/30 flex flex-col anim-fade-in-up">
                    <!-- Gradient Header -->
                    <div class="relative h-40 md:h-44 overflow-hidden">
                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" :alt="pkg.title"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                        <div v-else :class="['w-full h-full flex items-center justify-center bg-gradient-to-br relative overflow-hidden', getColorClass(index)]">
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full" viewBox="0 0 100 100"><circle cx="20" cy="20" r="15" fill="white" opacity="0.3"/><circle cx="80" cy="30" r="20" fill="white" opacity="0.2"/><circle cx="50" cy="70" r="25" fill="white" opacity="0.25"/></svg>
                            </div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl md:text-6xl mb-2 opacity-90 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-500">{{ getIcon(index) }}</div>
                            </div>
                        </div>

                        <!-- Schedule Status Badge -->
                        <div class="absolute top-3 left-3">
                            <span v-if="pkg.schedule_status === 'active'" class="bg-green-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Berlangsung
                            </span>
                            <span v-else-if="pkg.schedule_status === 'upcoming'" class="bg-yellow-400 text-foreground text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg">⏳ Akan Datang</span>
                            <span v-else-if="pkg.schedule_status === 'expired'" class="bg-gray-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg">⛔ Berakhir</span>
                            <span v-else class="bg-primary/80 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg">♾️ Tanpa Batas</span>
                        </div>
                        <span v-if="pkg.is_active" class="absolute top-3 right-3 bg-card/90 backdrop-blur text-green-600 text-[10px] md:text-[11px] font-semibold px-2.5 py-1 rounded-full shadow border border-border">✅ Aktif</span>
                    </div>

                    <div class="p-4 md:p-5 flex flex-col flex-1">
                        <h3 class="text-base md:text-lg font-bold text-foreground flex-1 line-clamp-1 mb-2">{{ pkg.title }}</h3>

                        <div class="flex flex-wrap gap-1.5 mb-2.5">
                            <span v-if="pkg.bidang" class="inline-flex items-center text-[10px] md:text-[11px] bg-primary/10 text-primary font-semibold px-2 py-0.5 rounded-full">📂 {{ pkg.bidang }}</span>
                            <span v-if="pkg.level" class="inline-flex items-center text-[10px] md:text-[11px] bg-foreground/10 text-foreground font-semibold px-2 py-0.5 rounded-full">🎯 {{ pkg.level }}</span>
                            <span v-if="pkg.kelas" class="inline-flex items-center text-[10px] md:text-[11px] bg-yellow-400/15 text-yellow-700 font-semibold px-2 py-0.5 rounded-full">🏫 {{ pkg.kelas }}</span>
                        </div>

                        <p class="text-muted-foreground text-xs md:text-sm line-clamp-2 mb-3 leading-relaxed">{{ pkg.description }}</p>

                        <div v-if="getScheduleLabel(pkg)" class="flex items-center gap-1.5 text-[10px] md:text-[11px] text-muted-foreground mb-2.5 bg-muted/50 px-2 py-1.5 rounded-md">
                            <span>📅</span>
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
                            <span v-if="pkg.schedule_status === 'expired'" class="block w-full text-center bg-muted text-muted-foreground py-2 rounded-xl font-semibold text-xs md:text-sm cursor-not-allowed">⛔ Jadwal Berakhir</span>
                            <span v-else-if="pkg.schedule_status === 'upcoming'" class="block w-full text-center bg-yellow-100 text-yellow-700 py-2 rounded-xl font-semibold text-xs md:text-sm cursor-not-allowed">⏳ Belum Dimulai</span>
                            <Link v-else :href="route('packages.show', pkg.id)" class="block w-full text-center bg-primary text-white py-2 rounded-xl font-semibold hover:bg-primary/90 hover:shadow-lg transition-all duration-300 text-xs md:text-sm">📖 Kerjakan Tugas</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 md:py-16 bg-card rounded-2xl shadow-sm border border-border anim-fade-in-up">
                <div class="text-6xl md:text-7xl mb-6">📭</div>
                <h3 class="text-xl md:text-2xl font-bold text-muted-foreground">Belum Ada Tugas</h3>
                <p class="text-muted-foreground mt-2 text-sm md:text-base">Tugas akan segera tersedia</p>
            </div>

            <!-- Pagination -->
            <Pagination :links="packages.links" />
        </div>
    </UserLayout>
</template>
