{{-- user/orders/payment.blade.php --}}
@extends('layouts.app')

@section('title', 'Checkout - KPM Belajar Online')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 md:gap-3">
            <a href="{{ route('orders.index') }}" class="text-muted-foreground hover:text-muted-foreground transition p-1 -ml-1 text-lg">←</a>
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
                                No. Pesanan: <span class="font-mono">{{ $order->order_number }}</span>
                            </p>
                        </div>
                        <span class="px-2 md:px-3 py-1 bg-gold-50 text-gold-700 text-xs font-medium rounded-full flex-shrink-0">
                            ⏳ Menunggu Pembayaran
                        </span>
                    </div>
                </div>

                <div class="p-4 md:p-6 flex items-start gap-3 md:gap-4">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-md flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl md:text-3xl">📚</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm md:text-lg font-semibold text-foreground truncate">{{ $order->item_title }}</h3>
                        <div class="flex flex-wrap items-center gap-1 md:gap-2 mt-1">
                            <span class="text-xs md:text-sm text-muted-foreground">Paket Bank Soal</span>
                            <span class="w-1 h-1 bg-border rounded-full hidden sm:block"></span>
                            <span class="text-xs md:text-sm text-muted-foreground">Akses Penuh</span>
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-6 pb-4 md:pb-6">
                    <div class="bg-muted rounded-md p-3 md:p-4 space-y-2">
                        <div class="flex justify-between text-xs md:text-sm">
                            <span class="text-muted-foreground">Harga Paket</span>
                            <span class="text-foreground">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs md:text-sm">
                            <span class="text-muted-foreground">Biaya Layanan</span>
                            <span class="text-success-500">Gratis</span>
                        </div>
                        <div class="border-t border-border pt-2 mt-2">
                            <div class="flex justify-between font-semibold">
                                <span class="text-foreground text-sm md:text-base">Total</span>
                                <span class="text-lg md:text-xl text-foreground">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
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
                            <label class="text-xs md:text-sm font-medium text-foreground">Nama</label>
                            <p class="text-foreground mt-1 text-sm md:text-base break-all">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <label class="text-xs md:text-sm font-medium text-foreground">Email</label>
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
                        'Status pesanan diperbarui otomatis setelah pembayaran.',
                    ] as $i => $step)
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 md:w-6 md:h-6 rounded-full bg-primary/10 text-primary text-[10px] md:text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                {{ $i + 1 }}
                            </span>
                            <span class="text-xs md:text-sm text-muted-foreground">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        {{-- Kolom Kanan: Midtrans Snap --}}
        <div class="lg:col-span-1">
            @include('partials.midtrans-snap', [
                'snapToken'   => $snapToken,
                'clientKey'   => $clientKey,
                'snapJsUrl'   => $snapJsUrl,
                'amount'      => (int) $order->total_price,
                'configured'  => $gatewayConfigured,
                'sandbox'     => $sandboxMode,
                'finishUrl'   => url('/payment/finish?order_ref=' . $order->id),
                'cancelUrl'   => route('orders.index'),
                'itemTitle'   => $order->item_title,
                'isVideo'     => false,
            ])
        </div>
    </div>

    <div class="text-center text-[10px] md:text-xs text-muted-foreground pt-2">
        <p>© {{ date('Y') }} KPM Belajar Online. Semua hak dilindungi.</p>
        <p class="mt-1">Dengan melanjutkan pembayaran, Anda menyetujui Syarat & Ketentuan yang berlaku.</p>
    </div>
</div>
@endsection
