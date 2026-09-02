{{-- admin/reports/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Laporan - Admin')
@section('header-title', 'Laporan & Analitik')
@section('header-sub', 'Lihat laporan lengkap sistem membership')

@section('content')
<div class="space-y-6">
    <!-- Filter Section -->
    <div class="admin-card p-3 sm:p-5 md:p-6">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate ?? request('start_date', date('Y-m-01')) }}" class="form-input">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate ?? request('end_date', date('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Jenis Laporan</label>
                    <select name="report_type" class="form-select">
                        <option value="all" {{ (request('report_type') ?? 'all') == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="summary" {{ request('report_type') == 'summary' ? 'selected' : '' }}>Ringkasan</option>
                        <option value="transactions" {{ request('report_type') == 'transactions' ? 'selected' : '' }}>Transaksi</option>
                        <option value="users" {{ request('report_type') == 'users' ? 'selected' : '' }}>Pengguna</option>
                        <option value="packages" {{ request('report_type') == 'packages' ? 'selected' : '' }}>Paket</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Paket</label>
                    <select name="package_id" class="form-select">
                        <option value="">Semua Paket</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>{{ $package->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-end gap-2">
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Tampilkan
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn-secondary">Reset</a>
                    <button type="button" onclick="exportExcel()" class="btn-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Excel
                    </button>
                    <button type="button" onclick="exportPdf()" class="btn-danger">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        PDF
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Pendapatan</h3>
                    <p class="text-lg font-bold text-brand-900">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Transaksi</h3>
                    <p class="text-lg font-bold text-brand-900">{{ $totalTransactions ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Berhasil</h3>
                    <p class="text-lg font-bold text-success-500">{{ $paidCount ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Pending</h3>
                    <p class="text-lg font-bold text-gold-600">{{ $pendingCount ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart & Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="lg:col-span-2 admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Grafik Pendapatan Harian</h3>
            <div class="h-64 relative"><canvas id="revenueChart"></canvas></div>
        </div>
        <div class="admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Distribusi Pembayaran</h3>
            <div class="space-y-3">
                @forelse($paymentDistribution ?? [] as $type => $data)
                    @php $percent = $totalTransactions > 0 ? round(($data['count'] / $totalTransactions) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm"><span class="text-muted-foreground">{{ ucfirst(str_replace('_', ' ', $type)) }}</span><span class="font-semibold text-brand-900">{{ $data['count'] }} ({{ $percent }}%)</span></div>
                        <div class="w-full bg-muted rounded-full h-2 mt-1"><div class="h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%; background: {{ ['#27438D', '#00a2e9', '#FCC626', '#2E7D3E', '#ec1d1d'][$loop->index % 5] }};"></div></div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <div class="w-14 h-14 rounded-lg bg-muted dark:bg-card/5 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/></svg>
                        </div>
                        <p class="text-muted-foreground text-sm font-medium">Belum ada data distribusi</p>
                        <p class="text-muted-foreground text-xs mt-1">Data akan muncul setelah ada transaksi</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Packages & Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Paket Terlaris</h3>
            <div class="space-y-3">
                @forelse($topPackages ?? [] as $package)
                    <div class="flex items-center justify-between p-3 bg-muted rounded-md">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-md bg-brand-50 flex items-center justify-center flex-shrink-0">
                                @if($loop->index === 0)
                                    <svg class="w-4 h-4 text-gold-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @elseif($loop->index === 1)
                                    <svg class="w-4 h-4 text-muted-foreground" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @elseif($loop->index === 2)
                                    <svg class="w-4 h-4 text-gold-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                @endif
                            </span>
                            <span class="font-medium text-foreground">{{ $package['package_name'] }}</span>
                        </div>
                        <div class="text-right"><div class="font-semibold text-brand-900">{{ $package['count'] }} transaksi</div><div class="text-xs text-muted-foreground">Rp {{ number_format($package['revenue'], 0, ',', '.') }}</div></div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10">
                        <div class="w-14 h-14 rounded-lg bg-muted dark:bg-card/5 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <p class="text-muted-foreground text-sm font-medium">Belum ada data paket</p>
                        <p class="text-muted-foreground text-xs mt-1">Paket terlaris akan muncul di sini</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Statistik Pengguna</h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-brand-50 rounded-md"><div class="text-2xl font-bold text-brand-900">{{ $totalUsers ?? 0 }}</div><div class="text-xs text-muted-foreground mt-1">Total</div></div>
                <div class="text-center p-3 bg-success-50 rounded-md"><div class="text-2xl font-bold text-success-500">{{ $activeUsers ?? 0 }}</div><div class="text-xs text-muted-foreground mt-1">Aktif</div></div>
                <div class="text-center p-3 bg-gold-50 rounded-md"><div class="text-2xl font-bold text-gold-500">{{ $newUsers ?? 0 }}</div><div class="text-xs text-muted-foreground mt-1">Baru</div></div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="admin-card overflow-hidden">
        <div class="p-5 border-b border-border flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-brand-900">Transaksi Terbaru</h3>
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-primary hover:text-brand-900 font-medium transition-colors">Lihat Semua
                <svg class="w-4 h-4 inline-block -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm admin-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">Pesanan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">User</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Paket</th>
                        <th class="px-4 py-3 text-left">Jumlah</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal Pembayaran</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse(($transactions ?? [])->take(10) as $transaction)
                        <tr>
                            <td class="px-4 py-3"><span class="font-mono text-xs font-semibold bg-muted px-2 py-0.5 rounded-lg">{{ $transaction->order_number }}</span></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-foreground">{{ $transaction->user?->name ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-muted-foreground">@if($transaction->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $transaction->item_title }}</td>
                            <td class="px-4 py-3 font-semibold text-brand-900">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($transaction->payment_status == 'pending')
                                    <span class="badge-warning">{{ ucfirst($transaction->payment_status) }}</span>
                                @elseif($transaction->payment_status == 'paid')
                                    <span class="badge-success">{{ ucfirst($transaction->payment_status) }}</span>
                                @else
                                    <span class="badge-danger">{{ ucfirst($transaction->payment_status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground font-mono text-sm">
                                {{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground font-mono text-sm">
                                {{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('H:i:s') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-lg bg-muted dark:bg-card/5 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                                    </div>
                                    <p class="text-muted-foreground text-sm font-medium">Belum ada transaksi</p>
                                    <p class="text-muted-foreground text-xs mt-1">Transaksi terbaru akan muncul di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="exportForm" method="GET" action="">
    <input type="hidden" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}">
    <input type="hidden" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
    <input type="hidden" name="report_type" value="{{ request('report_type', 'all') }}">
    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
    <input type="hidden" name="package_id" value="{{ request('package_id') }}">
</form>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const chartData = @json($chartData ?? ['labels' => [], 'revenue' => [], 'count' => []]);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: [
                { label: 'Pendapatan (Rp)', data: chartData.revenue || [], backgroundColor: 'rgba(39, 67, 141, 0.6)', borderColor: '#27438D', borderWidth: 2, borderRadius: 4, yAxisID: 'y' },
                { label: 'Jumlah Transaksi', data: chartData.count || [], type: 'line', borderColor: '#FCC626', backgroundColor: 'rgba(252, 198, 38, 0.1)', borderWidth: 3, pointBackgroundColor: '#FCC626', pointBorderColor: '#161758', tension: 0.3, yAxisID: 'y1' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 10 }, boxWidth: 12, padding: 10 } } },
            scales: {
                y: { beginAtZero: true, position: 'left', ticks: { font: { size: 9 }, callback: v => v >= 1000 ? 'Rp ' + (v/1000) + 'k' : 'Rp ' + v } },
                y1: { beginAtZero: true, position: 'right', ticks: { font: { size: 9 }, stepSize: 1 }, grid: { drawOnChartArea: false } },
                x: { ticks: { font: { size: 9 }, maxTicksLimit: 15 }, grid: { display: false } }
            }
        }
    });
});

function exportExcel() { document.getElementById('exportForm').action = "{{ route('admin.reports.export-excel') }}"; document.getElementById('exportForm').submit(); }
function exportPdf() { document.getElementById('exportForm').action = "{{ route('admin.reports.export-pdf') }}"; document.getElementById('exportForm').submit(); }
</script>
@endsection
