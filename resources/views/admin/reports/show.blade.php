{{-- admin/reports/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Laporan - Admin')
@section('header-title', 'Detail Laporan')
@section('header-sub', 'Lihat detail laporan secara lengkap')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.reports.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Laporan
        </a>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                Cetak
            </button>
            <button onclick="exportExcel()" class="btn-success">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Excel
            </button>
            <button onclick="exportPdf()" class="btn-danger">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                PDF
            </button>
        </div>
    </div>

    <!-- Report Info -->
    <div class="admin-card p-5 sm:p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="stagger-item">
                <p class="form-label">Periode</p>
                <p class="text-sm font-semibold text-brand-900 mt-1">{{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            </div>
            <div class="stagger-item">
                <p class="form-label">Jenis</p>
                <p class="text-sm font-semibold text-brand-900 mt-1">{{ ucfirst($reportType) }}</p>
            </div>
            <div class="stagger-item">
                <p class="form-label">Total Data</p>
                <p class="text-sm font-semibold text-brand-900 mt-1">{{ isset($data['total_count']) ? $data['total_count'] : (isset($data['transactions']) ? $data['transactions']->count() : 0) }}</p>
            </div>
            <div class="stagger-item">
                <p class="form-label">Dicetak</p>
                <p class="text-sm font-semibold text-brand-900 mt-1">{{ Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($data['total_revenue']))
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="form-label">Pendapatan</h3>
                    <p class="text-lg font-bold text-brand-900">Rp {{ number_format($data['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <h3 class="form-label">Transaksi</h3>
                    <p class="text-lg font-bold text-brand-900">{{ $data['total_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="form-label">Berhasil</h3>
                    <p class="text-lg font-bold text-success-500">{{ $data['paid_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="form-label">Pending</h3>
                    <p class="text-lg font-bold text-gold-600">{{ $data['pending_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        <div class="p-5 border-b border-border"><h3 class="text-sm font-semibold text-brand-900">Detail Data</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm admin-table">
                <thead>
                    <tr>
                        @if(isset($data['transactions']) && $data['transactions']->isNotEmpty())
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Pesanan</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">User</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Paket</th>
                            <th class="px-4 py-3 text-left">Jumlah</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal Pembayaran</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Pembayaran</th>
                        @elseif(isset($data['users']) && $data['users']->isNotEmpty())
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Status</th>
                            <th class="px-4 py-3 text-left">Pesanan</th>
                        @elseif(isset($data['package_stats']) && $data['package_stats']->isNotEmpty())
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Paket</th>
                            <th class="px-4 py-3 text-left">Harga</th>
                            <th class="px-4 py-3 text-left">Pesanan</th>
                            <th class="px-4 py-3 text-left">Pendapatan</th>
                        @else
                            <th class="px-4 py-3 text-center">Tidak ada data</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @if(isset($data['transactions']) && $data['transactions']->isNotEmpty())
                        @foreach($data['transactions'] as $index => $t)
                            <tr>
                                <td class="px-4 py-3 text-center text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3"><span class="font-mono text-xs font-semibold bg-muted px-2 py-0.5 rounded-lg">{{ $t->order_number }}</span></td>
                                <td class="px-4 py-3 hidden sm:table-cell text-foreground">{{ $t->user?->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 hidden md:table-cell text-muted-foreground">{{ $t->package?->title ?? 'Deleted' }}</td>
                                <td class="px-4 py-3 font-semibold text-brand-900">Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    @if($t->payment_status == 'pending')
                                        <span class="badge-warning">{{ ucfirst($t->payment_status) }}</span>
                                    @elseif($t->payment_status == 'paid')
                                        <span class="badge-success">{{ ucfirst($t->payment_status) }}</span>
                                    @else
                                        <span class="badge-danger">{{ ucfirst($t->payment_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground font-mono text-sm">{{ $t->payment_time ? Carbon\Carbon::parse($t->payment_time)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground font-mono text-sm">{{ $t->payment_time ? Carbon\Carbon::parse($t->payment_time)->format('H:i:s') : '-' }}</td>
                            </tr>
                        @endforeach
                    @elseif(isset($data['users']) && $data['users']->isNotEmpty())
                        @foreach($data['users'] as $index => $u)
                            <tr>
                                <td class="px-4 py-3 text-center text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $u->email }}</td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    @if($u->is_active)
                                        <span class="badge-success">Aktif</span>
                                    @else
                                        <span class="badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-brand-900">{{ $u->orders_count }}</td>
                            </tr>
                        @endforeach
                    @elseif(isset($data['package_stats']) && $data['package_stats']->isNotEmpty())
                        @foreach($data['package_stats'] as $index => $stat)
                            <tr>
                                <td class="px-4 py-3 text-center text-muted-foreground">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ $stat['package']->title }}</td>
                                <td class="px-4 py-3 text-muted-foreground">Rp {{ number_format($stat['package']->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-brand-900">{{ $stat['total_orders'] }}</td>
                                <td class="px-4 py-3 font-semibold text-success-500">Rp {{ number_format($stat['revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="empty-state">
                                    <div class="w-12 h-12 mx-auto bg-muted rounded-md flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <p class="text-muted-foreground text-sm">Tidak ada data untuk ditampilkan</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($data['users']) && $data['users'] instanceof \Illuminate\Pagination\LengthAwarePaginator && $data['users']->hasPages())
        <div class="admin-card p-4">
            {{ $data['users']->withQueryString() }}
        </div>
    @endif
</div>

<form id="exportForm" method="GET" action="">
    <input type="hidden" name="start_date" value="{{ $startDate }}">
    <input type="hidden" name="end_date" value="{{ $endDate }}">
    <input type="hidden" name="report_type" value="{{ $reportType }}">
    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
    <input type="hidden" name="package_id" value="{{ request('package_id') }}">
</form>

<script>
function exportExcel() { document.getElementById('exportForm').action = "{{ route('admin.reports.export-excel') }}"; document.getElementById('exportForm').submit(); }
function exportPdf() { document.getElementById('exportForm').action = "{{ route('admin.reports.export-pdf') }}"; document.getElementById('exportForm').submit(); }
</script>

<style>
@media print {
    .admin-header, .btn-print, form button { display: none !important; }
    .bg-card { background: white !important; box-shadow: none !important; border: 1px solid #e5e7eb !important; }
    .rounded-md { border-radius: 0 !important; }
}
</style>
@endsection
