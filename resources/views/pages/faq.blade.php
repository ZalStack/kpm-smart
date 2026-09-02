{{-- pages/faq.blade.php --}}
@extends('layouts.app')

@section('title', 'FAQ - KPM Belajar Online')

@section('content')
<!-- Breadcrumb -->
<nav class="flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground mb-4 md:mb-5" aria-label="Breadcrumb">
    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 font-medium hover:text-primary transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Beranda
    </a>
    <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-foreground font-semibold truncate">FAQ</span>
</nav>

@php
    $faqGroups = [
        [
            'icon' => '🧩', 'title' => 'Umum', 'color' => '#00a2e9',
            'items' => [
                ['Apa itu KPM Belajar Online?', 'KPM Belajar Online adalah platform belajar berbayar yang menyediakan bank soal latihan dengan pembahasan lengkap serta video pembahasan soal untuk mendukung belajar mandiri.'],
                ['Apakah perlu membuat akun untuk membeli?', 'Ya. Kamu perlu mendaftar (gratis) terlebih dahulu agar pembelian, akses, dan progres belajarmu tersimpan rapi di satu akun.'],
                ['Apakah bisa diakses dari HP?', 'Bisa! Tampilan platform sudah responsif dan nyaman digunakan dari HP, tablet, maupun laptop.'],
            ],
        ],
        [
            'icon' => '💳', 'title' => 'Pembelian & Pembayaran', 'color' => '#009a4b',
            'items' => [
                ['Metode pembayaran apa saja yang tersedia?', 'Pembayaran diproses melalui Midtrans: transfer bank (BCA, BNI, BRI, Mandiri), e-wallet (GoPay, ShopeePay), QRIS, kartu kredit/debit, hingga Indomaret/Alfamart.'],
                ['Sudah bayar tapi status masih "Pending"?', 'Untuk beberapa metode (terutama transfer manual/minimarket), verifikasi butuh waktu beberapa menit. Halaman akan diperbarui otomatis setelah Midtrans mengonfirmasi. Jika lebih dari 1 jam tetap pending, hubungi kami via live chat dengan menyertakan nomor pesanan.'],
                ['Bisakah melanjutkan pembayaran yang tertunda?', 'Bisa. Buka menu <strong>Pesanan</strong>, temukan pesanan berstatus pending, lalu klik tombol <strong>Bayar</strong> untuk membuka kembali halaman pembayaran.'],
                ['Apakah harga sudah termasuk pajak?', 'Harga yang tampil adalah harga final yang kamu bayar — tidak ada biaya tersembunyi.'],
            ],
        ],
        [
            'icon' => '🔑', 'title' => 'Aktivasi & Akses', 'color' => '#f97316',
            'items' => [
                ['Bagaimana cara mengaktifkan paket bank soal?', 'Setelah pembayaran lunas, buka detail pesanan untuk melihat <strong>Enroll Key</strong>. Masukkan key tersebut pada halaman paket untuk membuka akses membership sesuai durasi.'],
                ['Kenapa video saya belum bisa ditonton padahal sudah bayar?', 'Setelah pembayaran terverifikasi, akses video perlu diaktifkan oleh admin (biasanya cepat). Kartu video akan menampilkan status <strong>"Menunggu Aktivasi"</strong> — begitu aktif, statusnya berubah menjadi <strong>"Aktif"</strong> dan tombol Tonton muncul otomatis.'],
                ['Berapa lama masa akses saya?', 'Tergantung produk: paket bank soal memiliki durasi membership (misal 30/90 hari), sedangkan video pembahasan dihitung sejak akses diaktifkan (misal 30 hari). Sisa waktu selalu tampil di dashboard dan halaman pesanan.'],
                ['Akses saya habis, apakah harus beli lagi?', 'Ya, akses bersifat sewa-masa-aktif. Kamu bisa memperpanjang langsung dari kartu paket/video lewat tombol <strong>Perpanjang</strong>.'],
            ],
        ],
        [
            'icon' => '🎬', 'title' => 'Video Pembahasan', 'color' => '#ec4899',
            'items' => [
                ['Apakah video bisa diunduh?', 'Video hanya dapat ditonton streaming di platform agar kualitas dan hak konten tetap terjaga.'],
                ['Kenapa pemutar video tidak muncul?', 'Pastikan koneksi internet stabil dan browser kamu versi terbaru. Jika masih bermasalah, coba muat ulang halaman atau ganti browser.'],
            ],
        ],
        [
            'icon' => '⚙️', 'title' => 'Akun & Teknis', 'color' => '#7c3aed',
            'items' => [
                ['Bagaimana jika lupa password?', 'Klik <strong>"Lupa Password?"</strong> di halaman login, masukkan emailmu, jawab pertanyaan verifikasi, lalu buat password baru.'],
                ['Apakah data pribadi saya aman?', 'Kami hanya menyimpan data yang diperlukan (nama, email, sekolah) dan tidak membagikannya ke pihak ketiga. Pembayaran sepenuhnya ditangani Midtrans dengan enkripsi standar industri.'],
            ],
        ],
    ];
