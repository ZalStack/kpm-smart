<?php

/*
|------------------------------------------------------------------
| Midtrans Payment Gateway
|------------------------------------------------------------------
| Kredensial WAJIB diisi lewat .env (jangan hardcode di file ini).
| Ambil dari Dashboard Midtrans -> Settings -> Access Keys.
|
| Sandbox : https://api.sandbox.midtrans.com
| Produksi: https://api.midtrans.com
|
| Dokumentasi Snap API: https://snap-docs.midtrans.com/
*/

return [
    'merchant_id'  => env('MIDTRANS_MERCHANT_ID'),
    'client_key'   => env('MIDTRANS_CLIENT_KEY'),
    'server_key'   => env('MIDTRANS_SERVER_KEY'),

    // Mode sandbox aktif secara default agar tidak ada transaksi nyata
    // yang terjadi tanpa sengaja saat konfigurasi belum lengkap.
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Aktifkan logging request/response ke channel 'midtrans'.
    'enable_logging' => env('MIDTRANS_ENABLE_LOGGING', false),

    // Masa berlaku tagihan (menit) sebelum kedaluwarsa di sisi Midtrans.
    'expiry_minutes' => (int) env('MIDTRANS_EXPIRY_MINUTES', 60),

    // URL Snap JS CDN (jangan ubah kecuali Midtrans merilis versi baru).
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
