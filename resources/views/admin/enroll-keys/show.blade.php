{{-- admin/enroll-keys/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Enroll Key - Admin')
@section('header-title', 'Detail Enroll Key')
@section('header-sub', 'Lihat informasi lengkap kunci pendaftaran')

@section('content')
<div class="space-y-6">
    <!-- Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('admin.enroll-keys.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Kembali ke Enroll Keys
        </a>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                Cetak
            </button>
            @if(!($enrollKey->enrollment['activated'] ?? false))
                <form action="{{ route('admin.enroll-keys.activate', $enrollKey->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aktifkan
                    </button>
                </form>
            @endif
            @if(!($enrollKey->enrollment['sent_by_admin'] ?? false))
                <form action="{{ route('admin.enroll-keys.send', $enrollKey->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Kirim
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Enroll Key Card -->
            <div class="admin-card p-5 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="form-label mb-0">Enroll Key</span>
                        <code class="font-mono font-bold text-lg bg-primary/10 px-4 py-2 rounded-md text-brand-800 border-2 border-accent-400/30">{{ $enrollKey->enrollment['key'] ?? '-' }}</code>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($enrollKey->enrollment['activated'] ?? false)
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-warning">Belum Aktif</span>
                        @endif
                        @if($enrollKey->enrollment['sent_by_admin'] ?? false)
                            <span class="badge-info">Terkirim</span>
                        @else
                            <span class="badge-neutral">Belum Terkirim</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                    <div>
                        <p class="form-label">Informasi Pesanan</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-muted-foreground">Nomor Pesanan</span><span class="font-mono font-semibold text-foreground">{{ $enrollKey->order_number }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Total Harga</span><span class="font-semibold text-foreground">Rp {{ number_format($enrollKey->total_price, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Status Pembayaran</span><span class="badge-success text-[10px] py-0.5 px-2">Lunas</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Metode Pembayaran</span><span class="text-foreground">{{ $enrollKey->payment_type ? ucfirst($enrollKey->payment_type) : '-' }}</span></div>
                        </div>
                    </div>
                    <div>
                        <p class="form-label">Paket</p>
                        <div class="bg-muted rounded-md p-4">
                            <div class="font-semibold text-foreground">{{ $enrollKey->package?->title ?? 'Paket Dihapus' }}</div>
                            <div class="text-sm text-muted-foreground mt-1">{{ $enrollKey->package?->description ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 pt-4 border-t border-border">
                    <div>
                        <p class="form-label">Pengguna</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-900 to-brand-800 text-white flex items-center justify-center font-bold text-xl flex-shrink-0">
                                {{ strtoupper(substr($enrollKey->user?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-foreground">{{ $enrollKey->user?->name ?? 'Pengguna Tidak Dikenal' }}</div>
                                <div class="text-sm text-muted-foreground">{{ $enrollKey->user?->email ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="form-label">Detail Status</p>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-muted-foreground">Aktif</span><span class="font-medium {{ ($enrollKey->enrollment['activated'] ?? false) ? 'text-success-500' : 'text-gold-600' }}">{{ ($enrollKey->enrollment['activated'] ?? false) ? 'Ya' : 'Tidak' }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Dikirim oleh Admin</span><span class="font-medium {{ ($enrollKey->enrollment['sent_by_admin'] ?? false) ? 'text-accent-400' : 'text-muted-foreground' }}">{{ ($enrollKey->enrollment['sent_by_admin'] ?? false) ? 'Ya' : 'Tidak' }}</span></div>
                            <div class="flex justify-between"><span class="text-muted-foreground">Dibuka oleh Pengguna</span><span class="font-medium {{ ($enrollKey->enrollment['unlocked'] ?? false) ? 'text-brand-800' : 'text-muted-foreground' }}">{{ ($enrollKey->enrollment['unlocked'] ?? false) ? 'Ya' : 'Tidak' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Practice Sessions -->
            @if($enrollKey->practiceSessions->isNotEmpty())
            <div class="admin-card p-5 md:p-6">
                <h3 class="text-base font-semibold text-brand-900 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0116.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.015 6.015 0 01-5.54 0"/></svg>
                    Sesi Latihan
                </h3>
                <div class="space-y-3">
                    @foreach($enrollKey->practiceSessions as $session)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-muted rounded-md">
                            <span class="text-sm text-muted-foreground">{{ $session->created_at->format('d/m/Y H:i') }}</span>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-success-50 rounded-md text-sm font-medium text-success-500 border border-success-500/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    {{ $session->correct_answer ?? 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-danger-50 rounded-md text-sm font-medium text-danger-500 border border-danger-500/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    {{ $session->wrong_answer ?? 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-brand-50 rounded-md text-sm font-semibold text-brand-800 border border-brand-800/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                     Skor: {{ number_format($session->total_score ?? 0, 0) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="admin-card p-5 md:p-6">
                <h3 class="form-label mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    @if(!($enrollKey->enrollment['activated'] ?? false))
                        <form action="{{ route('admin.enroll-keys.activate', $enrollKey->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 p-3 bg-muted rounded-md hover:bg-success-50 transition text-foreground hover:text-success-500">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Aktifkan Kunci
                            </button>
                        </form>
                    @endif
                    @if(!($enrollKey->enrollment['sent_by_admin'] ?? false))
                        <form action="{{ route('admin.enroll-keys.send', $enrollKey->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 p-3 bg-muted rounded-md hover:bg-accent-50 transition text-foreground hover:text-accent-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                Kirim ke Pengguna
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 p-3 bg-muted rounded-md hover:bg-muted transition text-foreground">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Lihat Semua Pesanan
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 bg-muted rounded-md hover:bg-muted transition text-foreground">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Lihat Semua Pengguna
                    </a>
                </div>
            </div>

            <!-- Summary -->
            <div class="admin-card p-5 md:p-6">
                <h3 class="form-label mb-4">Ringkasan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-2 border-b border-border"><span class="text-muted-foreground">Pesanan</span><span class="font-mono font-semibold text-brand-900">{{ $enrollKey->order_number }}</span></div>
                    <div class="flex justify-between py-2 border-b border-border"><span class="text-muted-foreground">Paket</span><span class="font-medium text-brand-900">{{ $enrollKey->package?->title ?? '-' }}</span></div>
                    <div class="flex justify-between py-2 border-b border-border"><span class="text-muted-foreground">Pengguna</span><span class="font-medium text-brand-900">{{ $enrollKey->user?->name ?? '-' }}</span></div>
                    <div class="flex justify-between py-2"><span class="text-muted-foreground">Dibuat</span><span class="text-foreground">{{ $enrollKey->created_at->format('d/m/Y H:i') }}</span></div>
                </div>
            </div>

            <!-- Related Keys -->
            @if($relatedKeys->isNotEmpty())
            <div class="admin-card p-5 md:p-6">
                <h3 class="form-label mb-4">Enroll Key Terkait</h3>
                <div class="space-y-2">
                    @foreach($relatedKeys as $related)
                        <a href="{{ route('admin.enroll-keys.show', $related->id) }}" class="flex items-center justify-between p-3 bg-muted rounded-md hover:bg-muted transition">
                            <span class="font-mono font-semibold text-sm text-foreground truncate">{{ $related->enrollment['key'] ?? '-' }}</span>
                            @if($related->enrollment['activated'] ?? false)
                                <span class="badge-success text-[10px] py-0.5 px-2">Aktif</span>
                            @else
                                <span class="badge-warning text-[10px] py-0.5 px-2">Nonaktif</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
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