@endphp

<!-- Hero -->
<div class="relative rounded-lg overflow-hidden bg-gradient-to-br from-navy via-navy-light to-navy text-white shadow-xl shadow-navy/20">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-24 -right-16 w-72 h-72 bg-primary/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-28 -left-10 w-80 h-80 bg-gold-400/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative z-10 px-5 sm:px-8 py-12 sm:py-16 md:py-20 text-center max-w-3xl mx-auto">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-white/85 mb-5">❓ Pusat Bantuan</span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight">Pertanyaan yang <span class="text-gold-400">Sering Diajukan</span></h1>
        <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed">Temukan jawaban cepat seputar pembelian, aktivasi, video, dan penggunaan platform.</p>
    </div>
</div>

<!-- Quick nav -->
<div class="mt-6 md:mt-8 flex gap-2 overflow-x-auto scrollbar-none pb-1 -mx-1 px-1">
    @foreach($faqGroups as $gi => $group)
        <a href="#faq-group-{{ $gi }}" class="whitespace-nowrap inline-flex items-center gap-1.5 bg-card border border-border hover:border-primary/40 hover:text-navy-light text-muted-foreground text-xs md:text-sm font-semibold px-4 py-2 rounded-full transition-colors shadow-sm">
            {{ $group['icon'] }} {{ $group['title'] }}
        </a>
    @endforeach
</div>

<!-- Accordion -->
<div class="mt-6 md:mt-8 space-y-8 md:space-y-10">
    @foreach($faqGroups as $gi => $group)
        <div id="faq-group-{{ $gi }}" class="scroll-mt-24">
            <h2 class="flex items-center gap-2.5 text-lg sm:text-xl font-bold text-foreground mb-4">
                <span class="w-9 h-9 rounded-md flex items-center justify-center text-lg flex-shrink-0" style="background: color-mix(in srgb, {{ $group['color'] }} 14%, white);">{{ $group['icon'] }}</span>
                {{ $group['title'] }}
            </h2>
            <div class="space-y-3">
                @foreach($group['items'] as $item)
                    <details class="group faq-item bg-card rounded-lg border border-border shadow-sm open:shadow-md open:border-primary/25 transition-all duration-300 overflow-hidden">
                        <summary class="flex items-center justify-between gap-3 cursor-pointer select-none list-none px-4 sm:px-5 py-4">
                            <span class="font-semibold text-sm md:text-base text-foreground group-open:text-navy-light">{{ $item[0] }}</span>
                            <span class="w-7 h-7 rounded-full bg-muted group-open:bg-primary text-muted-foreground group-open:text-white flex items-center justify-center flex-shrink-0 transition-all duration-300">
                                <svg class="w-3.5 h-3.5 transition-transform duration-300 group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </span>
                        </summary>
                        <div class="px-4 sm:px-5 pb-4 -mt-1">
                            <p class="text-xs md:text-sm text-muted-foreground leading-relaxed">{!! $item[1] !!}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<!-- CTA -->
<div class="mt-10 md:mt-14 relative rounded-lg overflow-hidden bg-gradient-to-br from-navy via-navy-light to-navy text-white text-center px-5 py-10 sm:py-12">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute bottom-0 left-1/4 w-56 h-56 bg-success-500/15 rounded-full blur-3xl"></div>
    </div>
    <div class="relative z-10">
        <h2 class="text-xl sm:text-2xl font-extrabold">Tidak Menemukan Jawabannya?</h2>
        <p class="mt-3 text-white/70 text-sm sm:text-base max-w-lg mx-auto">Tim kami siap membantu lewat live chat di pojok kanan bawah — klik saja ikon 💬.</p>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('pages.guide') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gold-400 text-foreground px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-gold-500 hover:-translate-y-0.5 transition-all duration-300">📖 Baca Panduan</a>
            <a href="{{ route('pages.features') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/15 text-white px-8 py-3.5 rounded-lg font-semibold text-sm hover:bg-white/15 transition-all duration-300">✨ Lihat Fitur</a>
        </div>
    </div>
</div>

<style>
    html { scroll-behavior: smooth; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    summary::-webkit-details-marker { display: none; }
</style>
@endsection
