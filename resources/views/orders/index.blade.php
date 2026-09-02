{{-- orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Riwayat Pesanan - KPM Belajar Online')

@section('content')
<style>
    .orders-stagger > * {
        animation: fadeInUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .orders-stagger > *:nth-child(1) { animation-delay: 0ms; }
    .orders-stagger > *:nth-child(2) { animation-delay: 60ms; }
    .orders-stagger > *:nth-child(3) { animation-delay: 120ms; }
    .orders-stagger > *:nth-child(4) { animation-delay: 180ms; }
    .orders-stagger > *:nth-child(5) { animation-delay: 240ms; }
    .orders-stagger > *:nth-child(6) { animation-delay: 300ms; }
</style>

<div class="space-y-6 orders-stagger">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-navy-light/10 text-navy-light text-xs font-semibold mb-2">📦 Pesanan</span>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Riwayat Pesanan</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Semua pesanan paket &amp; video pembahasan Anda</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('videos.index') }}" class="inline-flex items-center gap-2 bg-card border border-border text-muted-foreground px-4 py-2.5 md:py-3 rounded-md font-semibold hover:bg-muted hover:border-border hover:-translate-y-0.5 transition-all duration-200 text-center text-sm md:text-base">
                🎬 Beli Video
            </a>
            <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-navy-light to-navy text-white px-4 md:px-6 py-2.5 md:py-3 rounded-md font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-center text-sm md:text-base">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Beli Paket Baru
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-nowrap overflow-x-auto gap-2 pb-2 scrollbar-hidden">
        <button class="tab-btn active flex-shrink-0 px-4 md:px-5 py-2 rounded-md text-xs md:text-sm font-medium bg-navy-light text-white shadow-sm transition-all duration-200" data-filter="all">Semua</button>
        <button class="tab-btn flex-shrink-0 px-4 md:px-5 py-2 rounded-md text-xs md:text-sm font-medium bg-card text-muted-foreground border border-border hover:bg-muted hover:border-border transition-all duration-200" data-filter="paid">✅ Lunas</button>
        <button class="tab-btn flex-shrink-0 px-4 md:px-5 py-2 rounded-md text-xs md:text-sm font-medium bg-card text-muted-foreground border border-border hover:bg-muted hover:border-border transition-all duration-200" data-filter="pending">⏳ Tertunda</button>
        <button class="tab-btn flex-shrink-0 px-4 md:px-5 py-2 rounded-md text-xs md:text-sm font-medium bg-card text-muted-foreground border border-border hover:bg-muted hover:border-border transition-all duration-200" data-filter="failed">❌ Gagal</button>
    </div>

    @if ($orders->isEmpty())
        <div class="bg-card rounded-lg shadow-sm border border-border p-8 md:p-12 text-center">
            <div class="text-5xl md:text-6xl mb-4">🛒</div>
            <h3 class="text-lg md:text-xl font-bold text-muted-foreground">Belum Ada Pesanan</h3>
            <p class="text-muted-foreground mt-2 text-sm md:text-base">Mulai beli paket bank soal atau video pembahasan untuk belajar</p>
            <div class="flex flex-wrap items-center justify-center gap-2 mt-4">
                <a href="{{ route('packages.index') }}" class="inline-block bg-navy-light text-white px-6 md:px-8 py-2.5 md:py-3 rounded-md font-semibold hover:bg-navy transition text-sm md:text-base">Lihat Paket</a>
                <a href="{{ route('videos.index') }}" class="inline-block bg-card border border-border text-muted-foreground px-6 md:px-8 py-2.5 md:py-3 rounded-md font-semibold hover:bg-muted transition text-sm md:text-base">🎬 Lihat Video</a>
            </div>
        </div>
    @else
        <div class="space-y-3 md:space-y-4">
            @foreach ($orders as $order)
                @php
                    $isVideo = $order->isVideoOrder();
                    $videoItem = $isVideo ? $order->videoOrder : null;
                @endphp
                @if ($isVideo)
                    {{-- ===== Kartu Pesanan Video Pembahasan ===== --}}
                    @php
                        $accessActive = $videoItem && $videoItem->access_granted && $videoItem->access_end && $videoItem->access_end->isFuture();
                        $daysLeft = $accessActive ? (int) now()->startOfDay()->diffInDays($videoItem->access_end, false) : 0;
                        $watchUrl = route('videos.show', $videoItem?->video_id ?? 0);
                    @endphp
                    <div class="order-row bg-card rounded-lg shadow-sm border border-border p-4 md:p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" data-status="{{ $order->payment_status }}">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-3 md:gap-4">
                                    <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-pink-50 to-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-xl md:text-2xl">🎬</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-semibold text-foreground truncate text-sm md:text-base">{{ $videoItem?->video?->title ?? 'Video Pembahasan' }}</h3>
                                            <span class="px-2 py-0.5 bg-pink-100 text-pink-600 text-[10px] rounded-full flex-shrink-0">Video</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground mt-1">
                                            <span class="font-mono">{{ $order->order_number }}</span>
                                            <span class="w-1 h-1 bg-border rounded-full hidden sm:block"></span>
                                            <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                        </div>

                                        @if ($order->payment_status === 'paid')
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-[11px] md:text-xs">
                                                @if ($accessActive)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold bg-success-50 text-success-500 ring-1 ring-success-500/10">✅ Akses Aktif</span>
                                                    <span class="text-muted-foreground">
                                                        {{ $videoItem->access_start?->translatedFormat('d M Y') }} – {{ $videoItem->access_end?->translatedFormat('d M Y') }}
                                                    </span>
                                                    <span class="text-muted-foreground">Sisa {{ max(0, $daysLeft) }} hari</span>
                                                @elseif($videoItem && $videoItem->access_granted && $videoItem->access_end && $videoItem->access_end->isPast())
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold bg-danger-100 text-danger-500">⛔ Kedaluwarsa</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold bg-gold-400/15 text-gold-600">⏳ Menunggu Aktivasi Admin</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-start md:items-end gap-1">
                                <div class="flex items-center gap-2">
                                    @if ($order->payment_status === 'paid')
                                        <span class="px-2 md:px-3 py-1 bg-success-50 text-success-500 ring-1 ring-success-500/10 text-xs font-medium rounded-full">✅ Lunas</span>
                                    @elseif($order->payment_status === 'pending')
                                        <span class="px-2 md:px-3 py-1 bg-gold-400/15 text-gold-600 text-xs font-medium rounded-full flex items-center">
                                            <span class="w-1.5 h-1.5 bg-gold-400 rounded-full mr-1.5 animate-pulse"></span> Menunggu
                                        </span>
                                    @else
                                        <span class="px-2 md:px-3 py-1 bg-danger-100 text-danger-500 text-xs font-medium rounded-full">❌ Gagal</span>
                                    @endif
                                </div>
                                @if ($order->payment_time)
                                    <span class="text-[11px] text-muted-foreground">Dibayar {{ $order->payment_time->translatedFormat('d M Y, H:i') }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if ($order->payment_status === 'paid' && $accessActive)
                                    <a href="{{ $watchUrl }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-success-500 text-white text-xs md:text-sm font-medium rounded-md hover:bg-success-600 transition">▶️ Tonton</a>
                                @elseif($order->payment_status === 'paid' && $videoItem && !$videoItem->access_granted)
                                    <span class="text-xs text-muted-foreground px-2 py-1 bg-muted rounded-md">⏳ Menunggu Admin</span>
                                @elseif($order->payment_status === 'paid')
                                    <a href="{{ $watchUrl }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-navy-light text-white text-xs md:text-sm font-medium rounded-md hover:bg-navy transition">🔄 Beli Lagi</a>
                                @elseif($order->payment_status === 'pending' && $videoItem)
                                    <a href="{{ route('videos.pay', ['video' => $videoItem->video_id, 'videoOrder' => $videoItem->id]) }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-navy-light text-white text-xs md:text-sm font-medium rounded-md hover:bg-navy transition">💰 Bayar</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- ===== Kartu Pesanan Paket Bank Soal ===== --}}
                    @php
                        $enrollment = $order->enrollment ?? [];
                        $ready = ($enrollment['sent_by_admin'] ?? false) && ($enrollment['activated'] ?? false);
                        $unlocked = $enrollment['unlocked'] ?? false;
                        $mStatus = $order->membershipStatus();
                        $isCurrent = $order->payment_status !== 'paid'
                            || optional(\App\Models\Order::latestPaidFor($order->user_id, $order->package_id))->id === $order->id;
                    @endphp
                    <div class="order-row bg-card rounded-lg shadow-sm border border-border p-4 md:p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300" data-status="{{ $order->payment_status }}">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-3 md:gap-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <span class="text-xl md:text-2xl">📚</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-semibold text-foreground truncate text-sm md:text-base">{{ $order->item_title }}</h3>
                                        <span class="px-2 py-0.5 bg-muted text-muted-foreground text-[10px] rounded-full flex-shrink-0">Paket</span>
                                        @if($order->is_custom_amount)
                                            <span class="px-2 py-0.5 bg-success-500/10 text-success-500 text-[10px] rounded-full flex-shrink-0">💝 Seikhlasnya</span>
                                        @endif
                                        @if(str_contains($order->payment_notes ?? '', 'Perpanjangan'))
                                            <span class="px-2 py-0.5 bg-primary/10 text-primary text-[10px] rounded-full flex-shrink-0">🔄 Perpanjangan</span>
                                        @endif
                                        @if(!$isCurrent)
                                            <span class="px-2 py-0.5 bg-muted text-muted-foreground text-[10px] rounded-full flex-shrink-0">Riwayat</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground mt-1">
                                        <span class="font-mono">{{ $order->order_number }}</span>
                                        <span class="w-1 h-1 bg-border rounded-full hidden sm:block"></span>
                                        <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                    </div>

                                    @if($mStatus)
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-[11px] md:text-xs">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold
                                                @if($mStatus === 'active') bg-success-50 text-success-500 ring-1 ring-success-500/10
                                                @elseif($mStatus === 'expiring') bg-gold-400/15 text-gold-600
                                                @else bg-danger-100 text-danger-500 @endif">
                                                @if($mStatus === 'active') ✅ Aktif
                                                @elseif($mStatus === 'expiring') ⏳ Akan Berakhir
                                                @else ⛔ Kedaluwarsa @endif
                                            </span>
                                            <span class="text-muted-foreground">
                                                {{ $order->membership_start?->translatedFormat('d M Y') }} – {{ $order->membership_end?->translatedFormat('d M Y') }}
                                            </span>
                                            @if($order->isMembershipActive())
                                                <span class="text-muted-foreground">Sisa {{ $order->membershipDaysRemaining() }} hari</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($order->payment_status === 'paid' && $ready && $unlocked && $order->isMembershipActive())
                                        <div class="flex items-center mt-1 text-xs text-success-500 font-medium">
                                            <svg class="w-3 h-3 md:w-4 md:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            Akses Aktif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-start md:items-end gap-1">
                            <div class="flex items-center gap-2">
                                @if ($order->payment_status === 'paid')
                                    <span class="px-2 md:px-3 py-1 bg-success-50 text-success-500 ring-1 ring-success-500/10 text-xs font-medium rounded-full">✅ Lunas</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="px-2 md:px-3 py-1 bg-gold-400/15 text-gold-600 text-xs font-medium rounded-full flex items-center">
                                        <span class="w-1.5 h-1.5 bg-gold-400 rounded-full mr-1.5 animate-pulse"></span> Menunggu
                                    </span>
                                @else
                                    <span class="px-2 md:px-3 py-1 bg-danger-100 text-danger-500 text-xs font-medium rounded-full">❌ Gagal</span>
                                @endif
                            </div>
                            @if ($order->enrollment && isset($order->enrollment['key']) && $ready)
                                <div class="flex items-center gap-2">
                                    <code class="bg-muted px-2 md:px-3 py-1 rounded-md text-xs font-mono text-foreground break-all max-w-[120px] md:max-w-none">{{ $order->enrollment['key'] }}</code>
                                    <button onclick="copyToClipboard('{{ $order->enrollment['key'] }}')" class="text-muted-foreground hover:text-primary transition text-sm flex-shrink-0">📋</button>
                                </div>
                                @if ($unlocked)
                                    <span class="text-xs text-success-500 font-medium">✅ Teraktivasi</span>
                                @else
                                    <span class="text-xs text-gold-600 font-medium">🔑 Perlu Aktivasi</span>
                                @endif
                            @elseif ($order->payment_status === 'paid')
                                <span class="text-xs text-muted-foreground">⏳ Menunggu Admin</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($order->payment_status === 'paid' && $isCurrent)
                                @if (!$order->isMembershipActive())
                                    <a href="{{ route('packages.show', $order->package_id) }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-danger-500 text-white text-xs md:text-sm font-medium rounded-md hover:bg-danger-700 transition">⏳ Perpanjang</a>
                                @elseif (!$ready)
                                    <span class="text-xs text-muted-foreground px-2 py-1 bg-muted rounded-md">⏳ Menunggu Admin</span>
                                @elseif (!$unlocked)
                                    <a href="{{ route('packages.show', $order->package_id) }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-navy-light text-white text-xs md:text-sm font-medium rounded-md hover:bg-navy transition">🔑 Aktivasi</a>
                                @else
                                    <a href="{{ route('packages.show', $order->package_id) }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-success-500 text-white text-xs md:text-sm font-medium rounded-md hover:bg-success-600 transition">📖 Belajar</a>
                                @endif
                            @elseif($order->payment_status === 'pending')
                                <a href="{{ route('orders.process-payment', $order->id) }}" class="px-3 md:px-4 py-1.5 md:py-2 bg-navy-light text-white text-xs md:text-sm font-medium rounded-md hover:bg-navy transition">💰 Bayar</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="bg-primary/10 rounded-lg p-4 md:p-5 border border-primary/20">
        <div class="flex items-start gap-3 md:gap-4">
            <div class="w-8 h-8 md:w-10 md:h-10 bg-primary/15 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-lg md:text-xl">💡</span>
            </div>
            <div>
                <h4 class="font-semibold text-foreground text-sm md:text-base">Info Membership, Enroll Key &amp; Video</h4>
                <p class="text-xs md:text-sm text-muted-foreground mt-0.5">Setiap paket memiliki masa aktif membership sesuai durasi yang ditentukan Admin. Setelah pembayaran berhasil, Admin akan mengirimkan Enroll Key. Untuk pembelian video pembahasan, akses diaktifkan Admin setelah pembayaran terkonfirmasi dan berlaku sesuai durasi video.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const rows = document.querySelectorAll('.order-row');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => { t.classList.remove('active', 'bg-navy-light', 'text-white'); t.classList.add('bg-card', 'text-muted-foreground', 'border', 'border-border'); });
            this.classList.remove('bg-card', 'text-muted-foreground', 'border', 'border-border');
            this.classList.add('active', 'bg-navy-light', 'text-white');
            const filter = this.dataset.filter;
            rows.forEach(row => { row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none'; });
        });
    });
});

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => alert('✅ Enroll Key berhasil disalin!'));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ Enroll Key berhasil disalin!');
    }
}
</script>
@endpush
@endsection
