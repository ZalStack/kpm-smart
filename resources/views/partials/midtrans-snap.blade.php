{{-- partials/midtrans-snap.blade.php --}}
{{--
    Komponen pembayaran Midtrans Snap yang responsif untuk semua ukuran layar.
    Variabel:
      $snapToken          Snap Token dari Midtrans (string)
      $clientKey          Midtrans Client Key
      $snapJsUrl          URL Snap.js CDN
      $amount             Nominal (int) untuk tampilan
      $configured         Apakah kredensial gateway lengkap
      $sandbox            Mode sandbox aktif?
      $finishUrl          URL redirect setelah pembayaran selesai
      $cancelUrl          URL kembali ke daftar pesanan
      $itemTitle          Judul item yang dibeli
      $isVideo            Apakah ini pembayaran video?
--}}

<div id="midtrans-snap-widget" class="bg-white rounded-2xl shadow-sm border border-border overflow-hidden">
    {{-- Header --}}
    <div class="p-4 sm:p-5 md:p-6 border-b border-border">
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <h2 class="text-base md:text-lg font-semibold text-foreground flex items-center gap-2">
                <span class="w-8 h-8 md:w-9 md:h-9 bg-navy-light/10 rounded-lg flex items-center justify-center text-base md:text-lg">💳</span>
                Pembayaran Midtrans
            </h2>
            @if(isset($sandbox) && $sandbox)
                <span class="px-2 py-0.5 md:px-3 md:py-1 bg-gold-50 text-gold-700 text-[10px] md:text-xs font-semibold rounded-full border border-gold-200">🧪 Mode Sandbox</span>
            @endif
        </div>
        <p class="text-xs md:text-sm text-muted-foreground mt-2">
            Proses pembayaran aman melalui
            <span class="font-semibold text-foreground">Midtrans</span> —
            Virtual Account, QRIS, GoPay, ShopeePay, Kartu Kredit, dan lainnya.
        </p>
    </div>

    <div class="p-4 sm:p-5 md:p-6 space-y-4">

        {{-- Peringatan konfigurasi --}}
        @if(!isset($configured) || !$configured)
            <div class="p-3 md:p-4 bg-gold-50 border border-gold-200 rounded-xl text-xs md:text-sm text-gold-800">
                ⚠️ Gateway pembayaran belum dikonfigurasi (MIDTRANS_SERVER_KEY / MIDTRANS_CLIENT_KEY kosong). Hubungi administrator.
            </div>
        @endif

        @if(isset($configured) && $configured && isset($snapToken) && $snapToken)
            {{-- Tombol Bayar Sekarang (memicu Snap popup) --}}
            <button id="snap-pay-btn"
                    data-token="{{ $snapToken }}"
                    data-finish-url="{{ $finishUrl ?? url('/payment/finish') }}"
                    class="w-full bg-gradient-to-r from-navy-light to-navy text-white py-3 md:py-4 px-4 rounded-xl font-semibold text-sm md:text-base shadow-lg shadow-navy-light/20 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span id="snap-pay-text">Bayar Rp {{ number_format($amount, 0, ',', '.') }}</span>
            </button>

            {{-- Metode pembayaran yang tersedia (informasi) --}}
            <div class="bg-muted rounded-xl p-3 md:p-4">
                <p class="text-[10px] md:text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-2.5">Metode Pembayaran Tersedia</p>
                <div class="flex flex-wrap gap-1.5 md:gap-2">
                    @foreach([
                        ['icon' => '🏦', 'label' => 'Virtual Account'],
                        ['icon' => '📱', 'label' => 'GoPay'],
                        ['icon' => '🛒', 'label' => 'ShopeePay'],
                        ['icon' => '📷', 'label' => 'QRIS'],
                        ['icon' => '💳', 'label' => 'Kartu Kredit'],
                        ['icon' => '🏪', 'label' => 'Alfamart/Indomaret'],
                    ] as $method)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 md:px-2.5 md:py-1 bg-white border border-border rounded-full text-[10px] md:text-xs text-muted-foreground font-medium">
                            {{ $method['icon'] }} {{ $method['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

        @elseif(isset($configured) && $configured && (!isset($snapToken) || !$snapToken))
            {{-- Snap Token kosong / gagal dibuat --}}
            <div class="p-3 md:p-4 bg-danger-50 border border-danger-200 rounded-xl text-xs md:text-sm text-danger-800">
                ❌ Gagal menyiapkan sesi pembayaran. Silakan muat ulang halaman atau coba beberapa saat lagi.
            </div>
            <a href="{{ url()->current() }}" class="block w-full text-center bg-muted hover:bg-muted text-foreground py-3 rounded-xl font-medium text-sm transition">
                🔄 Muat Ulang Halaman
            </a>
        @endif

        {{-- Keamanan --}}
        <div class="flex items-center justify-center gap-1.5 text-[10px] md:text-xs text-muted-foreground pt-1">
            <svg class="w-3 h-3 md:w-3.5 md:h-3.5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Transaksi diamankan & terenkripsi oleh Midtrans
        </div>
    </div>

    @if(isset($cancelUrl))
        <div class="px-4 sm:px-5 md:px-6 pb-4 md:pb-6">
            <a href="{{ $cancelUrl }}" class="block text-center text-xs md:text-sm text-muted-foreground hover:text-navy-light transition">← Kembali</a>
        </div>
    @endif
</div>

@once
@push('scripts')
{{-- Midtrans Snap.js (CDN) --}}
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey ?? '' }}"></script>
<script>
(function () {
    'use strict';

    var btn = document.getElementById('snap-pay-btn');
    if (!btn) return;

    var token     = btn.dataset.token     || '';
    var finishUrl = btn.dataset.finishUrl || '';

    btn.addEventListener('click', function () {
        if (!token) {
            alert('Sesi pembayaran tidak valid. Silakan muat ulang halaman.');
            return;
        }

        btn.disabled = true;
        document.getElementById('snap-pay-text').innerHTML =
            '<svg class="animate-spin inline-block w-4 h-4 mr-1" viewBox="0 0 24 24" fill="none">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>' +
            '</svg> Membuka Midtrans...';

        window.snap.pay(token, {
            onSuccess: function (result) {
                window.location.href = finishUrl;
            },
            onPending: function (result) {
                window.location.href = finishUrl;
            },
            onError: function (result) {
                btn.disabled = false;
                document.getElementById('snap-pay-text').textContent = 'Bayar Rp {{ number_format($amount, 0, ',', '.') }}';
                alert('⚠️ Terjadi kesalahan pada proses pembayaran. Silakan coba lagi.');
            },
            onClose: function () {
                btn.disabled = false;
                document.getElementById('snap-pay-text').textContent = 'Bayar Rp {{ number_format($amount, 0, ',', '.') }}';
            },
        });
    });
}());
</script>
@endpush
@endonce
