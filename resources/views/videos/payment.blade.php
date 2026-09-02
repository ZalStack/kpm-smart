{{-- user/videos/payment.blade.php --}}
@extends('layouts.app')

@section('title', 'Checkout - KPM Belajar Online')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 md:gap-3">
            <a href="{{ route('videos.show', $video->id) }}" class="text-muted-foreground hover:text-muted-foreground transition p-1 -ml-1 text-lg">←</a>
            <h1 class="text-xl md:text-3xl font-bold text-foreground">Checkout</h1>
        </div>
        <div class="text-xs md:text-sm text-muted-foreground hidden sm:block">
            <span class="inline-flex items-center gap-1">
                <svg class="w-3 h-3 md:w-4 md:h-4 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Aman & Terpercaya
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        {{-- Kolom Kiri: Detail Pesanan --}}
        <div class="lg:col-span-2 space-y-4 md:space-y-6">
            {{-- Ringkasan Pesanan --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-6 border-b border-border">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 class="text-base md:text-lg font-semibold text-foreground">Ringkasan Pesanan</h2>
                            <p class="text-xs md:text-sm text-muted-foreground mt-1">
                                No. Pesanan: <span class="font-mono">{{ $videoOrder->order_number }}</span>
                            </p>
                        </div>
                        <span class="px-2 md:px-3 py-1 bg-gold-50 text-gold-700 text-xs font-medium rounded-full flex-shrink-0">
                            ⏳ Menunggu Pembayaran
                        </span>
                    </div>
                </div>

                <div class="p-4 md:p-6 flex items-start gap-3 md:gap-4">
                    <div class="w-12 h-12 md:w-16 md:h-16 rounded-md flex items-center justify-center flex-shrink-0 overflow-hidden relative {{ $video->thumbnail ? '' : 'bg-gradient-to-br from-navy to-navy-light' }}">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}"
                                 alt="{{ $video->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-white/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm md:text-lg font-semibold text-foreground truncate">{{ $video->title }}</h3>
                        <div class="flex flex-wrap items-center gap-1 md:gap-2 mt-1">
                            <span class="text-xs md:text-sm text-muted-foreground">Video Pembahasan</span>
                            <span class="w-1 h-1 bg-muted-foreground rounded-full hidden sm:block"></span>
                            <span class="text-xs md:text-sm text-muted-foreground">Akses {{ $video->access_duration_days }} hari</span>
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-6 pb-4 md:pb-6">
                    <div class="bg-muted rounded-md p-3 md:p-4 space-y-2">
                        @if($video->is_pay_what_you_want)
                            <div class="flex justify-between text-xs md:text-sm">
                                <span class="text-muted-foreground">Tipe Pembayaran</span>
                                <span class="text-success-500 font-semibold">💝 Seikhlasnya</span>
                            </div>
                            <div class="flex justify-between text-xs md:text-sm">
                                <span class="text-muted-foreground">Jumlah Bayar</span>
                                <span class="text-foreground font-semibold">Rp {{ number_format($videoOrder->total_price, 0, ',', '.') }}</span>
                            </div>
                        @elseif($video->hasDiscount())
                            <div class="flex justify-between text-xs md:text-sm">
                                <span class="text-muted-foreground">Harga Normal</span>
                                <span class="text-muted-foreground line-through">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs md:text-sm">
                                <span class="text-muted-foreground">Diskon ({{ $video->discount_label }})</span>
                                <span class="text-danger-500">- Rp {{ number_format($video->price - $videoOrder->total_price, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="flex justify-between text-xs md:text-sm">
                                <span class="text-muted-foreground">Harga Video</span>
                                <span class="text-foreground">Rp {{ number_format($videoOrder->total_price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-xs md:text-sm">
                            <span class="text-muted-foreground">Biaya Layanan</span>
                            <span class="text-success-500">Gratis</span>
                        </div>
                        <div class="border-t border-border pt-2 mt-2">
                            <div class="flex justify-between font-semibold">
                                <span class="text-foreground text-sm md:text-base">Total</span>
                                <span class="text-lg md:text-xl text-navy-light">
                                    Rp {{ number_format($videoOrder->total_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informasi Pembeli --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-6 border-b border-border">
                    <h2 class="text-base md:text-lg font-semibold text-foreground">Informasi Pembeli</h2>
                </div>
                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                        <div>
                            <label class="text-xs md:text-sm font-medium text-muted-foreground">Nama</label>
                            <p class="text-foreground mt-1 text-sm md:text-base break-all">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <label class="text-xs md:text-sm font-medium text-muted-foreground">Email</label>
                            <p class="text-foreground mt-1 text-sm md:text-base break-all">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cara Pembayaran --}}
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 md:p-6 hidden lg:block">
                <h2 class="text-base md:text-lg font-semibold text-foreground mb-3">Cara Pembayaran</h2>
                <ol class="space-y-2.5">
                    @foreach([
                        'Klik tombol "Bayar Sekarang" di sebelah kanan.',
                        'Popup Midtrans akan terbuka — pilih metode pembayaran.',
                        'Ikuti instruksi pembayaran (VA, QRIS, e-wallet, dsb.).',
                        'Setelah pembayaran berhasil, admin akan mengaktifkan akses video.',
                    ] as $i => $step)
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 md:w-6 md:h-6 rounded-full bg-navy-light/10 text-navy-light text-[10px] md:text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <span class="text-xs md:text-sm text-muted-foreground">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Activation Notice --}}
            <div class="bg-accent-50/50 rounded-lg border border-primary/10 p-4 md:p-5">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-md bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4.5 h-4.5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-foreground">Aktivasi oleh Admin</p>
                        <p class="text-xs text-muted-foreground mt-1 leading-relaxed">Setelah pembayaran berhasil, video akan diaktifkan oleh tim admin dalam waktu maksimal 1×24 jam. Anda akan mendapat notifikasi ketika akses sudah aktif.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Midtrans Snap --}}
        <div class="lg:col-span-1">
            @include('partials.midtrans-snap', [
                'snapToken'  => $snapToken,
                'clientKey'  => $clientKey,
                'snapJsUrl'  => $snapJsUrl,
                'amount'     => (int) $videoOrder->total_price,
                'configured' => $gatewayConfigured,
                'sandbox'    => $sandboxMode,
                'finishUrl'  => url('/video-payment/finish?video_order_id=' . $videoOrder->id),
                'cancelUrl'  => route('videos.show', $video->id),
                'itemTitle'  => $video->title,
                'isVideo'    => true,
            ])
        </div>
    </div>

    <div class="text-center text-[10px] md:text-xs text-muted-foreground pt-2">
        <p>© {{ date('Y') }} KPM Belajar Online. Semua hak dilindungi.</p>
        <p class="mt-1">Dengan melanjutkan pembayaran, Anda menyetujui Syarat & Ketentuan yang berlaku.</p>
    </div>
</div>
@endsection