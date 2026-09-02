{{-- user/practice/history.blade.php --}}
@extends('layouts.app')

@section('title', 'Riwayat Latihan')

@section('content')
<style>
    .hist-stagger > * {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .hist-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .hist-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .hist-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .hist-stagger > *:nth-child(4) { animation-delay: 180ms; }
</style>

<div class="space-y-6 hist-stagger">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-success-500/10 text-success-500 text-xs font-semibold mb-2">📊 Statistik</span>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Riwayat Latihan</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Semua latihan yang telah Anda kerjakan</p>
        </div>
        <a href="{{ route('practice.statistics') }}" class="inline-flex items-center gap-2 bg-navy-light text-white text-sm px-4 py-2.5 rounded-md hover:bg-navy transition-all duration-200 hover:-translate-y-0.5 text-center font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Statistik
        </a>
    </div>

    @if($sessions->isEmpty())
        <div class="bg-card rounded-lg p-8 md:p-12 text-center shadow-sm border border-border">
            <div class="text-5xl md:text-6xl mb-4">📝</div>
            <h3 class="text-lg md:text-xl font-bold text-muted-foreground">Belum Ada Latihan</h3>
            <p class="text-muted-foreground mt-2 text-sm md:text-base">Mulai kerjakan latihan soal untuk melihat riwayat</p>
            <a href="{{ route('packages.index') }}" class="inline-block mt-4 bg-gradient-to-r from-navy-light to-navy text-white px-6 py-2.5 rounded-md font-semibold hover:shadow-md transition text-sm md:text-base">Lihat Paket</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-4">
            @foreach($sessions as $session)
                <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-foreground text-sm md:text-base truncate">{{ $session->package->title }}</h3>
                            <p class="text-xs md:text-sm text-muted-foreground">{{ $session->created_at->format('d M Y H:i') }}</p>
                            <div class="mt-3 grid grid-cols-3 gap-1 md:gap-2">
                                <div><p class="text-[10px] text-muted-foreground">Skor</p><p class="font-bold text-foreground text-sm md:text-base">{{ number_format($session->total_score, 1) }}</p></div>
                                <div><p class="text-[10px] text-muted-foreground">Benar</p><p class="font-bold text-success-500 text-sm md:text-base">{{ $session->correct_answer }}</p></div>
                                <div><p class="text-[10px] text-muted-foreground">Salah</p><p class="font-bold text-danger-500 text-sm md:text-base">{{ $session->wrong_answer }}</p></div>
                            </div>
                            @php $minutes = floor($session->duration_seconds / 60); $seconds = $session->duration_seconds % 60; @endphp
                            <p class="text-[10px] text-muted-foreground mt-2">⏱️ {{ $minutes }}:{{ str_pad($seconds, 2, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <a href="{{ route('practice.show', $session->id) }}" class="bg-gradient-to-r from-navy-light to-navy text-white text-xs md:text-sm px-3 md:px-4 py-1.5 md:py-2 rounded-md hover:shadow-md transition whitespace-nowrap flex-shrink-0 font-semibold">Detail</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
