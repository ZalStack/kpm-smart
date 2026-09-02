{{-- admin/transactions/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Transaksi - Admin')
@section('header-title', 'Manajemen Transaksi')
@section('header-sub', 'Kelola semua pembayaran')

@section('content')
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="stat-card stagger-item">
                <div class="flex items-center gap-4">
                    <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Pendapatan</h3>
                        <p class="text-lg font-bold text-brand-900">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stagger-item">
                <div class="flex items-center gap-4">
                    <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Total</h3>
                        <p class="text-lg font-bold text-brand-900">{{ $stats['total_transactions'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stagger-item">
                <div class="flex items-center gap-4">
                    <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Lunas</h3>
                        <p class="text-lg font-bold text-success-500">{{ $stats['paid_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stagger-item">
                <div class="flex items-center gap-4">
                    <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Tertunda</h3>
                        <p class="text-lg font-bold text-gold-600">{{ $stats['pending_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="admin-card p-4 sm:p-5">
            <form action="{{ route('admin.transactions.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <input type="text" name="search" placeholder="Cari order atau user..." value="{{ request('search') }}" class="form-input pl-10">
                    </div>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input">
                </div>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <select name="payment_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Tertunda</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                    </select>
                    <select name="payment_type" class="form-select">
                        <option value="">Semua Metode</option>
                        @foreach ($paymentTypes as $type)
                            <option value="{{ $type }}" {{ request('payment_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <select name="package_id" class="form-select">
                        <option value="">Semua Paket</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button type="submit" class="btn-primary">Terapkan</button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn-secondary">Reset</a>
                    <div class="relative" id="exportWrap">
                        <button type="button" onclick="toggleExportDropdown()" class="btn-primary !bg-gradient-to-r !from-brand-900 !to-accent-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export
                        </button>
                        <div id="exportDropdown"
                            class="hidden absolute right-0 mt-2 bg-card rounded-md shadow-xl border border-border min-w-[160px] py-1.5 z-50 animate-scale-in">
                            <a href="#" onclick="exportExcel()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-muted transition-colors">
                                <svg class="w-4 h-4 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Export Excel
                            </a>
                            <a href="#" onclick="exportPdf()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-muted transition-colors">
                                <svg class="w-4 h-4 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5 text-left">Pesanan</th>
                            <th class="px-5 py-3.5 text-left hidden sm:table-cell">Pengguna</th>
                            <th class="px-5 py-3.5 text-left hidden md:table-cell">Paket</th>
                            <th class="px-5 py-3.5 text-left">Jumlah</th>
                            <th class="px-5 py-3.5 text-left">Status</th>
                            <th class="px-5 py-3.5 text-left hidden lg:table-cell">Metode</th>
                            <th class="px-5 py-3.5 text-left hidden xl:table-cell">Tanggal Bayar</th>
                            <th class="px-5 py-3.5 text-left hidden xl:table-cell">Waktu Bayar</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-muted/50 transition-colors duration-150">
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs font-semibold text-brand-800 bg-brand-50 px-2 py-0.5 rounded-md">{{ $transaction->order_number }}</span>
                                </td>
                                <td class="px-5 py-4 hidden sm:table-cell text-foreground">
                                    {{ $transaction->user?->name ?? 'Unknown' }}</td>
                                <td class="px-5 py-4 hidden md:table-cell text-muted-foreground">
                                    @if($transaction->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $transaction->item_title }}</td>
                                <td class="px-5 py-4 font-semibold text-brand-900">Rp
                                    {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if ($transaction->payment_status == 'pending')
                                        <span class="badge-warning">Tertunda</span>
                                    @elseif($transaction->payment_status == 'paid')
                                        <span class="badge-success">Lunas</span>
                                    @else
                                        <span class="badge-danger">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell text-muted-foreground">
                                    {{ $transaction->payment_type ? ucfirst(str_replace('_', ' ', $transaction->payment_type)) : '-' }}
                                </td>
                                <td class="px-5 py-4 hidden xl:table-cell text-muted-foreground font-mono text-sm">
                                    {{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-5 py-4 hidden xl:table-cell text-muted-foreground font-mono text-sm">
                                    {{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('H:i:s') : '-' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.transactions.show', $transaction->id) }}"
                                            class="w-8 h-8 rounded-md bg-muted hover:bg-accent-50 text-muted-foreground hover:text-accent-400 flex items-center justify-center transition-all duration-200 hover:scale-105"
                                            title="Lihat">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </a>
                                        @if ($transaction->payment_status == 'pending')
                                            <form action="{{ route('admin.orders.verify', $transaction->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-md bg-muted hover:bg-success-50 text-muted-foreground hover:text-success-500 flex items-center justify-center transition-all duration-200 hover:scale-105"
                                                    title="Verifikasi">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-muted to-muted flex items-center justify-center mb-4 ring-1 ring-border/60">
                                            <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                                        </div>
                                        <p class="text-base font-semibold text-muted-foreground">Tidak Ada Transaksi</p>
                                        <p class="text-sm text-muted-foreground mt-1.5 max-w-xs">Belum ada transaksi yang ditemukan. Coba ubah filter pencarian Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $transactions->links() }}
        </div>
    </div>

    <form id="exportForm" method="GET" action="">
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
        <input type="hidden" name="payment_type" value="{{ request('payment_type') }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="package_id" value="{{ request('package_id') }}">
    </form>

    <script>
        function toggleExportDropdown() {
            document.getElementById('exportDropdown').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#exportWrap')) document.getElementById('exportDropdown')?.classList.add('hidden');
        });

        function exportExcel() {
            document.getElementById('exportForm').action = "{{ route('admin.transactions.export-excel') }}";
            document.getElementById('exportForm').submit();
        }

        function exportPdf() {
            document.getElementById('exportForm').action = "{{ route('admin.transactions.export-pdf') }}";
            document.getElementById('exportForm').submit();
        }
    </script>
@endsection
