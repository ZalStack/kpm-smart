{{-- pages/guide.blade.php --}}
@extends('layouts.app')

@section('title', 'Panduan Penggunaan - KPM Belajar Online')

@section('content')
<!-- Breadcrumb -->
<nav class="flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground mb-4 md:mb-5" aria-label="Breadcrumb">
    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 font-medium hover:text-primary transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Beranda
    </a>
    <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-foreground font-semibold truncate">Panduan Penggunaan</span>
</nav>

@php
    $steps = [
        [
            'icon' => '📝', 'color' => '#00a2e9', 'title' => 'Daftar / Masuk',
            'desc' => 'Klik "Daftar" di pojok kanan atas, isi nama, email, dan password. Sudah punya akun? Langsung "Masuk".',
        ],
        [
            'icon' => '🛒', 'color' => '#009a4b', 'title' => 'Pilih Paket atau Video',
            'desc' => 'Jelajahi Paket Bank Soal atau Video Pembahasan di menu. Klik "Beli Sekarang" pada yang kamu inginkan.',
        ],
        [
            'icon' => '💳', 'color' => '#FCC626', 'title' => 'Selesaikan Pembayaran',
            'desc' => 'Pilih metode bayar (transfer, e-wallet, QRIS, minimarket) di halaman pembayaran Midtrans, lalu ikuti instruksinya.',
        ],
        [
            'icon' => '🔑', 'color' => '#f97316', 'title' => 'Aktivasi Akses',
            'desc' => 'Paket bank soal: masukkan Enroll Key dari halaman detail pesanan. Video: tunggu admin mengaktifkan aksesmu (biasanya cepat setelah pembayaran terverifikasi).',
        ],
        [
            'icon' => '🚀', 'color' => '#ec4899', 'title' => 'Mulai Belajar!',
            'desc' => 'Akses aktif? Kerjakan latihan soal atau tonton video pembahasan. Progress belajarmu tercatat otomatis di dashboard.',
        ],
    ];

    $topics = [
        [
            'icon' => '🧑‍🎓', 'title' => 'Mengerjakan Latihan Soal',
            'items' => [
                'Buka menu <strong>Latihan</strong> lalu pilih paket yang sudah kamu beli.',
                'Pilih jumlah soal atau mulai sesi latihan.',
                'Jawab soal satu per satu — pembahasan muncul setelah menjawab.',
                'Selesai? Nilai dan riwayat tersimpan otomatis di <strong>Statistik</strong>.',
            ],
        ],
        [
            'icon' => '🎬', 'title' => 'Menonton Video Pembahasan',
            'items' => [
                'Buka menu <strong>Video</strong> untuk melihat semua video yang tersedia.',
                'Status akses ditampilkan pada tiap kartu: Aktif, Menunggu Aktivasi, atau Premium (belum dibeli).',
                'Setelah membeli & akses diaktifkan admin, klik video lalu tekan play.',
                'Masa akses video mengikuti durasi paket (misal 30 hari sejak aktivasi).',
            ],
        ],
        [
            'icon' => '📦', 'title' => 'Mengelola Pesanan',
            'items' => [
                'Semua transaksi ada di menu <strong>Pesanan</strong>.',
                'Pesanan pending bisa dilanjutkan lewat tombol <strong>Bayar</strong>.',
                'Setelah lunas, status berubah menjadi <strong>Lunas</strong> beserta petunjuk aktivasi.',
                'Simpan nomor pesanan jika perlu menghubungi bantuan.',
            ],
        ],
        [
            'icon' => '💬', 'title' => 'Menghubungi Bantuan',
            'items' => [
                'Klik tombol chat hijau/biru di pojok kanan bawah layar.',
                'Tulis pertanyaanmu — tidak perlu login untuk bertanya.',
                'Jawaban admin akan muncul di jendela chat yang sama.',
                'Untuk kendala pembayaran, sertakan nomor pesanan (contoh: ORD-...).',
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
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-white/85 mb-5">📖 Panduan Penggunaan</span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight">Cara Menggunakan <span class="text-gold-400">KPM Belajar Online</span></h1>
        <p class="mt-4 text-sm sm:text-base text-white/70 leading-relaxed">Ikuti 5 langkah mudah di bawah ini untuk mulai belajar. Semua bisa diselesaikan kurang dari 10 menit.</p>
    </div>
</div>

<!-- Timeline Langkah -->
<div class="mt-8 md:mt-12 max-w-3xl mx-auto">
    <ol class="relative space-y-4 md:space-y-6">
        <!-- garis vertikal -->
        <span class="absolute left-[27px] top-4 bottom-4 w-0.5 bg-gradient-to-b from-primary/40 via-gold-400/40 to-success-500/40 hidden sm:block" aria-hidden="true"></span>
        @foreach($steps as $i => $step)
            <li class="relative flex gap-4 items-start bg-card rounded-lg border border-border shadow-sm hover:shadow-md transition-shadow duration-300 p-4 sm:p-5">
                <div class="relative z-10 w-14 h-14 rounded-lg flex items-center justify-center text-2xl flex-shrink-0 shadow-sm"
                     style="background: color-mix(in srgb, {{ $step['color'] }} 14%, white);">{{ $step['icon'] }}</div>
                <div class="min-w-0 pt-0.5">
                    <p class="text-[11px] font-extrabold uppercase tracking-widest" style="color: {{ $step['color'] }};">Langkah {{ $i + 1 }}</p>
                    <h3 class="font-bold text-foreground text-base md:text-lg mt-0.5">{{ $step['title'] }}</h3>
                    <p class="text-muted-foreground text-xs md:text-sm mt-1.5 leading-relaxed">{!! $step['desc'] !!}</p>
                </div>
            </li>
        @endforeach
    </ol>
</div>

<!-- Topik Detail -->
<div class="mt-10 md:mt-14">
    <div class="text-center mb-6 md:mb-8">
        <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-foreground">🔍 Penjelasan Lebih Detail</h2>
        <p class="text-xs sm:text-sm text-muted-foreground mt-1">Panduan singkat untuk fitur utama</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
        @foreach($topics as $topic)
            <div class="bg-card rounded-lg border border-border shadow-sm p-5 md:p-6 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-primary/15 to-navy-light/10 flex items-center justify-center text-xl flex-shrink-0">{{ $topic['icon'] }}</div>
                    <h3 class="font-bold text-foreground text-base md:text-lg">{{ $topic['title'] }}</h3>
                </div>
                <ul class="space-y-2.5">
                    @foreach($topic['items'] as $item)
                        <li class="flex gap-2.5 text-xs md:text-sm text-muted-foreground leading-relaxed">
                            <svg class="w-4 h-4 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>{!! $item !!}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</div>

<!-- Bantuan CTA -->
<div class="mt-10 md:mt-14 relative rounded-lg overflow-hidden bg-gradient-to-br from-navy via-navy-light to-navy text-white text-center px-5 py-10 sm:py-12">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 right-1/4 w-56 h-56 bg-primary/15 rounded-full blur-3xl"></div>
    </div>
    <div class="relative z-10">
        <h2 class="text-xl sm:text-2xl font-extrabold">Masih Ada yang Bingung?</h2>
        <p class="mt-3 text-white/70 text-sm sm:text-base max-w-lg mx-auto">Cek halaman FAQ, atau langsung tanya lewat live chat di pojok kanan bawah.</p>
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('pages.faq') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gold-400 text-foreground px-8 py-3.5 rounded-lg font-bold text-sm hover:bg-gold-500 hover:-translate-y-0.5 transition-all duration-300">❓ Lihat FAQ</a>
            <a href="{{ route('pages.features') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/15 text-white px-8 py-3.5 rounded-lg font-semibold text-sm hover:bg-white/15 transition-all duration-300">✨ Jelajahi Fitur</a>
        </div>
    </div>
</div>
@endsection
