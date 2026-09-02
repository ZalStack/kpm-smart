{{-- orders/detail.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Pesanan - KPM Belajar Online')

@section('content')
<div class="space-y-6 detail-stagger">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('orders.index') }}" class="text-primary hover:text-primary transition p-2 -ml-2 text-lg">←</a>
        <h1 class="text-xl md:text-3xl font-bold text-foreground">{{ ($isRenewal ?? false) ? 'Konfirmasi Perpanjangan' : 'Konfirmasi Pesanan' }}</h1>
    </div>

    <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden max-w-3xl">
        <!-- Package Preview -->
        <div class="p-4 md:p-6 border-b border-border">
            <div class="flex items-start gap-3 md:gap-4">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-3xl md:text-4xl">📚</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg md:text-xl font-bold text-foreground truncate">{{ $package->title }}</h2>
                    <p class="text-muted-foreground text-sm mt-1">
                        Paket Bank Soal • Akses {{ $package->membership_duration_label }}
                        @if($order->is_custom_amount)
                            <span class="ml-1 inline-block px-2 py-0.5 bg-success-500/10 text-success-500 text-[10px] rounded-full align-middle">💝 Bayar Seikhlasnya</span>
                        @endif
                        @if($isRenewal ?? false)
                            <span class="ml-1 inline-block px-2 py-0.5 bg-primary/10 text-primary text-[10px] rounded-full align-middle">🔄 Perpanjangan Membership</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="p-4 md:p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                <div class="bg-muted rounded-md p-3 md:p-4">
                    <p class="text-xs md:text-sm text-muted-foreground">No. Pesanan</p>
                    <p class="font-mono text-xs md:text-sm font-semibold text-foreground mt-1 break-all">{{ $order->order_number }}</p>
                </div>
                <div class="bg-muted rounded-md p-3 md:p-4">
                    <p class="text-xs md:text-sm text-muted-foreground">Status</p>
                    <div class="flex items-center mt-1">
                        <span class="w-2 h-2 bg-gold-400 rounded-full mr-2 animate-pulse"></span>
                        <span class="font-semibold text-gold-600 text-sm md:text-base">Menunggu Pembayaran</span>
                    </div>
                </div>
            </div>

            <!-- Price -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-md p-4 md:p-5">
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <span class="text-muted-foreground text-sm md:text-base">Total Pembayaran</span>
                    <span class="text-xl md:text-3xl font-bold text-foreground">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Membership Info -->
            <div class="flex items-start gap-3 p-3 md:p-4 bg-indigo-50 rounded-md">
                <svg class="w-5 h-5 text-foreground mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1.05A7.002 7.002 0 0117 11a1 1 0 11-2 0 5 5 0 00-4-4.9V8a1 1 0 11-2 0V6.1A5 5 0 005 11a1 1 0 11-2 0 7.002 7.002 0 016-6.95V3a1 1 0 011-1z"></path>
                    <path fill-rule="evenodd" d="M10 20a7 7 0 007-7H3a7 7 0 007 7z" clip-rule="evenodd"></path>
                </svg>
                <div class="text-sm text-foreground">
                    <p class="font-medium">Masa aktif membership: {{ $package->membership_duration_label }}</p>
                    <p class="text-foreground/80 mt-0.5 text-xs md:text-sm">
                        @if($isRenewal ?? false)
                            Masa aktif akan otomatis disambung dari tanggal berakhir membership Anda saat ini (atau mulai hari ini jika sudah kedaluwarsa), setelah pembayaran berhasil.
                        @else
                            Masa aktif dihitung otomatis mulai tanggal pembayaran berhasil.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Info -->
            <div class="flex items-start gap-3 p-3 md:p-4 bg-primary/10 rounded-md">
                <svg class="w-5 h-5 text-primary mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div class="text-sm text-primary">
                    <p class="font-medium">Setelah pembayaran berhasil</p>
                    <p class="text-primary mt-0.5 text-xs md:text-sm">Admin akan mengirimkan Enroll Key untuk mengakses paket</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-4 md:p-6 bg-muted border-t border-border flex flex-col sm:flex-row gap-3">
            <a href="{{ route('orders.process-payment', $order->id) }}"
               class="flex-1 bg-navy-light text-white py-3 px-4 md:px-6 rounded-md font-semibold text-center hover:bg-navy hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 text-sm md:text-base">
                💰 Bayar Sekarang
            </a>
            <a href="{{ route('packages.index') }}"
               class="flex-1 bg-muted text-foreground py-3 px-4 md:px-6 rounded-md font-semibold text-center hover:bg-muted transition-all duration-200 text-sm md:text-base border border-border">
                Batal
            </a>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-4 md:p-6 max-w-3xl">
        <p class="text-xs md:text-sm text-muted-foreground text-center">Metode Pembayaran Tersedia</p>
        <div class="flex flex-wrap justify-center gap-2 md:gap-3 mt-3">
            <span class="bg-muted px-3 py-1.5 md:px-4 md:py-2 rounded-md text-xs md:text-sm border border-border">🏦 Transfer Bank</span>
            <span class="bg-muted px-3 py-1.5 md:px-4 md:py-2 rounded-md text-xs md:text-sm border border-border">📱 E-Wallet</span>
            <span class="bg-muted px-3 py-1.5 md:px-4 md:py-2 rounded-md text-xs md:text-sm border border-border">💳 Kartu Kredit</span>
            <span class="bg-muted px-3 py-1.5 md:px-4 md:py-2 rounded-md text-xs md:text-sm border border-border">📸 QRIS</span>
            <span class="bg-muted px-3 py-1.5 md:px-4 md:py-2 rounded-md text-xs md:text-sm border border-border">🏪 Minimarket</span>
        </div>
    </div>
</div>
@endsection
