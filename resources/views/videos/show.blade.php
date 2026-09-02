@extends('layouts.app')

@section('title', $video->title . ' - Video Pembahasan')

@section('content')
@php
    $accessStatus = $videoOrder?->accessStatus();
    $hasActiveAccess = $accessStatus === 'active';
    $awaitingActivation = $accessStatus === 'awaiting_activation';

    $youtubeId = null;
    $vimeoId = null;
    $gdriveId = null;

    if ($video->video_url) {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]+)/i', $video->video_url, $m)) {
            $youtubeId = $m[1];
        } elseif (preg_match('/vimeo\.com\/(\d+)/i', $video->video_url, $m)) {
            $vimeoId = $m[1];
        } elseif (preg_match('/drive\.google\.com\/file\/d\/([\w-]+)/i', $video->video_url, $m)) {
            $gdriveId = $m[1];
        }
    }

    $hasPlyrSupport = $youtubeId || $vimeoId || $video->video_file;
@endphp

<div class="max-w-5xl mx-auto space-y-5">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('videos.index') }}" class="text-muted-foreground hover:text-navy-light transition font-medium inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Video Pembahasan
        </a>
        <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-muted-foreground font-medium truncate">{{ \Illuminate\Support\Str::limit($video->title, 40) }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Player / Paywall -->
        <div class="lg:col-span-2 space-y-4">
            {{-- ======================== PLAYER AREA ======================== --}}
            <div class="bg-black rounded-lg overflow-hidden shadow-xl relative aspect-video" id="player-wrapper">
                @if($hasActiveAccess)
                    {{-- === ACTIVE ACCESS: Show Player === --}}
                    @if($video->video_file)
                        <video id="plyr-video" playsinline preload="metadata"
                               poster="{{ $video->thumbnail ? asset('storage/' . $video->thumbnail) : '' }}">
                            <source src="{{ asset('storage/' . $video->video_file) }}" type="video/mp4">
                        </video>
                    @elseif($youtubeId)
                        <div id="plyr-youtube" data-plyr-provider="youtube" data-plyr-embed-id="{{ $youtubeId }}"></div>
                    @elseif($vimeoId)
                        <div id="plyr-vimeo" data-plyr-provider="vimeo" data-plyr-embed-id="{{ $vimeoId }}"></div>
                    @elseif($gdriveId)
                        <iframe src="https://drive.google.com/file/d/{{ $gdriveId }}/preview"
                                class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen title="{{ $video->title }}"></iframe>
                    @elseif($video->video_url)
                        <div class="w-full h-full flex flex-col items-center justify-center text-white p-6 text-center bg-gradient-to-br from-navy to-navy-light">
                            <div class="w-16 h-16 rounded-lg bg-white/10 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            </div>
                            <p class="font-semibold mb-1">Video tersedia di tautan eksternal</p>
                            <p class="text-white/60 text-xs mb-4">Klik tombol di bawah untuk menonton</p>
                            <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 bg-gold-400 text-foreground px-6 py-3 rounded-md font-bold hover:bg-gold-500 transition shadow-lg shadow-gold-400/20 active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                Buka Video
                            </a>
                        </div>
                    @endif

                @elseif($awaitingActivation)
                    {{-- === AWAITING ACTIVATION === --}}
                    <div class="absolute inset-0 w-full h-full">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="w-full h-full object-cover blur-sm scale-110 opacity-40">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-navy to-navy-light"></div>
                        @endif
                    </div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-black/60 backdrop-blur-[2px]">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gold-400/20 backdrop-blur-sm border border-gold-400/40 flex items-center justify-center mb-4 animate-pulse-soft">
                            <span class="text-3xl md:text-4xl">⏳</span>
                        </div>
                        <h3 class="text-white font-bold text-lg md:text-xl">Menunggu Aktivasi Admin</h3>
                        <p class="text-white/60 text-xs md:text-sm mt-2 max-w-md leading-relaxed">
                            Pembayaran Anda sudah diterima. Video ini perlu diaktifkan oleh tim admin sebelum dapat ditonton.
                        </p>
                        <div class="mt-3 bg-white/10 backdrop-blur rounded-md px-4 py-2.5 border border-white/10">
                            <p class="text-white/50 text-[10px] uppercase tracking-wider font-semibold">Estimasi Aktivasi</p>
                            <p class="text-white font-bold text-sm mt-0.5">Maksimal 1×24 jam</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 mt-5">
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-5 py-2.5 rounded-md font-semibold transition active:scale-95 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                                Lihat Status Pesanan
                            </a>
                            <button onclick="location.reload()" class="inline-flex items-center justify-center gap-2 bg-gold-400/20 hover:bg-gold-400/30 border border-gold-400/30 text-gold-400 px-5 py-2.5 rounded-md font-semibold transition active:scale-95 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                                Refresh Status
                            </button>
                        </div>
                    </div>

                @else
                    {{-- === PAYWALL: No Access / Expired === --}}
                    <div class="absolute inset-0 w-full h-full">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="w-full h-full object-cover blur-sm scale-110 opacity-50">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-navy to-navy-light blur-sm scale-110 opacity-80"></div>
                        @endif
                    </div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 bg-gradient-to-t from-black/70 via-black/40 to-black/30">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center mb-4">
                            @if($accessStatus === 'expired')
                                <svg class="w-7 h-7 md:w-8 md:h-8 text-white/80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-7 h-7 md:w-8 md:h-8 text-white/80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            @endif
                        </div>
                        @if($accessStatus === 'expired')
                            <h3 class="text-white font-bold text-lg md:text-xl">Masa Akses Berakhir</h3>
                            <p class="text-white/60 text-xs md:text-sm mt-1 max-w-sm">Perpanjang akses untuk melanjutkan menonton selama {{ $video->access_duration_days }} hari</p>
                        @else
                            <h3 class="text-white font-bold text-lg md:text-xl">Konten Premium</h3>
                            <p class="text-white/60 text-xs md:text-sm mt-1 max-w-sm">Beli akses untuk menonton video pembahasan ini selama {{ $video->access_duration_days }} hari</p>
                        @endif

                        @if(isset($pendingOrder) && $pendingOrder)
                            <a href="{{ route('videos.pay', ['video' => $video->id, 'videoOrder' => $pendingOrder->id]) }}"
                               class="mt-5 inline-flex items-center gap-2 bg-gold-400 text-foreground px-6 md:px-8 py-3 rounded-md font-bold hover:bg-gold-500 transition shadow-lg shadow-gold-400/30 active:scale-95">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                                Lanjutkan Pembayaran
                            </a>
                        @else
                            @if($video->is_pay_what_you_want)
                                <form action="{{ route('videos.order', $video->id) }}" method="POST" class="mt-5 w-full max-w-sm" id="pwYWForm">
                                    @csrf
                                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 border border-white/20">
                                        <p class="text-white/70 text-xs mb-2">Bayar Seikhlasnya — Min. Rp {{ number_format($video->minimumPayAmount(), 0, ',', '.') }}</p>
                                        <div class="flex gap-1.5 mb-3" id="pwYWQuickButtons">
                                            @php
                                                $min = $video->minimumPayAmount();
                                                $quickAmounts = [$min, $min + 10000, $min + 25000, $min + 50000];
                                            @endphp
                                            @foreach($quickAmounts as $amt)
                                                <button type="button" onclick="setPWYWAmount({{ $amt }})"
                                                        class="flex-1 bg-white/10 hover:bg-white/20 text-white text-[10px] md:text-[11px] font-semibold py-1.5 rounded-md transition pwYW-quick">
                                                    Rp {{ number_format($amt, 0, ',', '.') }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 text-sm font-semibold">Rp</span>
                                            <input type="number" name="amount" id="pwYWAmountInput" min="{{ $video->minimumPayAmount() }}" step="1000"
                                                   value="{{ $video->minimumPayAmount() }}"
                                                   class="w-full bg-white/10 border border-white/20 rounded-md pl-10 pr-4 py-2.5 text-white font-bold text-lg focus:outline-none focus:border-gold-400 focus:ring-1 focus:ring-gold-400/50 transition placeholder-white/30"
                                                   placeholder="Jumlah" required>
                                        </div>
                                        <button type="submit"
                                                class="w-full mt-3 bg-gold-400 text-foreground py-2.5 rounded-md font-bold hover:bg-gold-500 transition shadow-lg shadow-gold-400/30 active:scale-95 text-sm flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                            Bayar Seikhlasnya
                                        </button>
                                    </div>
                                </form>
                            @else
                                <form action="{{ route('videos.order', $video->id) }}" method="POST" class="mt-5">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 bg-gold-400 text-foreground px-6 md:px-8 py-3 rounded-md font-bold hover:bg-gold-500 transition shadow-lg shadow-gold-400/30 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                        {{ $accessStatus === 'expired' ? 'Perpanjang' : 'Beli Akses' }} — Rp {{ number_format($video->final_price, 0, ',', '.') }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            {{-- ======================== VIDEO INFO ======================== --}}
            <div class="bg-card rounded-lg shadow-sm border border-border p-5 md:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <h1 class="text-lg md:text-2xl font-bold text-foreground">{{ $video->title }}</h1>
                            @if($video->is_pay_what_you_want)
                                <span class="inline-flex items-center gap-1 bg-success-50 text-success-500 text-[10px] md:text-[11px] font-bold px-2 py-0.5 rounded-full">💝 Seikhlasnya</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted-foreground">
                            @if($video->package)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    {{ $video->package->title }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                {{ $video->created_at->translatedFormat('d M Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Akses {{ $video->access_duration_days }} hari
                            </span>
                        </div>
                    </div>
                    @if($hasActiveAccess)
                        <span class="badge-success flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Akses Aktif
                        </span>
                    @elseif($awaitingActivation)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gold-400/15 text-gold-600 text-xs font-semibold flex-shrink-0">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Menunggu Aktivasi
                        </span>
                    @endif
                </div>

                @if($video->description)
                    <div class="mt-4 pt-4 border-t border-border">
                        <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Deskripsi</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">{{ $video->description }}</p>
                    </div>
                @endif

                {{-- Activation Notice --}}
                @if(!$hasActiveAccess && !$awaitingActivation)
                    <div class="mt-4 pt-4 border-t border-border">
                        <div class="flex items-start gap-2.5 bg-accent-50/50 rounded-md p-3 border border-primary/10">
                            <svg class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                            <p class="text-xs text-accent-600 leading-relaxed">Setelah pembayaran, video akan diaktifkan oleh admin dalam waktu maksimal 1×24 jam.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ======================== SIDEBAR ======================== --}}
        <div class="space-y-4">
            @if($hasActiveAccess)
                {{-- === Access Info Card === --}}
                <div class="bg-gradient-to-br from-success-500 to-success-600 rounded-lg p-5 text-white shadow-lg shadow-success-500/20">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-md bg-white/15 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Akses Aktif</p>
                            <p class="text-white/70 text-xs">Anda dapat menonton video ini</p>
                        </div>
                    </div>
                    <div class="bg-white/10 rounded-md p-3 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-white/70">Mulai</span>
                            <span class="font-semibold">{{ $videoOrder->access_start?->translatedFormat('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/70">Berakhir</span>
                            <span class="font-semibold">{{ $videoOrder->access_end?->translatedFormat('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-white/20 pt-1.5">
                            <span class="text-white/70">Sisa Waktu</span>
                            <span class="font-bold">{{ $videoOrder->accessDaysRemaining() }} hari</span>
                        </div>
                    </div>
                </div>

            @elseif($awaitingActivation)
                {{-- === Awaiting Activation Card === --}}
                <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                    <div class="bg-gradient-to-r from-gold-400 to-[#f59e0b] p-4 text-foreground">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-md bg-white/25 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Menunggu Aktivasi</p>
                                <p class="text-foreground/70 text-xs">Pembayaran sudah diterima</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="bg-muted rounded-md p-3 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">No. Pesanan</span>
                                <span class="font-semibold font-mono text-foreground">{{ Str::limit($videoOrder->order_number, 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Dibayar</span>
                                <span class="font-semibold text-foreground">Rp {{ number_format($videoOrder->total_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Status</span>
                                <span class="font-semibold text-gold-600">⏳ Diaktivasi Admin</span>
                            </div>
                        </div>

                        <div class="bg-accent-50/50 rounded-md p-3 border border-primary/10">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                <p class="text-[11px] text-accent-600 leading-relaxed">Video akan diaktifkan oleh admin setelah verifikasi pembayaran. Estimasi maksimal 1×24 jam.</p>
                            </div>
                        </div>

                        <a href="{{ route('orders.index') }}" class="block w-full text-center bg-navy text-white py-2.5 rounded-md font-semibold text-sm hover:bg-navy-deep transition active:scale-95">
                            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                            Cek Status Pesanan
                        </a>
                    </div>
                </div>

            @else
                {{-- === Purchase Card === --}}
                <div class="bg-card rounded-lg shadow-sm border border-border p-5 lg:sticky lg:top-24">
                    @if($accessStatus === 'expired')
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-muted text-muted-foreground text-[11px] font-semibold mb-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Akses Berakhir
                        </div>
                        <h3 class="font-bold text-foreground text-base">Perpanjang Akses Video</h3>
                        <p class="text-xs text-muted-foreground mt-1">Lanjutkan menonton selama {{ $video->access_duration_days }} hari lagi</p>
                    @else
                        <h3 class="font-bold text-foreground text-base">
                            @if($video->is_pay_what_you_want) Bayar Seikhlasnya @else Beli Akses Video @endif
                        </h3>
                        <p class="text-xs text-muted-foreground mt-1">
                            @if($video->is_pay_what_you_want)
                                Tentukan jumlah yang ingin Anda bayar (min. Rp {{ number_format($video->minimumPayAmount(), 0, ',', '.') }})
                            @else
                                Sekali bayar, tonton selama {{ $video->access_duration_days }} hari
                            @endif
                        </p>
                    @endif

                    {{-- Price --}}
                    <div class="bg-muted rounded-md p-4 mt-4 space-y-2">
                        @if($video->is_pay_what_you_want)
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Tipe Pembayaran</span>
                                <span class="text-success-500 font-semibold">💝 Seikhlasnya</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Minimal</span>
                                <span class="font-semibold">Rp {{ number_format($video->minimumPayAmount(), 0, ',', '.') }}</span>
                            </div>
                            @if($video->hasDiscount())
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Harga Normal</span>
                                    <span class="text-muted-foreground line-through">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        @elseif($video->hasDiscount())
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Harga Normal</span>
                                <span class="text-muted-foreground line-through">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Diskon ({{ $video->discount_label }})</span>
                                <span class="text-danger-500 font-semibold">- Rp {{ number_format($video->price - $video->final_price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center border-t border-border pt-2">
                            <span class="text-sm font-semibold text-muted-foreground">
                                @if($video->is_pay_what_you_want) Bayar @else Total @endif
                            </span>
                            @if($video->is_pay_what_you_want)
                                <span class="text-lg font-bold text-success-500">Seikhlasnya</span>
                            @else
                                <span class="text-xl md:text-2xl font-bold text-navy-light">Rp {{ number_format($video->final_price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Admin Activation Notice --}}
                    <div class="flex items-start gap-2 mt-3 bg-accent-50/50 rounded-md p-2.5 border border-primary/10">
                        <svg class="w-3.5 h-3.5 text-primary mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <p class="text-[10px] text-accent-600 leading-relaxed">Video diaktifkan oleh admin setelah pembayaran diverifikasi.</p>
                    </div>

                    {{-- CTA --}}
                    @if(isset($pendingOrder) && $pendingOrder)
                        <a href="{{ route('videos.pay', ['video' => $video->id, 'videoOrder' => $pendingOrder->id]) }}"
                           class="block w-full text-center bg-gold-400 text-foreground py-3 rounded-md font-bold mt-4 hover:bg-gold-500 transition shadow-md active:scale-95">
                            <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                            Lanjutkan Pembayaran
                        </a>
                        <p class="text-[11px] text-gold-600 text-center mt-2">Anda punya pesanan belum dibayar untuk video ini</p>
                    @else
                        @if($video->is_pay_what_you_want)
                            <form action="{{ route('videos.order', $video->id) }}" method="POST" class="mt-4" id="sidebarPWYWForm">
                                @csrf
                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-muted-foreground">Jumlah Bayar</label>
                                    <div class="flex gap-1.5" id="sidebarPWYWQuick">
                                        @php
                                            $min = $video->minimumPayAmount();
                                            $quickAmounts = [$min, $min + 10000, $min + 25000, $min + 50000];
                                        @endphp
                                        @foreach($quickAmounts as $amt)
                                            <button type="button" onclick="setSidebarPWYWAmount({{ $amt }})"
                                                    class="flex-1 bg-muted hover:bg-accent-50 border border-border hover:border-primary/40 text-foreground text-[10px] font-semibold py-1.5 rounded-md transition sidebarPWYW-quick">
                                                +{{ $amt >= 1000 ? ($amt / 1000) . 'k' : number_format($amt) }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm font-semibold">Rp</span>
                                        <input type="number" name="amount" id="sidebarPWYWInput" min="{{ $video->minimumPayAmount() }}" step="1000"
                                               value="{{ $video->minimumPayAmount() }}"
                                               class="w-full border border-border rounded-md pl-10 pr-4 py-2.5 text-foreground font-bold text-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all bg-muted/50"
                                               placeholder="Jumlah" required>
                                    </div>
                                </div>
                                <button type="submit"
                                        class="w-full bg-navy-light text-white py-3 rounded-md font-bold hover:bg-navy transition shadow-md active:scale-95 flex items-center justify-center gap-2 mt-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    Bayar Seikhlasnya
                                </button>
                            </form>
                        @else
                            <form action="{{ route('videos.order', $video->id) }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit"
                                        class="w-full bg-navy-light text-white py-3 rounded-md font-bold hover:bg-navy transition shadow-md active:scale-95 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    {{ $accessStatus === 'expired' ? 'Perpanjang Sekarang' : 'Beli Sekarang' }}
                                </button>
                            </form>
                        @endif
                    @endif

                    {{-- Payment Methods --}}
                    <div class="flex flex-wrap justify-center gap-2 mt-4">
                        @foreach([
                            ['icon' => '🏦', 'label' => 'Transfer'],
                            ['icon' => '📱', 'label' => 'E-Wallet'],
                            ['icon' => '📸', 'label' => 'QRIS'],
                        ] as $method)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-muted rounded-md text-[10px] text-muted-foreground border border-border font-medium">
                                {{ $method['icon'] }} {{ $method['label'] }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-center gap-1.5 mt-4 text-[10px] text-muted-foreground">
                        <svg class="w-3 h-3 text-success-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Pembayaran aman via Midtrans
                    </div>
                </div>
            @endif

            {{-- === Benefits Card === --}}
            <div class="bg-card rounded-lg shadow-sm border border-border p-5">
                <h3 class="font-bold text-foreground text-sm mb-3">Yang Anda Dapatkan</h3>
                <ul class="space-y-2.5 text-xs text-muted-foreground">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-success-50 text-success-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Video pembahasan berkualitas tinggi
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-success-50 text-success-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Tonton berulang kali selama masa aktif
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-success-50 text-success-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Akses dari HP, tablet, maupun laptop
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-success-50 text-success-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Materi disusun oleh mentor berpengalaman
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ======================== PLYR.IO ======================== --}}
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">

@push('scripts')
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const playerWrapper = document.getElementById('player-wrapper');
    if (!playerWrapper) return;

    @if($hasActiveAccess)
        @if($video->video_file)
            const videoEl = document.getElementById('plyr-video');
            if (videoEl) {
                new Plyr(videoEl, {
                    controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'],
                    settings: ['speed'],
                    tooltips: { controls: true, seek: true },
                    keyboard: { focused: true, global: false },
                    speed: { selected: 1, options: [0.75, 1, 1.25, 1.5, 2] },
                    ratio: '16:9',
                    hideControls: true,
                    resetOnEnd: false,
                    disableContextMenu: false,
                    invertTime: false,
                    displayDuration: true,
                });
            }
        @elseif($youtubeId)
            new Plyr('#plyr-youtube', {
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'],
                settings: ['speed', 'quality'],
                tooltips: { controls: true, seek: true },
                youtube: { noCookie: true, rel: 0, showinfo: 0, iv_load_policy: 3, modestbranding: 1 },
                ratio: '16:9',
                hideControls: true,
            });
        @elseif($vimeoId)
            new Plyr('#plyr-vimeo', {
                controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'settings', 'pip', 'airplay', 'fullscreen'],
                settings: ['speed', 'quality'],
                tooltips: { controls: true, seek: true },
                vimeo: { byline: false, portrait: false, title: false, speed: true, transparent: 0 },
                ratio: '16:9',
                hideControls: true,
            });
        @endif
    @endif

    // PWYW Quick Buttons (overlay)
    @if($video->is_pay_what_you_want && !isset($pendingOrder))
    window.setPWYWAmount = function(amount) {
        const input = document.getElementById('pwYWAmountInput');
        if (input) {
            input.value = amount;
            input.focus();
        }
    };
    @endif

    // PWYW Quick Buttons (sidebar)
    @if($video->is_pay_what_you_want && !isset($pendingOrder))
    window.setSidebarPWYWAmount = function(amount) {
        const input = document.getElementById('sidebarPWYWInput');
        if (input) {
            input.value = amount;
            input.focus();
        }
    };

    // Validate minimum on submit
    const sidebarForm = document.getElementById('sidebarPWYWForm');
    if (sidebarForm) {
        sidebarForm.addEventListener('submit', function(e) {
            const input = document.getElementById('sidebarPWYWInput');
            const min = {{ $video->minimumPayAmount() }};
            if (input && parseFloat(input.value) < min) {
                e.preventDefault();
                alert('Jumlah pembayaran minimal Rp ' + min.toLocaleString('id-ID'));
                input.focus();
            }
        });
    }
    @endif
});
</script>
@endpush
@endsection