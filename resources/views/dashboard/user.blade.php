{{-- user/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard - KPM Belajar Online')

@section('content')
<style>
    .dash-stagger > * {
        animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .dash-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .dash-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .dash-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .dash-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .dash-stagger > *:nth-child(5) { animation-delay: 240ms; }
    .dash-stagger > *:nth-child(6) { animation-delay: 300ms; }
    .dash-stagger > *:nth-child(7) { animation-delay: 360ms; }
    .dash-stagger > *:nth-child(8) { animation-delay: 420ms; }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(0,162,233,.25); } 50% { box-shadow: 0 0 0 8px rgba(0,162,233,0); } }

    .anim-float { animation: float 6s ease-in-out infinite; }

    /* Hero - same as welcome */
    .dash-hero {
        background: linear-gradient(160deg, #0a0e27 0%, #161758 30%, #1e3a8a 60%, #161758 85%, #0a0e27 100%);
        position: relative; overflow: hidden;
    }
    .dash-hero::before {
        content: ''; position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 75% 20%, rgba(0,162,233,.18) 0%, transparent 55%),
            radial-gradient(ellipse 60% 50% at 15% 75%, rgba(252,198,38,.10) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 50%, rgba(99,102,241,.08) 0%, transparent 50%);
        pointer-events: none;
    }
    .dash-hero::after {
        content: ''; position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 32px 32px; pointer-events: none;
    }

    /* Card modern - same as welcome */
    .card-modern {
        background: #fff; border-radius: 20px;
        border: 1px solid rgba(0,0,0,.04);
        box-shadow: 0 1px 3px rgba(0,0,0,.03), 0 4px 20px rgba(0,0,0,.02);
        transition: all .4s cubic-bezier(.16,1,.3,1);
    }
    .card-modern:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(22,23,88,.08);
        border-color: rgba(0,162,233,.12);
    }

    /* Stat tile */
    .stat-tile {
        background: #fff; border-radius: 16px;
        border: 1px solid rgba(0,0,0,.04);
        box-shadow: 0 1px 3px rgba(0,0,0,.02);
        transition: all .35s cubic-bezier(.16,1,.3,1);
        position: relative; overflow: hidden;
    }
    .stat-tile::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--tile-accent, #00a2e9); opacity: 0; transition: opacity .3s;
    }
    .stat-tile:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.06); border-color: transparent; }
    .stat-tile:hover::before { opacity: 1; }

    /* Quick tile */
    .quick-tile {
        border-radius: 16px;
        transition: all .35s cubic-bezier(.16,1,.3,1);
    }
    .quick-tile:hover { transform: translateY(-3px); }

    /* Section badge */
    .section-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .375rem 1rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600;
    }

    /* Testimonial quote */
    .testi-quote { position: relative; padding-left: 1rem; border-left: 3px solid #00a2e9; }
    .testi-quote::before {
        content: '\201C'; position: absolute; top: -12px; left: -12px;
        font-size: 48px; color: #00a2e9; opacity: .12; font-family: Georgia, serif;
    }

    /* Btn gold */
    .btn-gold {
        background: linear-gradient(135deg, #FCC626 0%, #f5b800 100%);
        color: #161758; font-weight: 700; border-radius: 14px;
        padding: 0.875rem 2rem; display: inline-flex; align-items: center; gap: .5rem;
        transition: all .3s cubic-bezier(.16,1,.3,1);
        box-shadow: 0 4px 16px rgba(252,198,38,.25);
    }
    .btn-gold:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(252,198,38,.35);
        background: linear-gradient(135deg, #f5b800 0%, #d4a00a 100%);
    }

    /* Package card */
    .pkg-card { position: relative; overflow: hidden; border-radius: 20px; }
    .pkg-card .pkg-thumb { transition: transform .6s cubic-bezier(.16,1,.3,1); }
    .pkg-card:hover .pkg-thumb { transform: scale(1.08); }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(0,0,0,.1); border-radius: 10px; }

    @media (max-width: 640px) {
        .hero-title { font-size: 1.5rem !important; line-height: 1.25 !important; }
    }
</style>

@php
    $hour = now()->format('G');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<div class="space-y-6 md:space-y-8 dash-stagger">

    <!-- ================= HERO ================= -->
    <div class="relative rounded-3xl overflow-hidden dash-hero text-white shadow-xl shadow-navy/20">
        <!-- Floating Dots -->
        <div class="absolute top-8 left-[10%] w-2 h-2 bg-gold-400 rounded-full opacity-40 anim-float" style="animation-delay:0s"></div>
        <div class="absolute top-16 right-[15%] w-3 h-3 bg-cyan-400 rounded-full opacity-25 anim-float" style="animation-delay:1.5s"></div>
        <div class="absolute bottom-12 left-[20%] w-2 h-2 bg-success-400 rounded-full opacity-30 anim-float" style="animation-delay:3s"></div>
        <div class="absolute top-20 left-[50%] w-1.5 h-1.5 bg-white rounded-full opacity-15 anim-float" style="animation-delay:.7s"></div>

        <div class="relative z-10 p-5 sm:p-6 md:p-8 lg:p-10 flex flex-col lg:flex-row lg:items-center gap-5 lg:gap-8">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-gold-400 to-gold-500 text-foreground font-extrabold text-xl sm:text-2xl flex items-center justify-center shadow-lg shadow-black/20 flex-shrink-0" style="font-family:'Sora','Inter',sans-serif">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-white/50 text-xs sm:text-sm">{{ $greeting }},</p>
                        <h1 class="hero-title text-xl sm:text-2xl md:text-3xl font-bold truncate leading-tight" style="font-family:'Sora','Inter',sans-serif">{{ $user->name }}! 👋</h1>
                    </div>
                </div>

                <p class="text-sm sm:text-[0.94rem] text-white/50 max-w-md leading-relaxed mb-4">
                    Teruslah belajar dan tingkatkan kemampuanmu bersama <span class="text-gold-400 font-semibold">KPM Belajar Online</span>.
                </p>

                @if($user->student_name)
                    <div class="inline-flex items-center gap-2 max-w-full bg-white/[.07] backdrop-blur px-3 py-1.5 rounded-full text-[11px] sm:text-xs text-white/70 border border-white/[.1]">
                        🎓 <span class="truncate">{{ $user->student_name }} • {{ $user->student_class }} {{ $user->student_major ? '• ' . $user->student_major : '' }}</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-2.5 sm:flex-row lg:flex-col lg:w-52 flex-shrink-0">
                <a href="{{ route('packages.index') }}"
                   class="group btn-gold !py-2.5 !px-5 !rounded-md text-sm !shadow-lg !shadow-gold-400/25 text-center justify-center">
                    🚀 Mulai Belajar
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('videos.index') }}"
                   class="inline-flex items-center justify-center gap-2 bg-white/[.08] backdrop-blur border border-white/[.15] text-white px-5 py-2.5 rounded-md font-semibold text-sm hover:bg-white/[.12] hover:border-white/30 transition-all duration-300">
                    🎬 Jelajahi Video
                </a>
            </div>
        </div>
    </div>

    <!-- ================= STATISTIK ================= -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-primary/10 text-primary mb-2">📊 Statistik</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Progress Belajarmu</h2>
                <p class="text-[11px] text-muted-foreground mt-0.5 hidden sm:block">Ringkasan aktivitas belajar terkini</p>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#00a2e9">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] md:text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Paket Aktif</p>
                        <p class="text-2xl md:text-3xl font-extrabold text-foreground mt-1 leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $orders->count() }}</p>
                    </div>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">📚</div>
                </div>
                <a href="{{ route('packages.index') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-primary hover:text-foreground transition-colors">
                    Lihat paket
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#10b981">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] md:text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Total Latihan</p>
                        <p class="text-2xl md:text-3xl font-extrabold text-foreground mt-1 leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $totalAttempts }}</p>
                    </div>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-success-50 flex items-center justify-center text-xl flex-shrink-0">📝</div>
                </div>
                <a href="{{ route('practice.history') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-success-500 hover:text-success-600 transition-colors">
                    Lihat riwayat
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#FCC626">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] md:text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Nilai Terbaik</p>
                        <p class="text-2xl md:text-3xl font-extrabold text-foreground mt-1 leading-none" style="font-family:'Sora','Inter',sans-serif">{{ number_format($bestScore, 1) }}</p>
                    </div>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-gold-400/15 flex items-center justify-center text-xl flex-shrink-0">🏆</div>
                </div>
                <a href="{{ route('practice.statistics') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-gold-500 hover:text-foreground transition-colors">
                    Lihat statistik
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#161758">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] md:text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Rata-rata Nilai</p>
                        <p class="text-2xl md:text-3xl font-extrabold text-foreground mt-1 leading-none" style="font-family:'Sora','Inter',sans-serif">{{ number_format($averageScore, 1) }}</p>
                    </div>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-navy/10 flex items-center justify-center text-xl flex-shrink-0">📈</div>
                </div>
                <a href="{{ route('practice.statistics') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-navy hover:text-foreground transition-colors">
                    Lihat statistik
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- ================= AKSI CEPAT ================= -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-gold-50 text-gold-600 mb-2">⚡ Akses Cepat</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Mulai Aktivitas</h2>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
            <a href="{{ route('packages.index') }}" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">📚</div>
                <p class="text-[13px] font-bold text-foreground">Cari Paket</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Temukan yang sesuai</p>
            </a>
            <a href="{{ route('practice.history') }}" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-success-50 to-success-100/50 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">📝</div>
                <p class="text-[13px] font-bold text-foreground">Riwayat</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Pantau progress</p>
            </a>
            <a href="{{ route('orders.index') }}" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-gold-400/15 to-gold-400/5 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">📦</div>
                <p class="text-[13px] font-bold text-foreground">Pesanan</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Cek status beli</p>
            </a>
            <button onclick="toggleChat()" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-cyan-50 to-cyan-100/50 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">🤖</div>
                <p class="text-[13px] font-bold text-foreground">Tanya AI</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Asisten pintar</p>
            </button>
            <a href="{{ route('profile.edit') }}" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center col-span-2 sm:col-span-1">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100/50 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">👤</div>
                <p class="text-[13px] font-bold text-foreground">Profil Saya</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Kelola data diri</p>
            </a>
        </div>
    </div>

    <!-- ================= PUSAT BANTUAN ================= -->
    <div class="card-modern !rounded-2xl p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-50 to-primary/10 flex items-center justify-center text-xl flex-shrink-0">🧭</div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-sm" style="font-family:'Sora','Inter',sans-serif">Butuh Bantuan?</h3>
                    <p class="text-[11px] text-muted-foreground hidden sm:block">Pelajari fitur dan cara penggunaannya</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2.5 sm:flex-shrink-0">
                <a href="{{ route('pages.features') }}" class="quick-tile group flex flex-col items-center gap-1.5 bg-muted/50 hover:bg-primary/5 border border-border hover:border-primary/30 rounded-xl px-3 py-3 transition-all duration-300">
                    <span class="text-xl group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">✨</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-foreground whitespace-nowrap">Fitur</span>
                </a>
                <a href="{{ route('pages.guide') }}" class="quick-tile group flex flex-col items-center gap-1.5 bg-muted/50 hover:bg-success-50 border border-border hover:border-success-400/30 rounded-xl px-3 py-3 transition-all duration-300">
                    <span class="text-xl group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">📖</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-foreground whitespace-nowrap">Panduan</span>
                </a>
                <a href="{{ route('pages.faq') }}" class="quick-tile group flex flex-col items-center gap-1.5 bg-muted/50 hover:bg-gold-50 border border-border hover:border-gold-400/40 rounded-xl px-3 py-3 transition-all duration-300">
                    <span class="text-xl group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">❓</span>
                    <span class="text-[10px] md:text-[11px] font-semibold text-foreground whitespace-nowrap">FAQ</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ================= VIDEO PEMBAHASAN ================= -->
    @if($videos->isNotEmpty())
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="min-w-0">
                <span class="section-badge bg-cyan-50 text-cyan-600 mb-2">🎬 Video</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Video Pembahasan</h2>
                <p class="text-[11px] text-muted-foreground mt-0.5 hidden sm:block">Belajar lebih mudah dengan video pembahasan soal</p>
            </div>
            <a href="{{ route('videos.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-foreground transition-colors whitespace-nowrap flex-shrink-0">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            @foreach($videos as $video)
                @php
                    $accessStatus = $videoAccessMap[$video->id] ?? null;
                    $hasActiveAccess = $accessStatus === 'active';
                @endphp
                <a href="{{ route('videos.show', $video->id) }}" class="pkg-card card-modern group block !rounded-2xl overflow-hidden">
                    <div class="relative aspect-video overflow-hidden {{ $hasActiveAccess ? 'bg-gradient-to-br from-success-500/70 to-navy-light' : 'bg-gradient-to-br from-navy to-navy-light' }}">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="pkg-thumb w-full h-full object-cover" loading="lazy">
                        @endif
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/35 transition-colors flex items-center justify-center">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                @if($hasActiveAccess)
                                    <svg class="w-4 h-4 text-success-500 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                @endif
                            </div>
                        </div>
                        @if($accessStatus === 'active')
                            <span class="absolute top-2 left-2 bg-success-500 text-white text-[8px] md:text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow uppercase tracking-wide">✓ Aktif</span>
                        @elseif($accessStatus === 'awaiting_activation')
                            <span class="absolute top-2 left-2 bg-gold-400 text-foreground text-[8px] md:text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow uppercase tracking-wide">⏳ Menunggu</span>
                        @elseif($accessStatus === 'pending_payment')
                            <span class="absolute top-2 left-2 bg-gold-400 text-foreground text-[8px] md:text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow uppercase tracking-wide">💳 Bayar</span>
                        @elseif($accessStatus === 'expired')
                            <span class="absolute top-2 left-2 bg-muted text-white text-[8px] md:text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow uppercase tracking-wide">Berakhir</span>
                        @else
                            <span class="absolute top-2 left-2 bg-gold-400 text-foreground text-[8px] md:text-[9px] font-bold px-2.5 py-0.5 rounded-full shadow uppercase tracking-wide">🔒 Premium</span>
                        @endif
                    </div>
                    <div class="p-3 md:p-4">
                        <h4 class="text-[11px] md:text-xs font-semibold text-foreground line-clamp-2 leading-snug min-h-[1.75rem]">{{ $video->title }}</h4>
                        <div class="flex items-center justify-between gap-1 mt-2">
                            @if($hasActiveAccess)
                                <span class="text-[10px] md:text-[11px] font-bold text-success-500">Tonton Sekarang</span>
                            @elseif($accessStatus === 'awaiting_activation')
                                <span class="text-[10px] md:text-[11px] font-semibold text-gold-600">Menunggu Admin</span>
                            @elseif($accessStatus === 'pending_payment')
                                <span class="text-[10px] md:text-[11px] font-semibold text-gold-500">Selesaikan Bayar</span>
                            @elseif($accessStatus === 'expired')
                                <span class="text-[10px] md:text-[11px] font-semibold text-muted-foreground">Beli Lagi</span>
                            @else
                                <span class="text-[10px] md:text-[11px] font-bold text-foreground">Rp {{ number_format($video->final_price, 0, ',', '.') }}</span>
                            @endif
                            <span class="text-[9px] md:text-[10px] text-muted-foreground whitespace-nowrap">⏱️ {{ $video->access_duration_days }} hari</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ================= TESTIMONI ================= -->
    <div class="card-modern !rounded-2xl p-5 sm:p-6 md:p-8" id="testimonialSection">
        <div class="mb-5">
            <span class="section-badge bg-gold-50 text-gold-600 mb-2">💬 Testimoni</span>
            <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Beri Testimoni</h2>
            <p class="text-[11px] text-muted-foreground mt-0.5">Bagikan pengalaman belajarmu di KPM Belajar Online</p>
        </div>

        <div id="testimonialStatus"></div>

        <form id="testimonialForm" class="space-y-4 max-w-2xl">
            @csrf
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Rating</label>
                <div class="flex gap-1 text-2xl" id="ratingStars">
                    <button type="button" class="rating-star text-gold-400" data-value="1">★</button>
                    <button type="button" class="rating-star text-gold-400" data-value="2">★</button>
                    <button type="button" class="rating-star text-gold-400" data-value="3">★</button>
                    <button type="button" class="rating-star text-gold-400" data-value="4">★</button>
                    <button type="button" class="rating-star text-gold-400" data-value="5">★</button>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="5">
            </div>

            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Testimoni</label>
                <textarea name="content" id="testimonialContent" rows="3"
                          placeholder="Tulis pengalaman Anda menggunakan platform ini..."
                          class="w-full rounded-xl border-border focus:border-primary focus:ring-primary transition text-sm"
                          maxlength="500"></textarea>
                <p class="text-[10px] text-muted-foreground mt-1"><span id="charCount">0</span>/500 karakter</p>
            </div>

            <button type="submit" id="submitTestimonial"
                    class="btn-gold !text-sm !py-2.5 !px-6 w-full sm:w-auto justify-center">
                📤 Kirim Testimoni
            </button>
        </form>
    </div>

    <!-- ================= PAKET BANK SOAL ================= -->
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="min-w-0">
                <span class="section-badge bg-violet-50 text-violet-600 mb-2">📖 Bank Soal</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Paket Bank Soal</h2>
                <p class="text-[11px] text-muted-foreground mt-0.5 hidden sm:block">Paket yang tersedia untukmu</p>
            </div>
            <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-foreground transition-colors whitespace-nowrap flex-shrink-0">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @php
            $dashColors = [
                'bg-gradient-to-br from-violet-500 to-indigo-600',
                'bg-gradient-to-br from-emerald-500 to-green-600',
                'bg-gradient-to-br from-red-500 to-rose-600',
                'bg-gradient-to-br from-amber-400 to-orange-500',
                'bg-gradient-to-br from-cyan-500 to-blue-600',
                'bg-gradient-to-br from-pink-500 to-fuchsia-600',
                'bg-gradient-to-br from-teal-500 to-emerald-600',
                'bg-gradient-to-br from-blue-500 to-indigo-600',
            ];
            $dashIcons = ['📖', '📚', '🎯', '💡', '🚀', '🌟', '🎓', '📊', '⚡', '🏆', '💎', '🔥'];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
            @forelse($packages as $index => $package)
                @php
                    $colorClass = $dashColors[$index % count($dashColors)];
                    $icon = $dashIcons[$index % count($dashIcons)];

                    $latestOrder = \App\Models\Order::latestPaidFor(Auth::id(), $package->id);
                    $hasAccess = $latestOrder
                        && $latestOrder->enrollmentIsUnlocked()
                        && $latestOrder->isMembershipActive();
                    $needsActivation = $latestOrder
                        && $latestOrder->isMembershipActive()
                        && !$latestOrder->enrollmentIsUnlocked();
                    $needsRenewal = $latestOrder && !$latestOrder->isMembershipActive();
                    $isExpiringSoon = $latestOrder && $latestOrder->isMembershipExpiringSoon();

                    $totalCards = count($package->cards ?? []);
                    $totalQuestions = count($package->questions ?? []);
                    $videoCount = $package->videos()->where('is_active', true)->count();
                @endphp
                <div class="pkg-card card-modern flex flex-col group overflow-hidden">
                    {{-- Thumbnail --}}
                    <div class="relative h-44 overflow-hidden">
                        @if($package->thumbnail)
                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}" class="pkg-thumb w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full {{ $colorClass }} flex items-center justify-center relative">
                                <div class="absolute inset-0 opacity-10">
                                    <svg class="w-full h-full" viewBox="0 0 100 100"><circle cx="25" cy="25" r="18" fill="white" opacity=".3"/><circle cx="75" cy="35" r="22" fill="white" opacity=".2"/><circle cx="50" cy="75" r="28" fill="white" opacity=".2"/></svg>
                                </div>
                                <span class="text-6xl opacity-80 group-hover:scale-110 transition-transform duration-500">{{ $icon }}</span>
                            </div>
                        @endif

                        {{-- Badges --}}
                        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                            @if($package->is_pay_what_you_want)
                                <span class="bg-success-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">💝 Seikhlasnya</span>
                            @elseif($package->hasDiscount())
                                <span class="bg-danger-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">🔥 -{{ $package->discount_percent }}%</span>
                            @endif
                        </div>
                        <span class="absolute top-3 right-3 bg-card/90 backdrop-blur text-foreground text-[10px] font-semibold px-2.5 py-1 rounded-full shadow border border-border">⏳ {{ $package->membership_duration_label }}</span>
                        @if($hasAccess)
                            <div class="absolute bottom-3 left-3">
                                <span class="bg-success-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">✓ Dimiliki</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 md:p-5 flex flex-col flex-1">
                        {{-- Title --}}
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <h3 class="font-bold text-foreground text-[15px] leading-snug line-clamp-1">{{ $package->title }}</h3>
                            @if($hasAccess)
                                <span class="shrink-0 bg-success-100 text-success-600 text-[9px] px-2 py-0.5 rounded-full font-bold">✓ Dimiliki</span>
                            @endif
                        </div>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-1 mb-2">
                            @if($package->kelas)
                                <span class="text-[10px] bg-cyan-50 text-cyan-600 font-semibold px-2 py-0.5 rounded-full">🏫 {{ $package->kelas }}</span>
                            @endif
                            @if($package->jenjang)
                                <span class="text-[10px] bg-foreground/5 text-foreground font-semibold px-2 py-0.5 rounded-full">🎓 {{ $package->jenjang }}</span>
                            @endif
                            @if($package->hide_explanation)
                                <span class="text-[10px] bg-gold-400/15 text-gold-600 font-semibold px-2 py-0.5 rounded-full">🔒 Tanpa Pembahasan</span>
                            @endif
                        </div>

                        <p class="text-muted-foreground text-xs line-clamp-2 mb-3 leading-relaxed">{{ $package->description }}</p>

                        {{-- Stats --}}
                        <div class="flex items-center gap-3 text-[11px] text-muted-foreground mb-3">
                            <span class="inline-flex items-center gap-1">📋 {{ $totalCards }} Card</span>
                            <span class="inline-flex items-center gap-1">❓ {{ $totalQuestions }} Soal</span>
                            @if($videoCount > 0)
                                <span class="inline-flex items-center gap-1">🎬 {{ $videoCount }} Video</span>
                            @endif
                        </div>

                        {{-- Practice --}}
                        <div class="flex items-center gap-2 text-[11px] text-muted-foreground mb-3">
                            <span>⏱️ {{ $package->time_limit_minutes > 0 ? $package->time_limit_minutes . 'm' : 'Bebas' }}</span>
                            <span class="text-border">|</span>
                            <span>{{ $package->membership_duration_label }}</span>
                        </div>

                        {{-- Expiring Warning --}}
                        @if($hasAccess && $isExpiringSoon)
                            <div class="text-[10px] text-gold-600 bg-gold-400/10 border border-gold-400/20 rounded-lg px-2.5 py-1.5 text-center mb-3">
                                ⏳ Sisa {{ $latestOrder->membershipDaysRemaining() }} hari — segera perpanjang
                            </div>
                        @endif

                        {{-- Price & CTA --}}
                        <div class="mt-auto pt-3 border-t border-border">
                            <div class="flex items-center justify-between mb-3">
                                @if($hasAccess)
                                    <span class="text-success-500 font-bold text-sm">✅ Sudah Dimiliki</span>
                                @elseif($package->is_pay_what_you_want)
                                    <div>
                                        <span class="text-sm font-bold text-success-500">💝 Seikhlasnya</span>
                                        <div class="text-[10px] text-muted-foreground">Min. Rp {{ number_format($package->min_pay_amount ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                @elseif($package->hasDiscount())
                                    <div>
                                        <span class="text-lg font-bold text-danger-500">Rp {{ number_format($package->final_price, 0, ',', '.') }}</span>
                                        <div class="text-[10px] text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                                    </div>
                                @else
                                    <span class="text-lg font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            @if($hasAccess)
                                <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-success-500 text-white py-2.5 rounded-xl font-semibold text-sm hover:bg-success-600 hover:shadow-lg transition-all">📖 Mulai Belajar</a>
                            @elseif($needsActivation)
                                <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-gold-400 text-foreground py-2.5 rounded-xl font-semibold text-sm hover:bg-gold-500 hover:shadow-lg transition-all">🔑 Aktivasi Paket</a>
                            @elseif($needsRenewal)
                                <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-danger-500 text-white py-2.5 rounded-xl font-semibold text-sm hover:bg-danger-600 hover:shadow-lg transition-all">⏳ Perpanjang</a>
                            @else
                                <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-foreground text-white py-2.5 rounded-xl font-semibold text-sm hover:shadow-lg transition-all">🛒 Beli Sekarang</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 card-modern !rounded-2xl">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                    <p class="text-sm text-muted-foreground mt-2">Paket bank soal akan segera tersedia</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ================= PESANAN TERBARU ================= -->
    <div>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="min-w-0">
                <span class="section-badge bg-emerald-50 text-emerald-600 mb-2">🕐 Pesanan</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Pesanan Terbaru</h2>
                <p class="text-[11px] text-muted-foreground mt-0.5 hidden sm:block">Riwayat pembelian terkini</p>
            </div>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-foreground transition-colors whitespace-nowrap flex-shrink-0">
                Lihat Semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="card-modern !rounded-2xl p-8 text-center">
                <div class="text-5xl mb-3">🛒</div>
                <p class="font-medium text-sm text-muted-foreground">Belum ada pesanan. Yuk, mulai belajar sekarang!</p>
                <a href="{{ route('packages.index') }}" class="btn-gold !text-sm !py-2.5 !px-5 mt-4 inline-flex justify-center">Lihat Paket</a>
            </div>
        @else
            <!-- Kartu (mobile) -->
            <div class="md:hidden space-y-3">
                @foreach($recentOrders as $order)
                    <div class="card-modern !rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-foreground text-sm truncate">@if($order->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $order->item_title }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            </div>
                            @if($order->payment_status === 'paid')
                                <span class="bg-success-50 text-success-500 px-2.5 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">✅ Lunas</span>
                            @elseif($order->payment_status === 'pending')
                                <span class="bg-gold-400/20 text-gold-500 px-2.5 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">⏳ Pending</span>
                            @else
                                <span class="bg-danger-50 text-danger-500 px-2.5 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap">❌ Gagal</span>
                            @endif
                        </div>
                        <div class="mt-3 pt-3 border-t border-border">
                            @if($order->payment_status === 'paid')
                                @if($order->isVideoOrder() && $order->videoOrder)
                                    <a href="{{ route('videos.show', $order->videoOrder->video) }}" class="block text-center bg-primary text-white text-xs px-3 py-2.5 rounded-xl hover:bg-primary/90 transition font-semibold">▶️ Tonton Video</a>
                                @elseif(!$order->isVideoOrder())
                                    <a href="{{ route('packages.show', $order->package_id) }}" class="block text-center bg-success-500 text-white text-xs px-3 py-2.5 rounded-xl hover:bg-success-600 transition font-semibold">📖 Belajar</a>
                                @endif
                            @elseif($order->payment_status === 'pending' && $order->isVideoOrder() && $order->videoOrder)
                                <a href="{{ route('videos.pay', [$order->videoOrder->video, $order->videoOrder]) }}" class="block text-center bg-gold-400 text-foreground text-xs px-3 py-2.5 rounded-xl hover:bg-gold-500 transition font-semibold">💳 Bayar Sekarang</a>
                            @elseif($order->payment_status === 'pending' && !$order->isVideoOrder())
                                <a href="{{ route('orders.process-payment', $order) }}" class="w-full block text-center bg-gold-400 text-foreground text-xs px-3 py-2.5 rounded-xl hover:bg-gold-500 transition font-semibold">💳 Bayar Sekarang</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tabel (tablet ke atas) -->
            <div class="hidden md:block card-modern !rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px] text-sm">
                        <thead class="bg-muted/80 border-b border-border">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Harga</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-right text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-muted/60 transition">
                                    <td class="px-6 py-3.5 font-semibold text-foreground text-[13px]">@if($order->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $order->item_title }}</td>
                                    <td class="px-6 py-3.5 text-muted-foreground text-[13px]">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($order->payment_status === 'paid')
                                            <span class="bg-success-50 text-success-500 px-2.5 py-0.5 rounded-full text-xs font-bold">✅ Lunas</span>
                                        @elseif($order->payment_status === 'pending')
                                            <span class="bg-gold-400/20 text-gold-500 px-2.5 py-0.5 rounded-full text-xs font-bold">⏳ Pending</span>
                                        @else
                                            <span class="bg-danger-50 text-danger-500 px-2.5 py-0.5 rounded-full text-xs font-bold">❌ Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        @if($order->payment_status === 'paid')
                                            @if($order->isVideoOrder() && $order->videoOrder)
                                                <a href="{{ route('videos.show', $order->videoOrder->video) }}" class="bg-primary text-white text-xs px-3 py-1.5 rounded-xl hover:bg-primary/90 transition inline-block font-semibold">▶️ Tonton</a>
                                            @elseif(!$order->isVideoOrder())
                                                <a href="{{ route('packages.show', $order->package_id) }}" class="bg-success-500 text-white text-xs px-3 py-1.5 rounded-xl hover:bg-success-600 transition inline-block font-semibold">Belajar</a>
                                            @endif
                                        @elseif($order->payment_status === 'pending' && $order->isVideoOrder() && $order->videoOrder)
                                            <a href="{{ route('videos.pay', [$order->videoOrder->video, $order->videoOrder]) }}" class="bg-gold-400 text-foreground text-xs px-3 py-1.5 rounded-xl hover:bg-gold-500 transition inline-block font-semibold">Bayar</a>
                                        @elseif($order->payment_status === 'pending' && !$order->isVideoOrder())
                                            <a href="{{ route('orders.process-payment', $order) }}" class="bg-gold-400 text-foreground text-xs px-3 py-1.5 rounded-xl hover:bg-gold-500 transition inline-block font-semibold">Bayar</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Testimonial
document.addEventListener('DOMContentLoaded', function() {
    const isUser = {{ auth()->check() && auth()->user()->role === 'user' ? 'true' : 'false' }};
    if (!isUser) return;

    const form = document.getElementById('testimonialForm');
    if (!form) return;

    // Load user's testimonial
    fetch('/testimonials/my-testimonial')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const status = document.getElementById('testimonialStatus');
                if (data.data.is_approved) {
                    status.innerHTML = `
                        <div class="bg-success-50 border-l-4 border-success-500 p-4 rounded-2xl mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">✅</span>
                                <div>
                                    <p class="font-semibold text-success-600">Testimoni sudah disetujui!</p>
                                    <p class="text-sm text-muted-foreground">"${escapeHtml(data.data.content)}"</p>
                                    <p class="text-xs text-muted-foreground mt-1">Rating: ${'★'.repeat(data.data.rating)}${'☆'.repeat(5 - data.data.rating)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    form.style.display = 'none';
                } else {
                    status.innerHTML = `
                        <div class="bg-gold-400/10 border-l-4 border-gold-400 p-4 rounded-2xl mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⏳</span>
                                <div>
                                    <p class="font-semibold text-gold-600">Testimoni menunggu persetujuan</p>
                                    <p class="text-sm text-muted-foreground">"${escapeHtml(data.data.content)}"</p>
                                </div>
                            </div>
                        </div>
                    `;
                    form.style.display = 'none';
                }
            }
        })
        .catch(err => console.error('Error loading testimonial:', err));

    // Rating stars
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            ratingInput.value = value;
            stars.forEach(s => {
                const val = parseInt(s.dataset.value);
                if (val <= value) {
                    s.classList.remove('text-muted-foreground');
                    s.classList.add('text-gold-400');
                } else {
                    s.classList.remove('text-gold-400');
                    s.classList.add('text-muted-foreground');
                }
            });
        });

        star.addEventListener('mouseenter', function() {
            const value = parseInt(this.dataset.value);
            stars.forEach(s => {
                const val = parseInt(s.dataset.value);
                if (val <= value) {
                    s.classList.remove('text-muted-foreground');
                    s.classList.add('text-gold-300');
                } else {
                    s.classList.remove('text-gold-300');
                    s.classList.add('text-muted-foreground');
                }
            });
        });

        star.addEventListener('mouseleave', function() {
            const current = parseInt(ratingInput.value);
            stars.forEach(s => {
                const val = parseInt(s.dataset.value);
                if (val <= current) {
                    s.classList.remove('text-gold-300', 'text-muted-foreground');
                    s.classList.add('text-gold-400');
                } else {
                    s.classList.remove('text-gold-300', 'text-gold-400');
                    s.classList.add('text-muted-foreground');
                }
            });
        });
    });

    // Character counter
    const content = document.getElementById('testimonialContent');
    const charCount = document.getElementById('charCount');
    if (content && charCount) {
        content.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Submit form
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitTestimonial');
        const content = document.getElementById('testimonialContent');
        const rating = document.getElementById('ratingInput');

        if (!content.value.trim()) {
            alert('Silakan tulis testimoni Anda.');
            return;
        }

        btn.disabled = true;
        btn.textContent = '⏳ Mengirim...';

        fetch('/testimonials/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                content: content.value.trim(),
                rating: parseInt(rating.value)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = document.getElementById('testimonialStatus');
                status.innerHTML = `
                    <div class="bg-success-50 border-l-4 border-success-500 p-4 rounded-2xl mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <div>
                                <p class="font-semibold text-success-600">${escapeHtml(data.message)}</p>
                                <p class="text-sm text-muted-foreground">"${escapeHtml(content.value.trim())}"</p>
                            </div>
                        </div>
                    </div>
                `;
                form.style.display = 'none';
            } else {
                alert(data.message || 'Gagal mengirim testimoni.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            console.error(err);
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = '📤 Kirim Testimoni';
        });
    });
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
