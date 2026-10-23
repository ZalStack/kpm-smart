<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Line, Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import { Icon } from '@iconify/vue/offline';

defineOptions({ layout: (h, page) => h(UserLayout, null, () => page) });

const route = inject('route');

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    scoreOverTime: { type: Array, default: () => [] },
    accuracyOverTime: { type: Array, default: () => [] },
    packageStats: { type: Array, default: () => [] },
    totalAttempts: { type: Number, default: 0 },
    avgScore: { type: Number, default: 0 },
    bestScore: { type: Number, default: 0 },
    accuracy: { type: Number, default: 0 },
});

const CHART = {
    indigo: '#4f46e5',
    indigoFill: 'rgba(79, 70, 229, 0.12)',
    sky: '#0284c7',
    skyFill: 'rgba(2, 132, 199, 0.12)',
    palette: ['#4f46e5', '#0284c7', '#7c3aed', '#0891b2', '#6366f1', '#0ea5e9'],
};

const baseScales = {
    y: { min: 0, max: 100, ticks: { callback: (v) => v + '%' }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
    x: { grid: { display: false } },
};

const scoreChartData = computed(() => ({
    labels: props.scoreOverTime.map((d) => d.date),
    datasets: [
        {
            label: 'Nilai',
            data: props.scoreOverTime.map((d) => d.score),
            borderColor: CHART.indigo,
            backgroundColor: CHART.indigoFill,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: CHART.indigo,
        },
    ],
}));

const scoreChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: baseScales,
};

const accuracyChartData = computed(() => ({
    labels: props.accuracyOverTime.map((d) => d.date),
    datasets: [
        {
            label: 'Akurasi',
            data: props.accuracyOverTime.map((d) => d.accuracy),
            borderColor: CHART.sky,
            backgroundColor: CHART.skyFill,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: CHART.sky,
        },
    ],
}));

const accuracyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: baseScales,
};

const packageChartData = computed(() => ({
    labels: props.packageStats.map((d) => d.name),
    datasets: [
        {
            label: 'Rata-rata Nilai',
            data: props.packageStats.map((d) => d.avg_score),
            backgroundColor: CHART.palette,
            borderRadius: 8,
        },
    ],
}));

const packageChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { min: 0, max: 100, ticks: { callback: (v) => v + '%' }, grid: { color: 'rgba(148, 163, 184, 0.15)' } },
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    },
};

const hasData = computed(() => props.scoreOverTime.length > 0);

const statItems = computed(() => [
    { label: 'Total Percobaan', value: props.totalAttempts, icon: 'mdi:check-decagram-outline', cls: 'bg-indigo-50 text-indigo-600' },
    { label: 'Rata-rata Skor', value: props.avgScore + '%', icon: 'mdi:chart-line', cls: 'bg-sky-50 text-sky-600' },
    { label: 'Skor Tertinggi', value: props.bestScore + '%', icon: 'mdi:trophy-outline', cls: 'bg-amber-50 text-amber-600' },
    { label: 'Akurasi', value: props.accuracy + '%', icon: 'mdi:target', cls: 'bg-emerald-50 text-emerald-600' },
]);

const scoreClass = (v) => (v >= 80 ? 'text-emerald-600' : v >= 60 ? 'text-amber-600' : 'text-rose-500');
</script>

<template>
    <Head title="Analitik Belajar" />

    <div>
        <div class="mb-6 hidden sm:block">
            <h1 class="page-heading">
                <span class="icon-box"><Icon icon="mdi:chart-timeline-variant" width="22" /></span>
                Analitik Belajar
            </h1>
            <p class="page-subheading">Visualisasi progres belajarmu dari waktu ke waktu</p>
        </div>

        <div class="grid-stats mb-6">
            <div v-for="item in statItems" :key="item.label" class="stat-tile-v2 anim-fade-in-up">
                <div class="stat-tile-head">
                    <div class="icon-box" :class="item.cls"><Icon :icon="item.icon" width="20" /></div>
                </div>
                <p class="stat-label">{{ item.label }}</p>
                <p class="stat-value">{{ item.value }}</p>
            </div>
        </div>

        <template v-if="hasData">
            <div class="grid gap-5 lg:grid-cols-2">
                <div class="ui-card anim-fade-in-up">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="icon-box icon-box-sm"><Icon icon="mdi:chart-bar" width="18" /></div>
                        <h3 class="text-sm font-bold text-slate-900">Progres Nilai</h3>
                    </div>
                    <div class="h-64">
                        <Line :data="scoreChartData" :options="scoreChartOptions" />
                    </div>
                </div>

                <div class="ui-card anim-fade-in-up">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="icon-box icon-box-sm"><Icon icon="mdi:target" width="18" /></div>
                        <h3 class="text-sm font-bold text-slate-900">Progres Akurasi</h3>
                    </div>
                    <div class="h-64">
                        <Line :data="accuracyChartData" :options="accuracyChartOptions" />
                    </div>
                </div>
            </div>

            <div v-if="packageStats.length > 0" class="ui-card mt-5 anim-fade-in-up">
                <div class="mb-4 flex items-center gap-2">
                    <div class="icon-box icon-box-sm"><Icon icon="mdi:package-variant" width="18" /></div>
                    <h3 class="text-sm font-bold text-slate-900">Skor per Paket</h3>
                </div>
                <div class="h-64">
                    <Bar :data="packageChartData" :options="packageChartOptions" />
                </div>
            </div>

            <div v-if="packageStats.length > 0" class="ui-card mt-5 overflow-hidden p-0 anim-fade-in-up">
                <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 to-transparent px-5 py-4">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                        <Icon icon="mdi:clipboard-text-outline" width="18" class="text-indigo-500" />
                        Detail Statistik per Paket
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-sm">
                        <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">No</th>
                                <th class="px-5 py-3 text-left">Paket</th>
                                <th class="px-4 py-3 text-center">Percobaan</th>
                                <th class="px-4 py-3 text-center">Rata-rata</th>
                                <th class="px-4 py-3 text-center">Akurasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(pkg, index) in packageStats" :key="pkg.name" class="transition-colors hover:bg-slate-50/70">
                                <td class="px-4 py-3 text-center font-semibold text-slate-400">{{ index + 1 }}</td>
                                <td class="max-w-[200px] truncate px-5 py-3 font-semibold text-slate-800">{{ pkg.name }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ pkg.attempts }}</td>
                                <td class="px-4 py-3 text-center font-bold" :class="scoreClass(pkg.avg_score)">{{ pkg.avg_score }}%</td>
                                <td class="px-4 py-3 text-center font-bold" :class="scoreClass(pkg.accuracy)">{{ pkg.accuracy }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div v-else class="empty-state anim-fade-in-up">
            <div class="empty-icon"><Icon icon="mdi:chart-box-outline" width="44" /></div>
            <h3 class="empty-title">Belum Ada Data</h3>
            <p class="empty-desc">Kerjakan latihan pertamamu untuk mulai melihat analitik progres belajarmu.</p>
            <Link :href="route('user.packages.index')" class="btn-primary mt-4">
                <Icon icon="mdi:play-circle-outline" width="18" />
                Mulai Belajar
            </Link>
        </div>

        <div class="mt-6 flex justify-center">
            <Link :href="route('user.dashboard')" class="back-link">
                <Icon icon="mdi:arrow-left" width="18" />
                Kembali ke Dasbor
            </Link>
        </div>
    </div>
</template>
