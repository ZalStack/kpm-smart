<script setup>
import { inject,  ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
const route = inject('route');

const faqGroups = [
    {
        icon: 'mdi:puzzle-outline', title: 'Umum', color: '#00a2e9',
        items: [
            { q: 'Apa itu KPM SMART?', a: 'KPM SMART adalah platform belajar yang menyediakan bank soal latihan dengan pembahasan lengkap untuk mendukung belajar mandiri.' },
            { q: 'Apakah perlu membuat akun untuk mengakses soal?', a: 'Ya. Kamu perlu mendaftar (gratis) terlebih dahulu agar progres belajarmu tersimpan rapi di satu akun.' },
            { q: 'Apakah bisa diakses dari HP?', a: 'Bisa! Tampilan platform sudah responsif dan nyaman digunakan dari HP, tablet, maupun laptop.' },
        ],
    },
    {
        icon: 'mdi:key-outline', title: 'Akses & Soal', color: '#f97316',
        items: [
            { q: 'Bagaimana cara mulai belajar?', a: 'Setelah login, buka menu <strong>Soal</strong> untuk melihat daftar soal yang tersedia. Pilih soal yang sesuai, lalu mulai latihan.' },
            { q: 'Apakah semua soal bisa diakses gratis?', a: 'Ya! Semua soal bisa diakses langsung oleh pengguna yang sudah terdaftar. Kerjakan soal sepuasmu.' },
            { q: 'Berapa kali saya bisa mengerjakan soal yang sama?', a: 'Setiap soal hanya bisa dikerjakan satu kali. Pastikan kamu sudah siap sebelum memulai.' },
            { q: 'Bagaimana jika saya belum selesai mengerjakan?', a: 'Jika kamu keluar sebelum selesai, sesi latihan akan disimpan sebagai "in_progress" dan kamu bisa melanjutkannya nanti.' },
        ],
    },
    {
        icon: 'mdi:pencil-outline', title: 'Latihan Soal', color: '#009a4b',
        items: [
            { q: 'Apakah ada batas waktu mengerjakan?', a: 'Bergantung pada pengaturan soal. Beberapa soal memiliki batas waktu, beberapa tidak. Waktu akan ditampilkan saat kamu mulai latihan.' },
            { q: 'Bisakah mengulang soal yang salah?', a: 'Ya! Setelah menyelesaikan latihan, kamu bisa melihat pembahasan lengkap untuk setiap soal di halaman hasil.' },
            { q: 'Di mana saya bisa melihat riwayat latihan?', a: 'Buka menu <strong>Latihan</strong> untuk melihat semua sesi latihan yang sudah kamu kerjakan beserta nilainya.' },
            { q: 'Bagaimana cara melihat statistik belajar?', a: 'Klik menu <strong>Statistik</strong> di dropdown profil untuk melihat ringkasan performa belajarmu.' },
        ],
    },
    {
        icon: 'mdi:cog-outline', title: 'Akun & Teknis', color: '#7c3aed',
        items: [
            { q: 'Bagaimana jika lupa password?', a: 'Klik <strong>"Lupa Password?"</strong> di halaman login, masukkan emailmu, lalu buat password baru melalui link yang dikirim ke email.' },
            { q: 'Apakah data pribadi saya aman?', a: 'Kami hanya menyimpan data yang diperlukan (nama, email, sekolah) dan tidak membagikannya ke pihak ketiga.' },
            { q: 'Bagaimana cara mengubah profil saya?', a: 'Buka menu <strong>Profil Saya</strong> dari dropdown profil untuk mengubah data diri, foto profil, dan informasi lainnya.' },
        ],
    },
];

const openItem = ref(null);

function toggleItem(groupIdx, itemIdx) {
    const key = groupIdx + '-' + itemIdx;
    openItem.value = openItem.value === key ? null : key;
}
</script>

<template>
    <PublicLayout>
        <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground mb-4 md:mb-5" aria-label="Breadcrumb">
            <Link href="/" class="inline-flex items-center gap-1.5 font-medium hover:text-primary transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Beranda
            </Link>
            <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-foreground font-semibold truncate">FAQ</span>
        </nav>

        <div class="relative rounded-xl overflow-hidden bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-white shadow-xl shadow-primary/20">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-16 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-28 -left-10 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10 px-5 sm:px-8 py-12 sm:py-16 md:py-20 text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-white/85 mb-5"><Icon icon="mdi:help-circle-outline" class="w-4 h-4 inline-block align-middle" /> Pusat Bantuan</span>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight">Pertanyaan yang <span class="text-dry-sage">Sering Diajukan</span></h1>
                <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed">Temukan jawaban cepat seputar penggunaan platform dan latihan soal.</p>
            </div>
        </div>

        <div class="mt-6 md:mt-8 flex gap-2 overflow-x-auto pb-1">
            <a v-for="(group, gi) in faqGroups" :key="gi" :href="'#faq-' + gi" class="whitespace-nowrap inline-flex items-center gap-1.5 bg-card border border-border hover:border-primary/40 hover:text-primary hover:shadow-sm text-muted-foreground text-xs md:text-sm font-semibold px-4 py-2 rounded-full transition-all duration-200 shadow-sm min-h-10" @click.prevent="document.getElementById('faq-' + gi)?.scrollIntoView({ behavior: 'smooth', block: 'start' })">
                <Icon :icon="group.icon" class="w-4 h-4 inline-block align-middle" /> {{ group.title }}
            </a>
        </div>

        <div class="mt-6 md:mt-8 space-y-8 md:space-y-10">
            <div v-for="(group, gi) in faqGroups" :key="gi" :id="'faq-' + gi" class="scroll-mt-24">
                <h2 class="flex items-center gap-2.5 text-lg sm:text-xl font-bold text-foreground mb-4">
                    <span class="w-9 h-9 rounded-md flex items-center justify-center text-lg flex-shrink-0" :style="{ background: group.color + '22' }"><Icon :icon="group.icon" class="w-5 h-5" /></span>
                    {{ group.title }}
                </h2>
                <div class="space-y-3">
                    <div v-for="(item, ii) in group.items" :key="ii" class="bg-card rounded-xl border border-border shadow-sm transition-all duration-300 overflow-hidden" :class="openItem === gi + '-' + ii ? 'shadow-md border-primary/25' : ''">
                        <button class="w-full flex items-center justify-between gap-3 cursor-pointer select-none px-4 sm:px-5 py-4 min-h-12 text-left" @click="toggleItem(gi, ii)">
                            <span class="font-semibold text-sm md:text-base text-foreground">{{ item.q }}</span>
                            <span class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300" :class="openItem === gi + '-' + ii ? 'bg-primary text-white' : 'bg-muted text-muted-foreground'">
                                <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="openItem === gi + '-' + ii ? 'rotate-45' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </span>
                        </button>
                        <Transition name="faq">
                            <div v-if="openItem === gi + '-' + ii" class="px-4 sm:px-5 pb-4 -mt-1">
                                <p class="text-xs md:text-sm text-muted-foreground leading-relaxed" v-html="item.a"></p>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 md:mt-14 relative rounded-xl overflow-hidden bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-white text-center px-5 py-10 sm:py-12">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute bottom-0 left-1/4 w-56 h-56 bg-white/10 rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10">
                <h2 class="text-xl sm:text-2xl font-extrabold">Tidak Menemukan Jawabannya?</h2>
                <p class="mt-3 text-white/70 text-sm sm:text-base max-w-lg mx-auto">Tim kami siap membantu lewat live chat di pojok kanan bawah — klik saja ikon <Icon icon="mdi:chat-outline" class="w-4 h-4 inline-block align-middle" />.</p>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link :href="route('pages.guide')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-dry-sage text-foreground px-8 py-3.5 rounded-xl font-bold text-sm hover:bg-dry-sage/80 hover:shadow-lg hover:-translate-y-0.5 active:scale-95 transition-all duration-300"><Icon icon="mdi:book-open-page-variant" class="w-4 h-4" /> Baca Panduan</Link>
                    <Link :href="route('pages.features')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/15 text-white px-8 py-3.5 rounded-xl font-semibold text-sm hover:bg-white/15 hover:shadow-md active:scale-95 transition-all duration-300"><Icon icon="mdi:sparkles" class="w-4 h-4" /> Lihat Fitur</Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.faq-enter-active { transition: all 0.25s ease; }
.faq-leave-active { transition: all 0.2s ease; }
.faq-enter-from, .faq-leave-to { opacity: 0; max-height: 0; }
.faq-enter-to, .faq-leave-from { opacity: 1; max-height: 200px; }
</style>
