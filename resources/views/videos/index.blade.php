{{-- user/videos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Video Pembahasan - KPM Belajar Online')

@section('content')
<style>
    .vid-stagger > * {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .vid-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .vid-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .vid-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .vid-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .vid-stagger > *:nth-child(5) { animation-delay: 240ms; }
</style>

<div class="space-y-6 vid-stagger">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-pink-500/10 text-pink-500 text-xs font-semibold mb-2">🎬 Video</span>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Video Pembahasan</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Belajar lebih mudah dengan video pembahasan dari mentor kami</p>
        </div>
        <div class="flex items-center gap-2 text-sm">
            <span class="text-sm text-muted-foreground bg-card px-3 py-1.5 rounded-md shadow-sm border border-border">
                🎥 <strong>{{ $videos->total() }}</strong> Video Tersedia
            </span>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-card rounded-lg p-4 shadow-sm border border-border">
        <form method="GET" action="{{ route('videos.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul video..."
                       class="w-full pl-10 pr-4 py-2.5 border border-border rounded-md text-sm bg-muted/50 focus:bg-card focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
            </div>
            <select name="package_id" class="py-2.5 px-4 border border-border rounded-md text-sm bg-muted/50 focus:bg-card focus:border-primary outline-none transition-all sm:w-52">
                <option value="">Semua Paket</option>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>{{ $package->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-navy-light text-white px-6 py-2.5 rounded-md font-semibold text-sm hover:bg-navy transition active:scale-95">Cari</button>
            @if(request('search') || request('package_id'))
                <a href="{{ route('videos.index') }}" class="bg-card border border-border text-muted-foreground px-5 py-2.5 rounded-md font-medium text-sm hover:bg-muted transition text-center">Reset</a>
            @endif
        </form>
    </div>

    <!-- Videos Grid -->
    @if($videos->isEmpty())
        <div class="bg-card rounded-lg shadow-sm border border-border py-16 px-6 text-center">
            <div class="text-6xl mb-4">🎬</div>
            <h3 class="text-lg md:text-xl font-bold text-muted-foreground">Belum Ada Video</h3>
            <p class="text-muted-foreground mt-2 text-sm">Video pembahasan akan segera tersedia. Pantau terus halaman ini!</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            @foreach($videos as $video)
                @php
                    $accessStatus = $videoAccessMap[$video->id] ?? null;
                    $hasActiveAccess = $accessStatus === 'active';
                @endphp
                <div class="bg-card rounded-lg overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group border border-border flex flex-col">
                    <!-- Thumbnail -->
                    <a href="{{ route('videos.show', $video->id) }}" class="block relative aspect-video overflow-hidden {{ $hasActiveAccess ? 'bg-gradient-to-br from-success-500/80 to-navy-light' : 'bg-gradient-to-br from-navy to-navy-light' }}">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @endif
                        <!-- Play overlay -->
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-lg bg-white/90 backdrop-blur flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                @if($hasActiveAccess)
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-success-500 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                @else
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                @endif
                            </div>
                        </div>
                        <!-- Badge status -->
                        @if($accessStatus === 'active')
                            <span class="absolute top-3 left-3 bg-success-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md uppercase tracking-wide">✓ Akses Aktif</span>
                        @elseif($accessStatus === 'awaiting_activation')
                            <span class="absolute top-3 left-3 bg-gold-400 text-foreground text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md uppercase tracking-wide">⏳ Menunggu Aktivasi</span>
                        @elseif($accessStatus === 'pending_payment')
                            <span class="absolute top-3 left-3 bg-gold-400/20 text-gold-600 text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md uppercase tracking-wide">💳 Belum Dibayar</span>
                        @elseif($accessStatus === 'expired')
                            <span class="absolute top-3 left-3 bg-muted text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md uppercase tracking-wide">Akses Berakhir</span>
                        @else
                            <span class="absolute top-3 left-3 bg-gold-400 text-foreground text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md uppercase tracking-wide">🔒 Berbayar</span>
                        @endif
                        @if($video->hasDiscount() && !$hasActiveAccess)
                            <span class="absolute top-3 right-3 bg-danger-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md">
                                Diskon {{ $video->discount_label }}
                            </span>
                        @endif
                    </a>

                    <!-- Content -->
                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-bold text-foreground text-sm md:text-base line-clamp-2 leading-snug min-h-[2.5rem]">{{ $video->title }}</h3>
                        <p class="text-xs text-muted-foreground mt-1.5 line-clamp-2">{{ $video->description }}</p>

                        <div class="flex items-center justify-between gap-2 mt-auto pt-3 border-t border-border mt-3">
                            @if($hasActiveAccess)
                                <span class="text-success-500 font-bold text-xs sm:text-sm leading-tight">Tonton Sekarang</span>
                            @elseif($accessStatus === 'awaiting_activation')
                                <span class="text-gold-600 font-semibold text-xs leading-tight">Pembayaran diterima</span>
                            @elseif($accessStatus === 'expired')
                                <span class="text-muted-foreground font-semibold text-xs leading-tight">Masa akses habis</span>
                            @else
                                <div>
                                    @if($video->hasDiscount())
                                        <span class="text-[10px] text-muted-foreground line-through block leading-tight">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="text-foreground font-bold text-base leading-tight">Rp {{ number_format($video->final_price, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <a href="{{ route('videos.show', $video->id) }}"
                               class="text-xs font-semibold px-4 py-2 rounded-md transition-all active:scale-95 whitespace-nowrap @if($hasActiveAccess) bg-success-500 hover:bg-success-600 text-white @elseif($accessStatus === 'awaiting_activation') bg-gold-400 hover:bg-gold-500 text-foreground @elseif($accessStatus === 'pending_payment') bg-gold-400 hover:bg-gold-500 text-foreground @else bg-navy-light hover:bg-navy text-white @endif">
                                @if($hasActiveAccess)
                                    ▶ Tonton
                                @elseif($accessStatus === 'awaiting_activation')
                                    Lihat Status
                                @elseif($accessStatus === 'pending_payment')
                                    💳 Bayar
                                @elseif($accessStatus === 'expired')
                                    Beli Lagi
                                @else
                                    Lihat Detail
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
            {{ $videos->links() }}
        </div>
    @endif
</div>
@endsection