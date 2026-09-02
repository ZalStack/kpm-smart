{{-- admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header-title', 'Dashboard')
@section('header-sub', 'Ringkasan sistem membership')

@section('content')
<style>
    .admin-stagger > * {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .admin-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .admin-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .admin-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .admin-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .admin-stagger > *:nth-child(5) { animation-delay: 240ms; }
    .admin-stagger > *:nth-child(6) { animation-delay: 300ms; }
    .admin-stagger > *:nth-child(7) { animation-delay: 360ms; }

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

    .admin-alert {
        border-radius: 16px;
        transition: all .35s cubic-bezier(.16,1,.3,1);
    }
    .admin-alert:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0,0,0,.06);
    }

    .section-badge {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .375rem 1rem; border-radius: 9999px;
        font-size: .75rem; font-weight: 600;
    }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .anim-float { animation: float 6s ease-in-out infinite; }
</style>

<div class="space-y-6 md:space-y-8 admin-stagger">

    <!-- ================= HERO ================= -->
    <div class="relative rounded-3xl overflow-hidden hero-section text-white shadow-xl shadow-navy/20">
        <div class="absolute top-8 left-[10%] w-2 h-2 bg-gold-400 rounded-full opacity-40 anim-float" style="animation-delay:0s"></div>
        <div class="absolute top-16 right-[15%] w-3 h-3 bg-cyan-400 rounded-full opacity-25 anim-float" style="animation-delay:1.5s"></div>
        <div class="absolute bottom-12 left-[20%] w-2 h-2 rounded-full opacity-30 anim-float" style="background:#10b981;animation-delay:3s"></div>
        <div class="absolute top-20 left-[50%] w-1.5 h-1.5 bg-white rounded-full opacity-15 anim-float" style="animation-delay:.7s"></div>

        <div class="relative z-10 p-5 sm:p-6 md:p-8 lg:p-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="flex-1 min-w-0">
                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold leading-tight" style="font-family:'Sora','Inter',sans-serif">Halo, {{ Auth::user()->name ?? 'Admin' }}! 👋</h2>
                <p class="text-white/50 mt-2 text-sm md:text-base max-w-md leading-relaxed">Berikut ringkasan performa sistem membership hari ini.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <span class="bg-white/[.08] backdrop-blur px-4 py-2.5 rounded-xl text-xs md:text-sm font-medium border border-white/[.12]">📦 {{ $totalOrders }} Pesanan</span>
                <span class="bg-white/[.08] backdrop-blur px-4 py-2.5 rounded-xl text-xs md:text-sm font-medium border border-white/[.12]">👤 {{ $totalUsers }} Pengguna</span>
            </div>
        </div>
    </div>

    <!-- ================= ALERT BANNERS ================= -->
    @if($pendingVideoActivations > 0)
        <a href="{{ route('admin.video-orders.index', ['filter' => 'needs_activation']) }}"
           class="admin-alert flex flex-col sm:flex-row sm:items-center gap-3 bg-gradient-to-r from-gold-50 to-orange-50 border border-gold-200/60 p-4 md:p-5 group">
            <div class="w-11 h-11 rounded-xl bg-gold-400/15 flex items-center justify-center text-xl flex-shrink-0">🎬</div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gold-800 text-sm md:text-base">{{ $pendingVideoActivations }} akses video menunggu aktivasi</p>
                <p class="text-xs md:text-sm text-gold-700/70 mt-0.5">User sudah membayar — segera berikan akses agar mereka bisa menonton.</p>
            </div>
            <span class="text-xs font-bold text-white bg-gradient-to-r from-gold-500 to-orange-500 px-4 py-2.5 rounded-xl group-hover:shadow-md transition whitespace-nowrap text-center">Tinjau Sekarang →</span>
        </a>
    @endif

    @php
        $pendingSupportTickets = \App\Models\SupportTicket::where('status', 'pending')->count();
    @endphp
    @if($pendingSupportTickets > 0)
        <a href="{{ route('admin.support.index') }}"
           class="admin-alert flex flex-col sm:flex-row sm:items-center gap-3 bg-gradient-to-r from-primary/5 to-cyan-50 border border-primary/15 p-4 md:p-5 group">
            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">💬</div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-foreground text-sm md:text-base">{{ $pendingSupportTickets }} tiket bantuan menunggu</p>
                <p class="text-xs md:text-sm text-primary/70 mt-0.5">Ada pengguna yang membutuhkan bantuan — segera berikan respons.</p>
            </div>
            <span class="text-xs font-bold text-white bg-primary px-4 py-2.5 rounded-xl group-hover:shadow-md transition whitespace-nowrap text-center">Tinjau Sekarang →</span>
        </a>
    @endif

    <!-- ================= STATISTIK ================= -->
    <div>
        <div class="flex items-end justify-between mb-4">
            <div>
                <span class="section-badge bg-primary/10 text-primary mb-2">📊 Statistik</span>
                <h2 class="text-lg md:text-xl font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Ringkasan Sistem</h2>
                <p class="text-[11px] text-muted-foreground mt-0.5 hidden sm:block">Data terkini dari seluruh aktivitas platform</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
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

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#FCC626">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gold-50 flex items-center justify-center text-xl flex-shrink-0">🛒</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Pesanan</p>
                        <p class="text-xl md:text-2xl font-extrabold text-foreground leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $totalOrders }}</p>
                    </div>
                </div>
            </div>

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#10b981">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-success-50 flex items-center justify-center text-xl flex-shrink-0">✅</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Lunas</p>
                        <p class="text-xl md:text-2xl font-extrabold text-foreground leading-none" style="font-family:'Sora','Inter',sans-serif">{{ $paidOrders }}</p>
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

            <div class="stat-tile p-4 md:p-5" style="--tile-accent:#ef4444">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-danger-50 flex items-center justify-center text-xl flex-shrink-0">💰</div>
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider truncate">Pendapatan</p>
                        <p class="text-sm md:text-base font-extrabold text-foreground truncate leading-none mt-1" style="font-family:'Sora','Inter',sans-serif">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= ENROLL KEY ALERT ================= -->
    @php
        $pendingEnrolls = \App\Models\Order::where('payment_status', 'paid')
            ->whereJsonContains('enrollment->activated', false)
            ->count();
    @endphp
    @if($pendingEnrolls > 0)
    <div class="admin-alert bg-gradient-to-r from-gold-50 to-gold-100/50 border border-gold-200/50 p-4 md:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gold-400/15 flex items-center justify-center text-xl flex-shrink-0">🔑</div>
            <div class="flex-1 min-w-[200px]">
                <p class="font-bold text-foreground text-sm md:text-base">Ada {{ $pendingEnrolls }} Enroll Key yang perlu diaktivasi!</p>
                <p class="text-xs md:text-sm text-muted-foreground mt-0.5">Buka menu Enroll Keys untuk mengaktivasi akses pengguna.</p>
            </div>
            <a href="{{ route('admin.enroll-keys.index') }}" class="no-print bg-gold-400 text-foreground px-5 py-2.5 rounded-xl font-semibold hover:bg-gold-500 hover:shadow-md transition text-sm">Kelola Enroll Keys →</a>
        </div>
    </div>
    @endif

    <!-- ================= CHARTS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <div class="lg:col-span-2 card-modern !rounded-2xl p-5 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <span class="section-badge bg-violet-50 text-violet-600 mb-1.5">📈 Grafik</span>
                    <h3 class="font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Pendapatan Bulanan</h3>
                </div>
                <span class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-full font-semibold">{{ date('Y') }}</span>
            </div>
            <div class="relative" style="height: 260px;">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <div class="card-modern !rounded-2xl p-5 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <span class="section-badge bg-success-50 text-success-600 mb-1.5">📊 Paket</span>
                    <h3 class="font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Statistik Paket</h3>
                </div>
            </div>
            <div class="space-y-4">
                @php
                    $maxOrders = max($packageStats->take(5)->pluck('orders_count')->toArray() + [1]);
                @endphp
                @foreach($packageStats->take(5) as $stat)
                    <div>
                        <div class="flex justify-between text-sm gap-2">
                            <span class="font-medium text-foreground truncate">{{ $stat['name'] }}</span>
                            <span class="text-foreground font-medium flex-shrink-0">{{ $stat['orders_count'] }} pesanan</span>
                        </div>
                        <div class="w-full bg-muted rounded-full h-2 mt-1.5">
                            @php $width = $maxOrders > 0 ? ($stat['orders_count'] / $maxOrders) * 100 : 0; @endphp
                            <div class="bg-gradient-to-r from-primary to-primary/60 rounded-full h-2 transition-all duration-500" style="width: {{ $width }}%;"></div>
                        </div>
                        <div class="text-xs text-muted-foreground mt-0.5">
                            Pendapatan: Rp {{ number_format($stat['revenue'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
            @if($packageStats->count() > 5)
                <a href="{{ route('admin.packages.index') }}" class="mt-4 flex items-center justify-center gap-1 text-sm font-semibold text-primary hover:text-primary/80 transition">
                    Lihat Lainnya →
                </a>
            @endif
        </div>
    </div>

    <!-- ================= RECENT DATA ================= -->
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
            <div class="p-5 md:p-6 border-b border-border flex justify-between items-center">
                <div>
                    <span class="section-badge bg-gold-50 text-gold-600 mb-1.5">🛒 Pesanan</span>
                    <h3 class="font-bold text-foreground" style="font-family:'Sora','Inter',sans-serif">Pesanan Terbaru</h3>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-primary hover:text-primary/80 text-sm font-medium transition">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-border max-h-80 overflow-y-auto">
                @forelse($recentOrders as $order)
                    <div class="p-4 hover:bg-muted/60 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-foreground truncate text-sm">@if($order->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $order->item_title }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ $order->user->name ?? 'User' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-sm font-bold text-foreground">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </span>
                                <br>
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                    @if($order->payment_status === 'paid') bg-success-50 text-success-500
                                    @elseif($order->payment_status === 'pending') bg-gold-50 text-gold-600
                                    @else bg-danger-50 text-danger-500 @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-muted-foreground">
                        <div class="text-4xl mb-2">🛒</div>
                        <p class="text-sm">Belum ada pesanan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('monthlyRevenueChart');
    if (!ctx) return;

    const labels = @php echo json_encode(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']); @endphp;
    const data = @php echo json_encode(array_values($monthlyRevenue)); @endphp;

    const gradientFill = ctx.getContext('2d').createLinearGradient(0, 0, 0, 260);
    gradientFill.addColorStop(0, 'rgba(108, 99, 255, 0.35)');
    gradientFill.addColorStop(0.5, 'rgba(108, 99, 255, 0.12)');
    gradientFill.addColorStop(1, 'rgba(108, 99, 255, 0.01)');

    const gradientBorder = ctx.getContext('2d').createLinearGradient(0, 0, ctx.parentElement.offsetWidth, 0);
    gradientBorder.addColorStop(0, '#6c63ff');
    gradientBorder.addColorStop(0.5, '#4f46e5');
    gradientBorder.addColorStop(1, '#6c63ff');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan',
                data: data,
                fill: true,
                backgroundColor: gradientFill,
                borderColor: gradientBorder,
                borderWidth: 3,
                tension: 0.45,
                pointRadius: 0,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#6c63ff',
                pointHoverBorderWidth: 3,
                pointHitRadius: 20,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#ffffff',
                    titleFont: { size: 12, weight: '600', family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, weight: '700', family: "'Inter', sans-serif" },
                    padding: { top: 10, bottom: 10, left: 14, right: 14 },
                    cornerRadius: 12,
                    displayColors: false,
                    borderColor: 'rgba(108, 99, 255, 0.3)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(items) {
                            return items[0].label + ' {{ date("Y") }}';
                        },
                        label: function(item) {
                            return 'Rp ' + item.formattedValue;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        font: { size: 11, family: "'Inter', sans-serif", weight: '500' },
                        color: '#94a3b8',
                        padding: 6,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                        drawBorder: false,
                    },
                    border: { display: false },
                    ticks: {
                        font: { size: 10, family: "'Inter', sans-serif", weight: '500' },
                        color: '#94a3b8',
                        padding: 8,
                        maxTicksLimit: 5,
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(0) + 'jt';
                            if (value >= 1000) return (value / 1000).toFixed(0) + 'rb';
                            return value;
                        }
                    }
                }
            },
            animation: {
                duration: 1200,
                easing: 'easeOutQuart',
            }
        }
    });
});
</script>
@endpush
@endsection
