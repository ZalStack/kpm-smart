{{-- user/practice/statistics.blade.php --}}
@extends('layouts.app')

@section('title', 'Statistik Belajar')

@section('content')
<style>
    .stats-stagger > * {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .stats-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .stats-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .stats-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .stats-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .stats-stagger > *:nth-child(5) { animation-delay: 240ms; }
</style>

<div class="space-y-6 stats-stagger">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold mb-2">📈 Statistik</span>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Statistik Belajar</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Perkembangan belajar Anda</p>
        </div>
        <a href="{{ route('practice.history') }}" class="inline-flex items-center gap-2 bg-gold-400 text-foreground text-sm px-4 py-2.5 rounded-md hover:bg-gold-500 transition-all duration-200 hover:-translate-y-0.5 text-center font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Riwayat
        </a>
    </div>

    <!-- Overall Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4">
        <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-default group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Total Latihan</p>
                    <p class="text-xl md:text-2xl font-bold text-foreground mt-1">{{ $totalAttempts }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">📝</div>
            </div>
        </div>
        <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-default group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Nilai Terbaik</p>
                    <p class="text-xl md:text-2xl font-bold text-foreground mt-1">{{ number_format($bestScore, 1) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-gold-400/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🏆</div>
            </div>
        </div>
        <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-default group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Rata-rata</p>
                    <p class="text-xl md:text-2xl font-bold text-foreground mt-1">{{ number_format($averageScore, 1) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-success-500/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">📊</div>
            </div>
        </div>
        <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-default group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Akurasi</p>
                    <p class="text-xl md:text-2xl font-bold text-foreground mt-1">{{ number_format($accuracy, 1) }}%</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-navy-light/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🎯</div>
            </div>
        </div>
    </div>

    <!-- Per Package Stats -->
    @if($sessionsByPackage->isNotEmpty())
        <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border">
            <h3 class="font-bold text-foreground mb-4 text-base md:text-lg">📚 Statistik per Paket</h3>
            <div class="space-y-3 md:space-y-4">
                @foreach($sessionsByPackage as $stat)
                    <div>
                        <div class="flex flex-wrap justify-between items-center gap-2">
                            <span class="font-semibold text-foreground text-sm md:text-base">{{ $stat['package'] }}</span>
                            <span class="text-xs md:text-sm text-muted-foreground">{{ $stat['attempts'] }} latihan</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 md:gap-4 mt-1">
                            <div class="flex-1 min-w-[60px]">
                                <div class="w-full bg-muted rounded-full h-1.5 md:h-2">
                                    <div class="bg-gradient-to-r from-primary to-navy-light rounded-full h-1.5 md:h-2" style="width: {{ min($stat['avg_score'], 100) }}%;"></div>
                                </div>
                            </div>
                            <div class="text-[10px] md:text-sm whitespace-nowrap">
                                <span class="text-muted-foreground">Rata-rata: {{ number_format($stat['avg_score'], 1) }}</span>
                                <span class="text-muted-foreground mx-1">|</span>
                                <span class="text-success-500">Terbaik: {{ number_format($stat['best_score'], 1) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tips -->
    <div class="bg-gradient-to-r from-primary to-navy-light rounded-lg p-4 md:p-6 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_80%_20%,rgba(252,198,38,0.1)_0%,transparent_50%)] pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="font-bold text-base md:text-lg">💡 Tips Belajar</h3>
            <ul class="mt-2 md:mt-3 space-y-1.5 md:space-y-2 text-xs md:text-sm text-white/80">
                <li class="flex items-start gap-2"><span class="text-gold-400 mt-0.5">✦</span> Kerjakan latihan secara rutin untuk meningkatkan pemahaman</li>
                <li class="flex items-start gap-2"><span class="text-gold-400 mt-0.5">✦</span> Perhatikan pembahasan setiap soal untuk memahami konsep</li>
                <li class="flex items-start gap-2"><span class="text-gold-400 mt-0.5">✦</span> Ulangi latihan yang sulit hingga Anda merasa paham</li>
                <li class="flex items-start gap-2"><span class="text-gold-400 mt-0.5">✦</span> Catat perkembangan nilai Anda untuk melihat kemajuan</li>
            </ul>
        </div>
    </div>
</div>
@endsection
