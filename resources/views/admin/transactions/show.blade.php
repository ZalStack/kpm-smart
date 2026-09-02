{{-- admin/transactions/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Transaksi - Admin')
@section('header-title', 'Detail Transaksi')
@section('header-sub', 'Lihat informasi lengkap transaksi')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.transactions.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Transaksi
        </a>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Cetak
            </button>
            @if($transaction->payment_status == 'pending')
                <form action="{{ route('admin.orders.verify', $transaction->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verifikasi Pembayaran
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-4 md:space-y-6">
            <!-- Header Card -->
            <div class="admin-card p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-mono font-bold text-lg bg-muted px-3 py-1.5 rounded-md text-foreground">#{{ $transaction->order_number }}</span>
                        @if($transaction->payment_status == 'pending')
                            <span class="badge-warning">Menunggu</span>
                        @elseif($transaction->payment_status == 'paid')
                            <span class="badge-success">Lunas</span>
                        @else
                            <span class="badge-danger">Gagal</span>
                        @endif
                    </div>
                    <div class="text-sm text-muted-foreground">Dibuat: <span class="font-medium text-foreground">{{ $transaction->created_at->format('d F Y, H:i') }}</span></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                    <div>
                        <p class="form-label">Pelanggan</p>
                        <div class="font-semibold text-foreground">{{ $transaction->user?->name ?? 'Pengguna Tidak Dikenal' }}</div>
                        <div class="text-sm text-muted-foreground">{{ $transaction->user?->email ?? '-' }}</div>
                    </div>
                    <div>
                        <p class="form-label">Paket</p>
                        <div class="font-semibold text-foreground">{{ $transaction->package?->title ?? 'Paket Dihapus' }}</div>
                        <div class="text-lg font-bold text-success-500">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 pt-4 border-t border-border">
                    <div>
                        <p class="form-label">Metode Pembayaran</p>
                        <p class="text-foreground font-medium">{{ $transaction->payment_type ? ucfirst($transaction->payment_type) : '-' }}</p>
                    </div>
                    <div>
                        <p class="form-label">ID Transaksi</p>
                        <p class="font-mono text-sm bg-muted px-3 py-1.5 rounded-md text-foreground break-all">{{ $transaction->transaction_id ?? '-' }}</p>
                    </div>
                </div>

                <!-- Payment Date & Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 pt-4 border-t border-border">
                    <div>
                        <p class="form-label">Tanggal Pembayaran</p>
                        <p class="text-foreground font-medium font-mono">{{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="form-label">Waktu Pembayaran</p>
                        <p class="text-foreground font-medium font-mono">{{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('H:i:s') : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Enrollment Status -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                    Status Pendaftaran
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-muted rounded-md p-4">
                        <p class="form-label">Aktif</p>
                        <p class="text-lg font-semibold mt-1 {{ ($transaction->enrollment['activated'] ?? false) ? 'text-success-500' : 'text-muted-foreground' }}">{{ ($transaction->enrollment['activated'] ?? false) ? 'Ya' : 'Tidak' }}</p>
                        @if($transaction->enrollment['activated'] ?? false)
                            <svg class="w-5 h-5 text-success-500 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-muted-foreground mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        @endif
                    </div>
                    <div class="bg-muted rounded-md p-4">
                        <p class="form-label">Dikirim oleh Admin</p>
                        <p class="text-lg font-semibold mt-1 {{ ($transaction->enrollment['sent_by_admin'] ?? false) ? 'text-success-500' : 'text-muted-foreground' }}">{{ ($transaction->enrollment['sent_by_admin'] ?? false) ? 'Ya' : 'Tidak' }}</p>
                        @if($transaction->enrollment['sent_by_admin'] ?? false)
                            <svg class="w-5 h-5 text-success-500 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-muted-foreground mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        @endif
                    </div>
                    <div class="bg-muted rounded-md p-4">
                        <p class="form-label">Terbuka</p>
                        <p class="text-lg font-semibold mt-1 {{ ($transaction->enrollment['unlocked'] ?? false) ? 'text-success-500' : 'text-muted-foreground' }}">{{ ($transaction->enrollment['unlocked'] ?? false) ? 'Ya' : 'Tidak' }}</p>
                        @if($transaction->enrollment['unlocked'] ?? false)
                            <svg class="w-5 h-5 text-success-500 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @else
                            <svg class="w-5 h-5 text-muted-foreground mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        @endif
                    </div>
                </div>
                @if($transaction->enrollment['key'] ?? false)
                    <div class="mt-4 bg-primary/10 border border-primary/30 rounded-md p-4 flex items-center gap-3">
                        <svg class="w-5 h-5 text-accent-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                        <div><span class="text-sm font-semibold text-muted-foreground">Enrollment Key:</span> <code class="font-mono font-bold text-brand-800 bg-card px-4 py-1.5 rounded-md border border-primary/30 text-sm">{{ $transaction->enrollment['key'] }}</code></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4 md:space-y-6">
            <!-- Summary -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    Ringkasan
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Total Pesanan</span>
                        <span class="font-bold text-brand-900">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Status</span>
                        @if($transaction->payment_status == 'pending')
                            <span class="badge-warning">Menunggu</span>
                        @elseif($transaction->payment_status == 'paid')
                            <span class="badge-success">Lunas</span>
                        @else
                            <span class="badge-danger">Gagal</span>
                        @endif
                    </div>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Jenis Pembayaran</span>
                        <span class="font-medium text-foreground">{{ $transaction->payment_type ? ucfirst($transaction->payment_type) : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Tanggal Pembayaran</span>
                        <span class="font-medium text-foreground font-mono">{{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-border">
                        <span class="text-muted-foreground">Waktu Pembayaran</span>
                        <span class="font-medium text-foreground font-mono">{{ $transaction->payment_time ? Carbon\Carbon::parse($transaction->payment_time)->format('H:i:s') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-muted-foreground">Dibuat</span>
                        <span class="font-medium text-foreground">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    Aksi Cepat
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn-secondary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25"/></svg>
                        Lihat Semua Pesanan
                    </a>
                    @if($transaction->package)
                        <a href="{{ route('admin.packages.edit', $transaction->package_id) }}" class="btn-secondary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Edit Paket
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .main-content, .main-content * { visibility: visible; }
    .main-content { position: absolute; left: 0; top: 0; width: 100%; }
    .admin-header, .btn-print, form button { display: none !important; }
    .admin-card { background: white !important; box-shadow: none !important; border: 1px solid #e5e7eb !important; }
}
</style>
@endsection
