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

    @keyframes fadeUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

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

    .quick-tile {
        border-radius: 16px;
        transition: all .35s cubic-bezier(.16,1,.3,1);
    }
    .quick-tile:hover { transform: translateY(-3px); }

    .section-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .375rem 1rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600;
    }

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

    .pkg-card { position: relative; overflow: hidden; border-radius: 20px; }
    .pkg-card .pkg-thumb { transition: transform .6s cubic-bezier(.16,1,.3,1); }
    .pkg-card:hover .pkg-thumb { transform: scale(1.08); }

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

    <!-- HERO -->
    <div class="relative rounded-3xl overflow-hidden dash-hero text-white shadow-xl shadow-navy/20">
        <div class="absolute top-8 left-[10%] w-2 h-2 bg-gold-400 rounded-full opacity-40 anim-float" style="animation-delay:0s"></div>
        <div class="absolute top-16 right-[15%] w-3 h-3 bg-cyan-400 rounded-full opacity-25 anim-float" style="animation-delay:1.5s"></div>
        <div class="absolute bottom-12 left-[20%] w-2 h-2 bg-success-400 rounded-full opacity-30 anim-float" style="animation-delay:3s"></div>

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

                @if($user->name)
                    <div class="inline-flex items-center gap-2 max-w-full bg-white/[.07] backdrop-blur px-3 py-1.5 rounded-full text-[11px] sm:text-xs text-white/70 border border-white/[.1]">
                        🎓 <span class="truncate">{{ $user->name }} • {{ $user->student_class }} {{ $user->bidang ? '• ' . $user->bidang : '' }}</span>
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-2.5 sm:flex-row lg:flex-col lg:w-52 flex-shrink-0">
                <a href="{{ route('packages.index') }}"
                   class="group btn-gold !py-2.5 !px-5 !rounded-md text-sm !shadow-lg !shadow-gold-400/25 text-center justify-center">
                    🚀 Mulai Belajar
                    <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- STATISTIK -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-primary/10 text-primary mb-2">📊 Statistik</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Progress Belajarmu</h2>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
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

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#00a2e9">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] md:text-[11px] text-muted-foreground font-semibold uppercase tracking-wider">Paket Tersedia</p>
                        <p class="text-2xl md:text-3xl font-extrabold text-foreground mt-1 leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $packages->count() }}</p>
                    </div>
                    <div class="w-10 h-10 md:w-11 md:h-11 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">📚</div>
                </div>
                <a href="{{ route('packages.index') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] md:text-xs font-semibold text-primary hover:text-foreground transition-colors">
                    Lihat paket
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- AKSI CEPAT -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-gold-50 text-gold-600 mb-2">⚡ Akses Cepat</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Mulai Aktivitas</h2>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
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
            <button onclick="toggleChat()" class="quick-tile card-modern group !rounded-2xl p-4 md:p-5 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-cyan-50 to-cyan-100/50 flex items-center justify-center text-2xl group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">🤖</div>
                <p class="text-[13px] font-bold text-foreground">Tanya AI</p>
                <p class="text-[10px] text-muted-foreground mt-0.5 hidden sm:block">Asisten pintar</p>
            </button>
        </div>
    </div>

    <!-- PUSAT BANTUAN -->
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

    <!-- PAKET BANK SOAL -->
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
            ];
            $dashIcons = ['📖', '📚', '🎯', '💡', '🚀', '🌟'];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
            @forelse($packages as $index => $package)
                @php
                    $colorClass = $dashColors[$index % count($dashColors)];
                    $icon = $dashIcons[$index % count($dashIcons)];
                    $totalCards = count($package->cards ?? []);
                    $totalQuestions = count($package->questions ?? []);
                @endphp
                <a href="{{ route('packages.show', $package->id) }}" class="pkg-card card-modern group block !rounded-2xl overflow-hidden">
                    <div class="relative h-40 overflow-hidden">
                        @if($package->thumbnail)
                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}" class="pkg-thumb w-full h-full object-cover" loading="lazy">
                        @else
                            <div class="w-full h-full {{ $colorClass }} flex items-center justify-center">
                                <span class="text-5xl opacity-80 group-hover:scale-110 transition-transform duration-500">{{ $icon }}</span>
                            </div>
                        @endif
                        <span class="absolute top-3 right-3 bg-card/90 backdrop-blur text-foreground text-[10px] font-semibold px-2.5 py-1 rounded-full shadow border border-border">
                            @php $sch = $package->schedule_status; @endphp
                            @if($sch === 'active') 🟢 Berlangsung
                            @elseif($sch === 'upcoming') ⏳ Akan Datang
                            @elseif($sch === 'expired') ⛔ Berakhir
                            @else ♾️ Tanpa Batas
                            @endif
                        </span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-foreground text-[14px] leading-snug line-clamp-1">{{ $package->title }}</h3>
                        <div class="flex items-center gap-3 text-[11px] text-muted-foreground mt-2">
                            <span>📋 {{ $totalCards }} Card</span>
                            <span>❓ {{ $totalQuestions }} Soal</span>
                            @if($package->bidang)<span>📂 {{ $package->bidang }}</span>@endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16 card-modern !rounded-2xl">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                    <p class="text-sm text-muted-foreground mt-2">Paket bank soal akan segera tersedia</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
