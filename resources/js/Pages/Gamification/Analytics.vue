<script setup>
import { inject, ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import StatCard from '@/Components/shared/StatCard.vue';
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

const scoreChartData = computed(() => ({
    labels: props.scoreOverTime.map(d => d.date),
    datasets: [{
        label: 'Nilai',
        data: props.scoreOverTime.map(d => d.score),
        borderColor: '#588157',
        backgroundColor: 'rgba(88, 129, 87, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
    }],
}));

const scoreChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
        x: { grid: { display: false } },
    },
};

const accuracyChartData = computed(() => ({
    labels: props.accuracyOverTime.map(d => d.date),
    datasets: [{
        label: 'Akurasi',
        data: props.accuracyOverTime.map(d => d.accuracy),
        borderColor: '#344e41',
        backgroundColor: 'rgba(52, 78, 65, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
    }],
}));

const accuracyChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
        x: { grid: { display: false } },
    },
};

const packageChartData = computed(() => ({
    labels: props.packageStats.map(d => d.name),
    datasets: [{
        label: 'Rata-rata Nilai',
        data: props.packageStats.map(d => d.avg_score),
        backgroundColor: ['#588157', '#344e41', '#a3b18a', '#3a5a40', '#dad7cd', '#588157'],
        borderRadius: 8,
    }],
}));

const packageChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
    },
};

const hasData = computed(() => props.scoreOverTime.length > 0);
</script>

<template>
    <UserLayout>
        <Head title="Analitik - KPM SMART" />
        <template #header-title>📈 Analitik Belajar</template>
        <template #header-sub>Visualisasi progres belajarmu</template>

        <div class="max-w-4xl mx-auto space-y-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard title="Total Percobaan" :value="totalAttempts" color="pine-teal" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>" />
                <StatCard title="Rata-rata Skor" :value="avgScore + '%'" color="fern" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z'/></svg>" />
                <StatCard title="Skor Tertinggi" :value="bestScore + '%'" color="hunter-green" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516'/></svg>" />
                <StatCard title="Akurasi" :value="accuracy + '%'" color="dry-sage" icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z'/></svg>" />
            </div>

            <template v-if="hasData">
                <!-- Score over time -->
                <div class="anim-fade-in-up bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="text-sm font-semibold mb-4">📊 Progres Nilai</h3>
                    <div class="h-64">
                        <Line :data="scoreChartData" :options="scoreChartOptions" />
                    </div>
                </div>

                <!-- Accuracy over time -->
                <div class="anim-fade-in-up bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="text-sm font-semibold mb-4">🎯 Progres Akurasi</h3>
                    <div class="h-64">
                        <Line :data="accuracyChartData" :options="accuracyChartOptions" />
                    </div>
                </div>

                <!-- Package stats -->
                <div v-if="packageStats.length > 0" class="anim-fade-in-up bg-card border rounded-2xl p-5 shadow-card">
                    <h3 class="text-sm font-semibold mb-4">📦 Skor per Soal</h3>
                    <div class="h-64">
                        <Bar :data="packageChartData" :options="packageChartOptions" />
                    </div>
                </div>

                <!-- Package detail table -->
                <div v-if="packageStats.length > 0" class="bg-card border rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="text-sm font-semibold">📋 Detail Statistik per Soal</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/30 text-xs text-muted-foreground">
                                <tr>
                                    <th class="text-left px-5 py-3 font-medium">Soal</th>
                                    <th class="text-center px-4 py-3 font-medium">Percobaan</th>
                                    <th class="text-center px-4 py-3 font-medium">Rata-rata</th>
                                    <th class="text-center px-4 py-3 font-medium">Akurasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/50">
                                <tr v-for="pkg in packageStats" :key="pkg.name" class="hover:bg-muted/20">
                                    <td class="px-5 py-3 font-medium truncate max-w-[200px]">{{ pkg.name }}</td>
                                    <td class="text-center px-4 py-3">{{ pkg.attempts }}</td>
                                    <td class="text-center px-4 py-3">
                                        <span :class="pkg.avg_score >= 80 ? 'text-emerald-600' : pkg.avg_score >= 60 ? 'text-yellow-600' : 'text-red-500'" class="font-semibold">
                                            {{ pkg.avg_score }}%
                                        </span>
                                    </td>
                                    <td class="text-center px-4 py-3">
                                        <span :class="pkg.accuracy >= 80 ? 'text-emerald-600' : pkg.accuracy >= 60 ? 'text-yellow-600' : 'text-red-500'" class="font-semibold">
                                            {{ pkg.accuracy }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- Empty state -->
            <div v-else class="bg-card border rounded-2xl p-12 text-center">
                <div class="text-5xl mb-4">📊</div>
                <h3 class="font-semibold text-lg mb-2">Belum Ada Data</h3>
                <p class="text-sm text-muted-foreground mb-4">Mulai kerjakan soal untuk melihat analitik belajarmu</p>
                <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary/90 transition">
                    Mulai Belajar
                </Link>
            </div>

            <div class="flex justify-center">
                <Link :href="route('user.dashboard')" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Kembali ke Dasbor
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
