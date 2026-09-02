{{-- admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard')
@section('header-sub', 'Ringkasan sistem')

@section('content')
<style>
    .admin-stagger > * {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .admin-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .admin-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .admin-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .admin-stagger > *:nth-child(4) { animation-delay: 180ms; }

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

    .section-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .375rem 1rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600;
    }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="space-y-6 md:space-y-8 admin-stagger">

    <!-- HERO -->
    <div class="relative rounded-3xl overflow-hidden hero-section text-white shadow-xl shadow-navy/20">
        <div class="relative z-10 p-5 sm:p-6 md:p-8 lg:p-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="flex-1 min-w-0">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold leading-tight" style="font-family:'Sora','Inter',sans-serif">Halo, {{ Auth::user()->name ?? 'Admin' }}! 👋</h2>
                <p class="text-white/50 mt-2 text-sm md:text-base max-w-md leading-relaxed">Berikut ringkasan performa sistem hari ini.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <span class="bg-white/[.08] backdrop-blur px-4 py-2.5 rounded-xl text-xs md:text-sm font-medium border border-white/[.12]">📦 {{ $totalPackages }} Paket</span>
                <span class="bg-white/[.08] backdrop-blur px-4 py-2.5 rounded-xl text-xs md:text-sm font-medium border border-white/[.12]">👤 {{ $totalUsers }} Pengguna</span>
            </div>
        </div>
    </div>

    <!-- ALERT BANNERS -->
    @php
        $pendingSupportTickets = \App\Models\SupportTicket::where('status', 'pending')->count();
    @endphp
    @if($pendingSupportTickets > 0)
        <a href="{{ route('admin.support.index') }}"
           class="flex flex-col sm:flex-row sm:items-center gap-3 bg-gradient-to-r from-primary/5 to-cyan-50 border border-primary/15 p-4 md:p-5 group hover:transform hover:-translate-y-0.5 transition-all">
            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">💬</div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-foreground text-sm md:text-base">{{ $pendingSupportTickets }} tiket bantuan menunggu</p>
                <p class="text-xs md:text-sm text-primary/70 mt-0.5">Ada pengguna yang membutuhkan bantuan — segera berikan respons.</p>
            </div>
            <span class="text-xs font-bold text-white bg-primary px-4 py-2.5 rounded-xl group-hover:shadow-md transition whitespace-nowrap text-center">Tinjau Sekarang →</span>
        </a>
    @endif

    <!-- STATISTIK -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-primary/10 text-primary mb-2">📊 Statistik</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Ringkasan Sistem</h2>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#161758">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">👤</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Pengguna</p>
                        <p class="text-xl md:text-2xl font-extrabold text-foreground leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#6366f1">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center text-xl flex-shrink-0">📚</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Paket</p>
                        <p class="text-xl md:text-2xl font-extrabold text-foreground leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $totalPackages }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#00a2e9">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-cyan-50 flex items-center justify-center text-xl flex-shrink-0">📝</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Latihan</p>
                        <p class="text-xl md:text-2xl font-extrabold text-foreground leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $totalSessions }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT DATA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <div class="card-modern !rounded-2xl overflow-hidden">
            <div class="p-5 md:p-6 border-b border-border flex justify-between items-center">
                <div>
                    <span class="section-badge bg-cyan-50 text-cyan-600 mb-1.5">👤 Pengguna</span>
                    <h3 class="font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Pengguna Terbaru</h3>
                </div>
                <span class="text-xs text-muted-foreground bg-muted px-2.5 py-1 rounded-full">{{ $recentUsers->count() }} data</span>
            </div>
            <div class="divide-y divide-border max-h-80 overflow-y-auto">
                @forelse($recentUsers as $user)
                    <div class="p-4 hover:bg-muted/60 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover border border-border flex-shrink-0">
                                <div class="min-w-0">
                                    <p class="font-semibold text-foreground truncate text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] md:text-xs bg-primary/10 text-primary px-2.5 py-1 rounded-full font-medium flex-shrink-0">
                                {{ $user->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-muted-foreground">
                        <div class="text-4xl mb-2">👤</div>
                        <p class="text-sm">Belum ada pengguna</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card-modern !rounded-2xl overflow-hidden">
            <div class="p-5 md:p-6 border-b border-border">
                <div>
                    <span class="section-badge bg-violet-50 text-violet-600 mb-1.5">📊 Paket</span>
                    <h3 class="font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Statistik Paket</h3>
                </div>
            </div>
            <div class="p-5 md:p-6 space-y-4">
                @php
                    $maxSessions = max($packageStats->take(5)->pluck('sessions_count')->toArray() + [1]);
                @endphp
                @foreach($packageStats->take(5) as $stat)
                    <div>
                        <div class="flex justify-between text-sm gap-2">
                            <span class="font-medium text-foreground truncate">{{ $stat['name'] }}</span>
                            <span class="text-foreground font-medium flex-shrink-0">{{ $stat['completed_count'] }} selesai</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-2 mt-1.5">
                            @php $width = $maxSessions > 0 ? ($stat['sessions_count'] / $maxSessions) * 100 : 0; @endphp
                            <div class="bg-gradient-to-r from-primary to-primary/60 rounded-full h-2 transition-all duration-500" style="width: {{ $width }}%;"></div>
                        </div>
                        <div class="text-xs text-muted-foreground mt-0.5">
                            Rata-rata Nilai: {{ $stat['avg_score'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
