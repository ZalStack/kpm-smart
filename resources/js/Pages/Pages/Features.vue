<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const page = usePage();
const user = page.props.auth?.user;

const features = [
    { icon: '📚', color: '#00a2e9', title: 'Bank Soal Berkualitas', desc: 'Ribuan soal latihan dengan berbagai tingkat kesulitan, selalu diperbarui mengikuti kurikulum terbaru.' },
    { icon: '💡', color: '#009a4b', title: 'Pembahasan Detail', desc: 'Setiap soal dilengkapi pembahasan langkah demi langkah yang mudah dipahami.' },
    { icon: '📝', color: '#27438D', title: 'Latihan Mandiri', desc: 'Kerjakan soal kapan saja, ulangi sebanyak yang kamu mau sampai benar-benar paham.' },
    { icon: '📊', color: '#b58900', title: 'Statistik Belajar', desc: 'Pantau perkembanganmu: nilai terbaik, rata-rata, dan riwayat latihan dalam dashboard yang mudah dibaca.' },
    { icon: '💬', color: '#7c3aed', title: 'Bantuan Cepat', desc: 'Ada pertanyaan? Gunakan live chat di pojok kanan bawah — tim kami siap membantu.' },
    { icon: '📱', color: '#14b8a6', title: 'Akses Multi Perangkat', desc: 'Tampilan responsif di HP, tablet, maupun laptop. Belajar kapan saja, di mana saja.' },
];

const steps = [
    { num: '01', title: 'Daftar Akun', desc: 'Registrasi gratis dengan email aktif' },
    { num: '02', title: 'Pilih Paket', desc: 'Pilih bank soal yang sesuai kebutuhanmu' },
    { num: '03', title: 'Mulai Belajar', desc: 'Akses langsung dan kerjakan soal' },
];
</script>

<template>
    <PublicLayout>
        <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground mb-4 md:mb-5" aria-label="Breadcrumb">
            <Link href="/" class="inline-flex items-center gap-1.5 font-medium hover:text-primary transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda
            </Link>
            <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-foreground font-semibold truncate">Fitur Unggulan</span>
        </nav>

        <div class="relative rounded-lg overflow-hidden bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-white shadow-xl shadow-primary/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-16 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-28 -left-10 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10 px-5 sm:px-8 py-12 sm:py-16 md:py-20 text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-white/85 mb-5">✨ Fitur Unggulan</span>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight">Semua yang Kamu Butuhkan untuk <span class="text-dry-sage">Belajar Lebih Baik</span></h1>
                <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed">KPM Belajar Online menyediakan soal, pembahasan, dan statistik belajar dalam satu platform yang mudah digunakan.</p>
            </div>
        </div>

        <div class="mt-8 md:mt-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
            <div v-for="(f, i) in features" :key="i" class="group bg-card rounded-lg border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-5 md:p-6">
                <div class="w-[52px] h-[52px] rounded-lg flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300" :style="{ background: f.color + '1a' }">{{ f.icon }}</div>
                <h3 class="font-bold text-foreground text-base md:text-lg">{{ f.title }}</h3>
                <p class="text-muted-foreground text-xs md:text-sm mt-2 leading-relaxed">{{ f.desc }}</p>
            </div>
        </div>

        <div class="mt-10 md:mt-14 bg-card rounded-lg border border-border shadow-sm p-5 sm:p-7 md:p-9">
            <div class="text-center mb-7 md:mb-9">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-foreground">🚀 Mulai dalam 3 Langkah Mudah</h2>
                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Cepat dan tanpa ribet</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
                <div v-for="s in steps" :key="s.num" class="relative bg-muted/70 rounded-lg p-5 border border-border hover:border-primary/30 transition-colors">
                    <span class="text-3xl font-extrabold text-primary/20 absolute top-3 right-4 select-none">{{ s.num }}</span>
                    <h4 class="font-bold text-foreground text-sm md:text-base pr-8">{{ s.title }}</h4>
                    <p class="text-xs md:text-sm text-muted-foreground mt-1.5 leading-relaxed">{{ s.desc }}</p>
                </div>
            </div>
        </div>

        <div class="mt-10 md:mt-14 relative rounded-lg overflow-hidden bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-white text-center px-5 py-10 sm:py-14">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 left-1/4 w-56 h-56 bg-white/10 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-extrabold">Siap Merasakan Semua Fiturnya?</h2>
                <p class="mt-3 text-white/70 text-sm sm:text-base max-w-lg mx-auto">Bergabung sekarang dan jelajahi ribuan soal latihan.</p>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link v-if="user" :href="route('packages.index')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-dry-sage text-foreground px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-dry-sage/80 hover:-translate-y-0.5 transition-all duration-300">🚀 Mulai Belajar</Link>
                    <Link v-else :href="route('login')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-dry-sage text-foreground px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-dry-sage/80 hover:-translate-y-0.5 transition-all duration-300">🔑 Masuk</Link>
                    <Link :href="route('pages.guide')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/15 text-white px-8 py-3.5 rounded-lg font-semibold text-sm hover:bg-white/15 transition-all duration-300">📖 Baca Panduan</Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
